<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class SessionLifetimeMiddleware
{
    /**
     * Handle an incoming request.
     * Set different session behaviors for employees vs customers.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (Auth::check()) {
            $user = Auth::user();
            
            // For employees (owner, manager, cashier), force session-only cookies
            if (in_array($user->role, ['owner', 'manager', 'cashier'])) {
                // Force session to expire when browser closes
                Config::set('session.expire_on_close', true);
                Config::set('session.lifetime', 120); // 2 hours max, but expires on browser close
                
                // Set cookie to expire when browser closes (session cookie)
                $cookieName = Config::get('session.cookie');
                if ($request->cookies->has($cookieName)) {
                    cookie()->queue(cookie(
                        $cookieName,
                        $request->cookie($cookieName),
                        0, // 0 means session cookie (expires when browser closes)
                        Config::get('session.path'),
                        Config::get('session.domain'),
                        Config::get('session.secure'),
                        Config::get('session.http_only'),
                        false,
                        Config::get('session.same_site')
                    ));
                }
            }
        }
        
        // Check if customer is authenticated
        if (Auth::guard('customer')->check()) {
            // Check if customer used "Remember Me" during login
            if ($request->session()->get('customer_remember_me', false)) {
                // Customer used "Remember Me" - allow longer sessions
                Config::set('session.expire_on_close', false);
                Config::set('session.lifetime', 43200); // 30 days for remembered customers
            } else {
                // Customer didn't use "Remember Me" - use shorter session
                Config::set('session.expire_on_close', true);
                Config::set('session.lifetime', 120); // 2 hours, expires on browser close
            }
        } else {
            // If customer is not authenticated, make sure we use default session settings
            // This handles cases where session expired naturally
            if ($request->session()->has('customer_remember_me')) {
                $request->session()->forget('customer_remember_me');
            }
        }

        return $next($request);
    }
}
