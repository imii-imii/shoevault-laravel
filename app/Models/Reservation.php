<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = [
        'reservation_id',
        'items', // JSON field for multiple products
        'customer_name',
        'customer_email',
        'customer_phone',
        'total_amount',
        'pickup_date',
        'pickup_time',
        'status',
        'notes',
        'reserved_at'
    ];

    protected $casts = [
        'pickup_date' => 'date',
        'reserved_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'items' => 'array', // Cast JSON to array automatically
    ];

    public function product(): BelongsTo
    {
        // Since we store product details directly, this relationship is optional
        // Only use if product_id is set and references reservation_products table
        return $this->belongsTo(ReservationProduct::class, 'product_id')->withDefault();
    }

    /**
     * Get the reservation status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'confirmed' => 'bg-blue-100 text-blue-800', 
            'completed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Scope for pending reservations
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for completed reservations
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for cancelled reservations
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
}
