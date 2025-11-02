<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class Notification extends Model
{
    protected $fillable = [
        'type',
        'title',
        'message',
        'target_role',
        'data',
        'is_read',
        'read_at',
        'icon',
        'priority'
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Scopes
    public function scopeForRole(Builder $query, string $role): Builder
    {
        return $query->where(function ($q) use ($role) {
            $q->where('target_role', $role)
              ->orWhere('target_role', 'all');
        });
    }

    public function scopeUnreadForUser(Builder $query, string $userId): Builder
    {
        return $query->whereDoesntHave('reads', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    public function scopeReadByUser(Builder $query, string $userId): Builder
    {
        return $query->whereHas('reads', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    // Relationships
    public function reads(): HasMany
    {
        return $this->hasMany(NotificationRead::class);
    }

    public function readByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'notification_reads')
            ->withPivot('read_at')
            ->withTimestamps();
    }

    // Methods
    public function markAsReadByUser(string $userId): void
    {
        // Create a read record if it doesn't exist
        \App\Models\NotificationRead::firstOrCreate([
            'notification_id' => $this->id,
            'user_id' => $userId
        ], [
            'read_at' => Carbon::now()
        ]);
    }

    public function isReadByUser(string $userId): bool
    {
        return $this->reads()->where('user_id', $userId)->exists();
    }

    public function getReadAtForUser(string $userId): ?Carbon
    {
        $read = $this->reads()->where('user_id', $userId)->first();
        return $read ? $read->read_at : null;
    }

    public function isExpired(int $days = 30): bool
    {
        return $this->created_at->lt(Carbon::now()->subDays($days));
    }

    // Static methods for creating notifications
    public static function createLowStockNotification(array $productData): self
    {
        return static::create([
            'type' => 'low_stock',
            'title' => 'Low Stock Alert',
            'message' => "Product '{$productData['name']}' is running low on stock (Size: {$productData['size']}, Stock: {$productData['stock']})",
            'target_role' => 'all', // Notify all roles about low stock
            'data' => $productData,
            'icon' => 'fas fa-exclamation-triangle',
            'priority' => $productData['stock'] <= 5 ? 'high' : 'normal'
        ]);
    }

    public static function createNewReservationNotification(array $reservationData): self
    {
        return static::create([
            'type' => 'new_reservation',
            'title' => 'New Reservation',
            'message' => "New reservation created: {$reservationData['reservation_id']} for {$reservationData['customer_name']}",
            'target_role' => 'all', // Notify all roles about new reservations
            'data' => $reservationData,
            'icon' => 'fas fa-calendar-plus',
            'priority' => 'normal'
        ]);
    }

    public static function createReservationExpiringNotification(array $reservationData): self
    {
        return static::create([
            'type' => 'reservation_expiring',
            'title' => 'Reservation Expiring',
            'message' => "Reservation {$reservationData['reservation_id']} will expire soon",
            'target_role' => 'all',
            'data' => $reservationData,
            'icon' => 'fas fa-clock',
            'priority' => 'high'
        ]);
    }
}
