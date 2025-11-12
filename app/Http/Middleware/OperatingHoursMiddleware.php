<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\SystemSettings;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class OperatingHoursMiddleware
{
    /**
     * Handle an incoming request.
     * Restrict manager and cashier access outside operating hours unless emergency access is enabled.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $userRole = $user->role;

        Log::info('OperatingHoursMiddleware: Checking access', [
            'user_id' => $user->user_id,
            'user_role' => $userRole,
            'route' => $request->path(),
            'time' => Carbon::now()->format('H:i:s')
        ]);

        // Check if user can access the system
        if (!SystemSettings::canAccessSystem($userRole)) {
            $operatingStart = SystemSettings::get('operating_hours_start', '10:00');
            $operatingEnd = SystemSettings::get('operating_hours_end', '19:00');
            
            Log::warning('OperatingHoursMiddleware: Access denied - outside operating hours', [
                'user_id' => $user->user_id,
                'user_role' => $userRole,
                'current_time' => Carbon::now()->format('H:i:s'),
                'operating_hours' => "$operatingStart - $operatingEnd",
                'emergency_access_active' => SystemSettings::isEmergencyAccessActive()
            ]);

            // Log out the user
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Return appropriate response
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Access denied. The system is only accessible during operating hours ($operatingStart - $operatingEnd). Please contact your administrator for emergency access.",
                    'operating_hours' => [
                        'start' => $operatingStart,
                        'end' => $operatingEnd,
                        'current_time' => Carbon::now()->format('H:i:s')
                    ]
                ], 403);
            }

            return redirect()->route('login')->withErrors([
                'access_denied' => "You cannot access the system outside operating hours ($operatingStart - $operatingEnd). Please try again during business hours or contact your administrator for emergency access."
            ]);
        }

        // Auto-disable expired emergency access
        if (SystemSettings::get('emergency_access_enabled', false) && !SystemSettings::isEmergencyAccessActive()) {
            Log::info('OperatingHoursMiddleware: Auto-disabling expired emergency access');
            SystemSettings::disableEmergencyAccess();
        }

        return $next($request);
    }
}
