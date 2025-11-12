<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SystemSettings extends Model
{
    protected $table = 'system_settings';
    
    protected $fillable = [
        'key',
        'value',
        'type',
        'description'
    ];

    /**
     * Cast attributes to appropriate types
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get a setting value with type casting
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value, string $type = 'string')
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => static::formatValue($value, $type),
                'type' => $type
            ]
        );
    }

    /**
     * Cast value to appropriate type
     */
    protected static function castValue($value, string $type)
    {
        if ($value === null) {
            return null;
        }

        return match($type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'datetime' => $value ? Carbon::parse($value) : null,
            'json' => json_decode($value, true),
            default => (string) $value
        };
    }

    /**
     * Format value for storage
     */
    protected static function formatValue($value, string $type)
    {
        if ($value === null) {
            return null;
        }

        return match($type) {
            'boolean' => $value ? '1' : '0',
            'datetime' => $value instanceof Carbon ? $value->toDateTimeString() : $value,
            'json' => json_encode($value),
            default => (string) $value
        };
    }

    /**
     * Check if current time is within operating hours
     */
    public static function isWithinOperatingHours(): bool
    {
        $enabled = static::get('operating_hours_enabled', true);
        
        if (!$enabled) {
            return true; // If operating hours are disabled, always allow access
        }

        $now = Carbon::now();
        $startTime = static::get('operating_hours_start', '10:00');
        $endTime = static::get('operating_hours_end', '19:00');

        $start = Carbon::createFromFormat('H:i', $startTime);
        $end = Carbon::createFromFormat('H:i', $endTime);

        return $now->between($start, $end);
    }

    /**
     * Check if emergency access is currently active
     */
    public static function isEmergencyAccessActive(): bool
    {
        $enabled = static::get('emergency_access_enabled', false);
        
        if (!$enabled) {
            return false;
        }

        $expiresAt = static::get('emergency_access_expires_at');
        
        if (!$expiresAt) {
            return false;
        }

        return Carbon::now()->lt($expiresAt);
    }

    /**
     * Enable emergency access for specified duration
     */
    public static function enableEmergencyAccess(int $durationMinutes = null): bool
    {
        $duration = $durationMinutes ?: static::get('emergency_access_duration', 30);
        $expiresAt = Carbon::now()->addMinutes($duration);

        static::set('emergency_access_enabled', true, 'boolean');
        static::set('emergency_access_expires_at', $expiresAt, 'datetime');

        return true;
    }

    /**
     * Disable emergency access
     */
    public static function disableEmergencyAccess(): bool
    {
        static::set('emergency_access_enabled', false, 'boolean');
        static::set('emergency_access_expires_at', null, 'datetime');

        return true;
    }

    /**
     * Get remaining emergency access time in minutes
     */
    public static function getEmergencyAccessRemainingMinutes(): int
    {
        if (!static::isEmergencyAccessActive()) {
            return 0;
        }

        $expiresAt = static::get('emergency_access_expires_at');
        return Carbon::now()->diffInMinutes($expiresAt, false);
    }

    /**
     * Check if manager/cashier can access the system
     * Only checks emergency access if outside operating hours
     */
    public static function canAccessSystem(string $userRole): bool
    {
        // Owners can always access
        if ($userRole === 'owner') {
            return true;
        }

        // If within operating hours, allow access
        if (static::isWithinOperatingHours()) {
            return true;
        }

        // Outside operating hours - check if manager/cashier and if emergency access is active
        if (in_array($userRole, ['manager', 'cashier'])) {
            return static::isEmergencyAccessActive();
        }

        return false;
    }
}