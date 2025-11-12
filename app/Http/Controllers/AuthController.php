<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Add debug logging
        Log::info('Staff login attempt', [
            'username' => $request->username,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Find user by username
        $user = User::where('username', $request->username)
                   ->where('is_active', true)
                   ->first();

        Log::info('User lookup result', [
            'username' => $request->username,
            'user_found' => $user ? true : false,
            'user_id' => $user ? $user->user_id : null,
            'user_role' => $user ? $user->role : null
        ]);

        if ($user && Hash::check($request->password, $user->password)) {
            Log::info('Password check passed, checking operating hours');
            
            // Check operating hours for managers and cashiers before login
            if (in_array($user->role, ['manager', 'cashier'])) {
                if (!\App\Models\SystemSettings::canAccessSystem($user->role)) {
                    $operatingStart = \App\Models\SystemSettings::get('operating_hours_start', '10:00');
                    $operatingEnd = \App\Models\SystemSettings::get('operating_hours_end', '19:00');
                    
                    Log::warning('Login blocked - outside operating hours', [
                        'user_id' => $user->user_id,
                        'user_role' => $user->role,
                        'current_time' => Carbon::now()->format('H:i:s'),
                        'operating_hours' => "$operatingStart - $operatingEnd",
                        'emergency_access_active' => \App\Models\SystemSettings::isEmergencyAccessActive()
                    ]);

                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => "Access denied. The system is only accessible during operating hours ($operatingStart - $operatingEnd). Please contact the owner for emergency access.",
                            'operating_hours' => [
                                'start' => $operatingStart,
                                'end' => $operatingEnd,
                                'current_time' => Carbon::now()->format('H:i:s')
                            ]
                        ], 403);
                    }

                    return back()->withErrors([
                        'access_denied' => "You cannot access the system outside operating hours ($operatingStart - $operatingEnd). Please try again during business hours or contact the owner for emergency access."
                    ])->withInput($request->except('password'));
                }
            }
            
            // For employees (owner, manager, cashier), never use "remember me" - always require login after browser close
            Auth::login($user, false);
            
            Log::info('Auth::login() completed', [
                'authenticated' => Auth::check(),
                'auth_user_id' => Auth::check() ? Auth::user()->user_id : null,
                'remember_token_used' => false
            ]);

            // Check if user has default password
            $defaultPasswords = [
                'manager' => 'manager123',
                'cashier' => 'cashier123',
                'employee' => 'employee123',
            ];

            if (isset($defaultPasswords[$user->role]) && Hash::check($defaultPasswords[$user->role], $user->password)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Default password detected. Redirecting to password change.',
                        'redirect' => route('force-password-change')
                    ]);
                }
                return redirect()->route('force-password-change')
                    ->with('message', 'You must change your default password before accessing the system.');
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'redirect' => $this->getRedirectUrl($user),
                    'user' => [
                        'username' => $user->username,
                        'role' => $user->role,
                        'user_id' => $user->user_id
                    ]
                ]);
            }

            Log::info('Attempting redirect for user', [
                'user_role' => $user->role,
                'redirect_route' => $this->getRedirectUrl($user)
            ]);

            return $this->redirectBasedOnRole($user);
        }

        Log::warning('Login failed', [
            'username' => $request->username,
            'user_found' => $user ? true : false,
            'password_check' => $user ? Hash::check($request->password, $user->password) : false
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        return back()->withErrors([
            'username' => 'Invalid credentials provided.',
        ])->withInput($request->except('password'));
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Redirect user based on their role
     */
    private function redirectBasedOnRole(User $user)
    {
        switch ($user->role) {
            case 'cashier':
                return redirect()->route('pos.dashboard');
            case 'manager':
                return redirect()->route('inventory.dashboard');
            case 'owner':
                return redirect()->route('owner.dashboard');
            default:
                return redirect()->route('login');
        }
    }

    /**
     * Get redirect URL for AJAX responses
     */
    private function getRedirectUrl(User $user)
    {
        switch ($user->role) {
            case 'cashier':
                return route('pos.dashboard');
            case 'manager':
                return route('inventory.dashboard');
            case 'owner':
                return route('owner.dashboard');
            default:
                return route('login');
        }
    }

    /**
     * Show the force password change form
     */
    public function forcePasswordChange()
    {
        return view('auth.force-password-change');
    }

    /**
     * Handle forced password change
     */
    public function updateForcedPassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed|regex:/^(?=.*[A-Za-z])(?=.*\d)/',
        ], [
            'new_password.regex' => 'The new password must contain at least one letter and one number.',
            'new_password.confirmed' => 'The new password confirmation does not match.',
        ]);

        $user = Auth::user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        // Ensure new password is different from current
        if (Hash::check($request->new_password, $user->password)) {
            return back()->withErrors(['new_password' => 'The new password must be different from your current password.']);
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        Log::info('User password changed after forced change', [
            'user_id' => $user->user_id,
            'username' => $user->username,
            'role' => $user->role
        ]);

        // Redirect to appropriate dashboard
        return redirect($this->getRedirectUrl($user))
            ->with('success', 'Password changed successfully! Welcome to the system.');
    }
}
