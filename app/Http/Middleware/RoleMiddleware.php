<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        Log::info('RoleMiddleware: Processing request', [
            'route' => $request->path(),
            'required_roles' => $roles,
            'authenticated' => Auth::check()
        ]);

        if (!Auth::check()) {
            Log::warning('RoleMiddleware: User not authenticated, redirecting to login');
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        Log::info('RoleMiddleware: User details', [
            'user_id' => $user->user_id,
            'username' => $user->username,
            'user_role' => $user->role,
            'required_roles' => $roles
        ]);
        
        // Owner has access to everything (removed admin role)
        if ($user->hasRole('owner')) {
            Log::info('RoleMiddleware: Owner access granted');
            return $next($request);
        }

        // Check if user has any of the required roles
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                Log::info('RoleMiddleware: Access granted', ['matched_role' => $role]);
                return $next($request);
            }
        }

        Log::warning('RoleMiddleware: Access denied', [
            'user_role' => $user->role,
            'required_roles' => $roles
        ]);

        abort(403, 'Access denied. Insufficient permissions.');
    }
}
