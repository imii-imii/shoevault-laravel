<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CustomerSession
{
    /**
     * Handle an incoming request.
     * 
     * This middleware ensures customer routes use a separate session cookie
     * from staff/admin routes, preventing session conflicts.
     * 
     * This must run BEFORE session is started, so we regenerate the session
     * with the new cookie name if needed.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $customerCookieName = Str::slug(env('APP_NAME', 'laravel')) . '-customer-session';
        $defaultCookieName = Str::slug(env('APP_NAME', 'laravel')) . '-session';
        
        // Set customer session cookie name
        config(['session.cookie' => $customerCookieName]);
        
        // If we have a staff session cookie but no customer cookie, we need to start fresh
        // This prevents the "double login" issue
        if ($request->hasCookie($defaultCookieName) && !$request->hasCookie($customerCookieName)) {
            // Store any data we might want to preserve
            $intendedUrl = $request->session()->get('url.intended');
            
            // Regenerate with new cookie name
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            // Restore intended URL if it exists
            if ($intendedUrl) {
                $request->session()->put('url.intended', $intendedUrl);
            }
        }
        
        return $next($request);
    }
}

