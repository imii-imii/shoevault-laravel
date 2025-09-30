<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Find user by username
        $user = User::where('username', $request->username)
                   ->where('is_active', true)
                   ->first();

        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user, $request->filled('remember'));
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'redirect' => $this->getRedirectUrl($user),
                    'user' => [
                        'name' => $user->name,
                        'role' => $user->role,
                        'permissions' => $user->permissions
                    ]
                ]);
            }

            return $this->redirectBasedOnRole($user);
        }

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
            case 'admin':
                return redirect()->route('pos.dashboard'); // Default to POS for admin
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
            case 'admin':
                return route('pos.dashboard');
            default:
                return route('login');
        }
    }
}
