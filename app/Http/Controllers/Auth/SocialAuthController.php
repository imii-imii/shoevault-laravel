<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use App\Models\Customer;
use App\Models\User;

class SocialAuthController extends Controller
{
    public function redirect(Request $request)
    {
        try {
            // Store the return URL if provided
            if ($request->has('return')) {
                session(['login_return_url' => $request->get('return')]);
            } elseif ($request->headers->get('referer')) {
                // Store referer as return URL if no explicit return URL
                $referer = $request->headers->get('referer');
                if (!str_contains($referer, '/customer/login')) {
                    session(['login_return_url' => $referer]);
                }
            }
            
            return Socialite::driver('google')->redirect();
        } catch (\Exception $e) {
            Log::error('Google OAuth redirect error: ' . $e->getMessage());
            return redirect()->route('customer.login')->with('error', 'Google Sign-In is not configured yet. Please contact the administrator.');
        }
    }

    public function callback(Request $request)
    {
        try {
            Log::info('Google OAuth callback started', [
                'url' => $request->fullUrl(),
                'params' => $request->all(),
                'session_id' => session()->getId(),
            ]);
            
            // Handle state verification issues
            try {
                $googleUser = Socialite::driver('google')->user();
            } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
                Log::warning('Invalid state exception, clearing session and retrying', [
                    'error' => $e->getMessage(),
                    'session_id' => session()->getId(),
                ]);
                
                // Clear any existing OAuth session data and try again
                session()->flush();
                session()->regenerate();
                
                // Redirect back to start OAuth flow again
                return redirect()->route('auth.google')->with('error', 'Session expired. Please try signing in again.');
            }
            
            Log::info('Google OAuth callback received', [
                'google_id' => $googleUser->id,
                'email' => $googleUser->email,
                'name' => $googleUser->name,
            ]);
            
            DB::beginTransaction();
            
            // Check if customer exists with this Google ID
            $customer = Customer::where('google_id', $googleUser->id)->first();
            
            if (!$customer) {
                // Check if customer exists with this email
                $customer = Customer::where('email', $googleUser->email)->first();
                
                if ($customer) {
                    // Link Google account to existing customer
                    $customer->google_id = $googleUser->id;
                    $customer->avatar = $googleUser->avatar;
                    $customer->email_verified_at = now(); // Mark as verified since Google verified it
                    $customer->save();
                } else {
                    // Create new customer account
                    // First create user record
                    $user = User::create([
                        'username' => 'google_' . $googleUser->id,
                        'password' => bcrypt('google_auth_' . time()),
                        'role' => 'customer',
                        'is_active' => true,
                        'email_verified_at' => now(),
                    ]);

                    // Then create customer record
                    $customer = Customer::create([
                        'user_id' => $user->user_id,
                        'fullname' => $googleUser->name,
                        'email' => $googleUser->email,
                        'google_id' => $googleUser->id,
                        'avatar' => $googleUser->avatar,
                        'email_verified_at' => now(),
                    ]);
                }
            } else {
                // Update existing customer info
                $customer->fullname = $googleUser->name;
                $customer->email = $googleUser->email;
                $customer->avatar = $googleUser->avatar;
                $customer->email_verified_at = now();
                $customer->save();
            }
            
            // Ensure customer is fresh from database
            $customer = $customer->fresh();
            
            // Login the customer
            Auth::guard('customer')->login($customer, true); // Remember the user
            
            // Debug: Check if login was successful
            $isLoggedIn = Auth::guard('customer')->check();
            Log::info('Google OAuth login attempt', [
                'customer_id' => $customer->customer_id,
                'email' => $customer->email,
                'login_successful' => $isLoggedIn,
                'session_id' => session()->getId(),
                'auth_user_id' => Auth::guard('customer')->id(),
            ]);
            
            if (!$isLoggedIn) {
                throw new \Exception('Failed to login customer after OAuth');
            }
            
            DB::commit();
            
            // Redirect back to the page they came from or portal
            $returnUrl = session('login_return_url', route('reservation.portal'));
            session()->forget('login_return_url');
            
            Log::info('Google OAuth success, redirecting to: ' . $returnUrl);
            
            return redirect($returnUrl)->with('success', 'Successfully signed in with Google!');
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Google OAuth callback error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('customer.login')->with('error', 'Something went wrong with Google Sign-In. Please try again.');
        }
    }
}