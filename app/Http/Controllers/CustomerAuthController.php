<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;
use App\Models\User;
use App\Mail\VerificationCodeMail;

class CustomerAuthController extends Controller
{
    /**
     * Show customer login page
     */
    public function showLogin()
    {
        return view('auth.customer');
    }

    /**
     * Handle customer login with email and password
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $customer = Customer::with('user')->where('email', $request->email)->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this email address. Please sign up first.',
            ], 404);
        }

        // Debug: Add logging to see what's happening
        Log::info('Customer login attempt', [
            'customer_id' => $customer->customer_id,
            'email' => $customer->email,
            'user_id' => $customer->user_id,
            'has_user' => !is_null($customer->user),
            'email_verified' => $customer->hasVerifiedEmail(),
        ]);

        // Check if email is verified
        if (!$customer->hasVerifiedEmail()) {
            // Generate a new verification code for existing accounts
            $code = $customer->generateEmailVerificationCode();
            
            // Send email with verification code
            try {
                Mail::to($customer->email)->send(new VerificationCodeMail($code, $customer->fullname));
            } catch (\Exception $e) {
                Log::error('Failed to send verification email: ' . $e->getMessage());
            }

            return response()->json([
                'success' => false,
                'message' => 'Your email address is not verified. We\'ve sent a new verification code to your email.',
                'needs_email_verification' => true,
                'email' => $customer->email
            ], 400);
        }

        // Check password against the linked user
        if (!$customer->user) {
            return response()->json([
                'success' => false,
                'message' => 'Account configuration error. Please contact support.',
                'debug' => 'No linked user found'
            ], 500);
        }

        // Check if the user has a dummy password (starts with 'customer_')
        if (strpos($customer->user->password, '$2y$') === false || 
            Hash::check('customer_' . $customer->created_at->timestamp, $customer->user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Your account was created before the password system was implemented. Please reset your password or contact support.',
                'needs_password_reset' => true
            ], 400);
        }

        if (!Hash::check($request->password, $customer->user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        // Manually log in the customer
        Auth::guard('customer')->login($customer);

        return response()->json([
            'success' => true,
            'message' => 'Login successful!',
            'customer' => [
                'id' => $customer->customer_id,
                'name' => $customer->fullname,
                'email' => $customer->email,
            ],
        ]);
    }

    /**
     * Verify email code and login customer
     */
    public function verifyCode(Request $request)
    {
        // Add debug logging
        Log::info('Email verification attempt', [
            'email' => $request->email,
            'code' => $request->code,
            'code_length' => strlen($request->code ?? ''),
        ]);

        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|min:5|max:7', // More flexible than size:6
        ]);

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer) {
            Log::warning('Verification attempt for non-existent customer', ['email' => $request->email]);
            return response()->json([
                'success' => false,
                'message' => 'Customer not found.',
            ], 404);
        }

        Log::info('Customer verification data', [
            'customer_id' => $customer->customer_id,
            'stored_code' => $customer->email_verification_code,
            'submitted_code' => $request->code,
            'code_expires_at' => $customer->email_verification_code_expires_at,
            'current_time' => now(),
            'is_expired' => $customer->email_verification_code_expires_at < now(),
        ]);

        // Check if code is valid and not expired
        if ($customer->email_verification_code !== $request->code) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code.',
            ], 400);
        }

        if ($customer->email_verification_code_expires_at < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Verification code has expired. Please request a new one.',
            ], 400);
        }

        // Mark email as verified (don't login automatically)
        $customer->markEmailAsVerified();

        return response()->json([
            'success' => true,
            'message' => 'Email successfully verified! You can now login with your password.',
        ]);
    }

    /**
     * Handle customer registration
     */
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:customers,email',
            'password' => 'required|string|min:6',
        ]);

        DB::beginTransaction();
        try {
            // Create user record with the provided password
            $user = User::create([
                'username' => $request->username,
                'password' => bcrypt($request->password),
                'role' => 'customer',
                'is_active' => true,
            ]);

            // Create customer record
            $customer = Customer::create([
                'user_id' => $user->user_id,
                'fullname' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
            ]);

            // Generate and send verification code for email verification
            $code = $customer->generateEmailVerificationCode();

            // Send email with verification code
            try {
                Mail::to($customer->email)->send(new VerificationCodeMail($code, $customer->fullname));
            } catch (\Exception $e) {
                // Log error but don't fail the request
                Log::error('Failed to send verification email: ' . $e->getMessage());
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Account created! Verification code sent to your email.',
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create account. Please try again.',
            ], 500);
        }
    }

    /**
     * Resend email verification code
     */
    public function resendVerificationCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found.',
            ], 404);
        }

        if ($customer->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email is already verified.',
            ], 400);
        }

        // Generate and send new verification code
        $code = $customer->generateEmailVerificationCode();

        try {
            Mail::to($customer->email)->send(new VerificationCodeMail($code, $customer->fullname));
        } catch (\Exception $e) {
            Log::error('Failed to send verification email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification email. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'New verification code sent to your email.',
        ]);
    }

    /**
     * Handle customer logout
     */
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        
        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out.',
        ]);
    }

    /**
     * Get current authenticated customer
     */
    public function user(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated.',
            ], 200); // Return 200 instead of 401 for status checking
        }

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->customer_id,
                'name' => $customer->fullname,
                'email' => $customer->email,
                'avatar' => $customer->avatar,
                'email_verified' => !is_null($customer->email_verified_at),
            ],
        ]);
    }

    /**
     * Update password for existing customers
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $customer = Customer::with('user')->where('email', $request->email)->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this email address.',
            ], 404);
        }

        // Verify the email verification code
        if ($customer->email_verification_code !== $request->verification_code ||
            $customer->email_verification_code_expires_at < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired verification code.',
            ], 400);
        }

        // Update the user's password
        if ($customer->user) {
            $customer->user->update([
                'password' => bcrypt($request->password)
            ]);
            
            // Clear the verification code
            $customer->update([
                'email_verification_code' => null,
                'email_verification_code_expires_at' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully! You can now login with your new password.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Account configuration error. Please contact support.',
        ], 500);
    }

    /**
     * Send password reset code for existing customers
     */
    public function sendPasswordResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this email address.',
            ], 404);
        }

        // Generate verification code
        $code = $customer->generateEmailVerificationCode();

        // Send email
        try {
            Mail::to($customer->email)->send(new VerificationCodeMail($code, $customer->fullname));
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Password reset code sent to your email.',
            'code' => app()->environment('local') ? $code : null, // Only show in development
        ]);
    }
}
