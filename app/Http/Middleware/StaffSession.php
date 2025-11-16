<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class StaffSession
{
    /**
     * Handle an incoming request.
     * 
     * This middleware ensures staff/admin routes use the default session cookie
     * separate from customer routes, preventing session conflicts.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $defaultCookieName = Str::slug(env('APP_NAME', 'laravel')) . '-session';
        $customerCookieName = Str::slug(env('APP_NAME', 'laravel')) . '-customer-session';
        
        // Ensure we're using the default (staff) session cookie
        config(['session.cookie' => $defaultCookieName]);
        
        // If we have a customer session cookie but no staff cookie, we need to start fresh
        // This prevents session conflicts when switching between customer and staff logins
        if ($request->hasCookie($customerCookieName) && !$request->hasCookie($defaultCookieName)) {
            // Regenerate with default cookie name
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
        
        return $next($request);
    }
}

