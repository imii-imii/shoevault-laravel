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
use App\Models\ProductSize;
use App\Mail\VerificationCodeMail;
use App\Mail\PasswordResetCodeMail;

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
            'remember' => 'sometimes|boolean',
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

        // Check if customer account is restricted or locked
        if ($customer->isRestricted()) {
            $isLocked = $customer->isLocked();
            
            if ($isLocked) {
                // Handle locked accounts
                if ($customer->restricted_until) {
                    // Temporarily locked
                    $restrictedUntil = $customer->restricted_until->format('M d, Y g:i A');
                    $message = "Your account has been temporarily locked until {$restrictedUntil}";
                } else {
                    // Permanently locked
                    $message = "Your account has been locked permanently";
                }
                
                // Add reason for locks
                if ($customer->restriction_reason) {
                    $reason = $customer->restriction_reason;
                    // Remove [LOCKED] prefix for display
                    if (str_starts_with($reason, '[LOCKED]')) {
                        $reason = trim(substr($reason, 8));
                    }
                    $message .= ".\nReason: " . $reason;
                }
            } else {
                // Handle non-lock restrictions
                $message = 'Your account has been restricted';
                
                if ($customer->restricted_until) {
                    $daysRemaining = $customer->days_remaining;
                    $restrictedUntil = $customer->restricted_until->format('M d, Y');
                    $message .= " until {$restrictedUntil}";
                    if ($daysRemaining > 0) {
                        $message .= " ({$daysRemaining} days remaining)";
                    }
                } else {
                    $message .= " permanently";
                }
                
                if ($customer->restriction_reason) {
                    $message .= ". Reason: " . $customer->restriction_reason;
                }
            }
            
            $message .= ".\nPlease contact support if you believe this is an error.";
            
            return response()->json([
                'success' => false,
                'message' => $message,
                'is_restricted' => true,
                'restriction_details' => [
                    'until' => $customer->restricted_until?->format('Y-m-d H:i:s'),
                    'reason' => $customer->restriction_reason,
                    'days_remaining' => $customer->days_remaining
                ]
            ], 403);
        }

        // Check if user account is deactivated
        if (!$customer->user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Please contact support for assistance.',
            ], 403);
        }

        // Manually log in the customer with remember me option
        $rememberMe = $request->filled('remember') && $request->boolean('remember');
        Auth::guard('customer')->login($customer, $rememberMe);

        // Store remember me choice in session for middleware to use
        $request->session()->put('customer_remember_me', $rememberMe);

        Log::info('Customer login successful', [
            'customer_id' => $customer->customer_id,
            'remember_me' => $rememberMe,
            'session_remember_flag' => $rememberMe
        ]);

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
            // Require Terms & Conditions to be accepted
            'terms' => 'accepted',
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
        // Clear remember me session flag
        $request->session()->forget('customer_remember_me');
        
        Auth::guard('customer')->logout();
        
        // Return a view with animated success message that redirects to login
        return view('auth.logout-success');
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
            Mail::to($customer->email)->send(new PasswordResetCodeMail($code, $customer->fullname));
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Password reset code sent to your email.',
            'code' => app()->environment('local') ? $code : null, // Only show in development
        ]);
    }

    /**
     * Show customer dashboard
     */
    public function dashboard()
    {
        $customer = Auth::guard('customer')->user();
        
        // Get all reservations by category
        $allReservations = $customer->reservations()
            ->with(['customer'])
            ->orderByRaw("CASE WHEN status IN ('pending', 'for_cancellation') THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->get();
            
        $pendingReservations = $customer->reservations()
            ->whereIn('status', ['pending', 'for_cancellation'])
            ->with(['customer'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        $completedReservations = $customer->reservations()
            ->where('status', 'completed')
            ->with(['customer'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        $cancelledReservations = $customer->reservations()
            ->where('status', 'cancelled')
            ->with(['customer'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('customer.dashboard', compact('customer', 'allReservations', 'pendingReservations', 'completedReservations', 'cancelledReservations'));
    }



    /**
     * Cancel customer reservation
     */
    public function cancelReservation(Request $request, $reservationId)
    {
        try {
            $customer = Auth::guard('customer')->user();
            
            // Find the reservation and verify it belongs to the customer
            $reservation = $customer->reservations()
                ->where('reservation_id', $reservationId)
                ->first();
                
            if (!$reservation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservation not found or you do not have permission to cancel it.'
                ], 404);
            }
            
            // Check if reservation can be cancelled
            if (!in_array($reservation->status, ['pending', 'confirmed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This reservation cannot be cancelled. Current status: ' . $reservation->status
                ]);
            }
            
            // Restore stock since reservation is being cancelled (stock was deducted on creation)
            if ($reservation->items && is_array($reservation->items)) {
                foreach ($reservation->items as $item) {
                    $sizeId = $item['size_id'] ?? null;
                    if ($sizeId) {
                        $size = \App\Models\ProductSize::find($sizeId);
                        if ($size) {
                            $size->increment('stock', $item['quantity']);
                            \Illuminate\Support\Facades\Log::info("Stock restored due to customer cancellation: Size ID {$sizeId}, Product: {$item['product_name']}, Quantity: {$item['quantity']}");
                        }
                    }
                }
            }

            // Update reservation status to cancelled
            $reservation->status = 'cancelled';
            $reservation->save();
            
            // Create notification for staff about cancellation
            \App\Models\Notification::create([
                'type' => 'reservation_cancelled',
                'title' => 'Reservation Cancelled by Customer',
                'message' => "Reservation {$reservation->reservation_id} has been cancelled by the customer.",
                'target_role' => 'all', // Notify all staff members
                'data' => json_encode([
                    'reservation_id' => $reservation->reservation_id,
                    'customer_name' => $customer->fullname,
                    'customer_email' => $customer->email,
                    'cancelled_at' => now()->toISOString()
                ]),
                'is_read' => false
            ]);
            
            Log::info('Customer cancelled reservation', [
                'reservation_id' => $reservation->reservation_id,
                'customer_id' => $customer->customer_id,
                'customer_email' => $customer->email
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Reservation cancelled successfully.'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error cancelling reservation', [
                'reservation_id' => $reservationId,
                'customer_id' => $customer->customer_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while cancelling the reservation. Please try again.'
            ], 500);
        }
    }
}
