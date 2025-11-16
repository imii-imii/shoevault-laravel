<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class DetectSessionType
{
    /**
     * Handle an incoming request.
     * 
     * This middleware runs BEFORE the session is started to detect whether
     * this is a customer or staff route and set the appropriate session cookie name.
     * 
     * This prevents session conflicts between customer and staff logins.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();
        
        // Define customer routes - these will use customer session
        $customerRoutes = [
            'customer',
            'portal',
            'form',
            'product',
            'category',
            'brand',
            'size-converter',
            'api/products',
            'api/check-pending-reservations',
            'api/reservations',
            'api/send-reservation-email',
        ];
        
        // Define staff routes - these will use default session
        $staffRoutes = [
            'login',
            'logout',
            'pos',
            'owner',
            'manager',
            'force-password-change',
        ];
        
        // Check if this is a customer route
        $isCustomerRoute = $path === '/' || Str::startsWith($path, $customerRoutes);
        
        // Check if this is a staff route
        $isStaffRoute = Str::startsWith($path, $staffRoutes);
        
        // Set appropriate session cookie name and behavior
        if ($isCustomerRoute && !$isStaffRoute) {
            // Customer routes: use customer session cookie (persistent by default)
            config([
                'session.cookie' => Str::slug(env('APP_NAME', 'laravel')) . '-customer-session',
                'session.expire_on_close' => false, // Customers stay logged in
                'session.lifetime' => 10080, // 7 days default
            ]);
        } else {
            // Staff routes: use default session cookie (expire on browser close)
            config([
                'session.cookie' => Str::slug(env('APP_NAME', 'laravel')) . '-session',
                'session.expire_on_close' => true, // Staff logout on browser close
                'session.lifetime' => 120, // 2 hours max
            ]);
        }
        
        return $next($request);
    }
}

