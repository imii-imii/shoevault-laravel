<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // If user is not authenticated, continue
        if (!$user) {
            return $next($request);
        }
        
        // If already on the password change page or logout, allow through
        if ($request->routeIs('force-password-change') || 
            $request->routeIs('force-password-change.update') || 
            $request->routeIs('logout')) {
            return $next($request);
        }
        
        // Check if user has a default password based on their role
        $defaultPasswords = [
            'manager' => 'manager123',
            'cashier' => 'cashier123',
            'employee' => 'employee123',
        ];
        
        if (isset($defaultPasswords[$user->role])) {
            $defaultPassword = $defaultPasswords[$user->role];
            
            // Check if current password matches the default password
            if (Hash::check($defaultPassword, $user->password)) {
                // Redirect to force password change page
                return redirect()->route('force-password-change')
                    ->with('message', 'You must change your default password before accessing the system.');
            }
        }
        
        return $next($request);
    }
}
