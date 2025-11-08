<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;

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
            Log::info('Password check passed, attempting Auth::login()');
            
            Auth::login($user, $request->filled('remember'));
            
            Log::info('Auth::login() completed', [
                'authenticated' => Auth::check(),
                'auth_user_id' => Auth::check() ? Auth::user()->user_id : null
            ]);
            
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
}
