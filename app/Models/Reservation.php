<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Notification;
use Carbon\Carbon;

class Reservation extends Model
{
    protected $primaryKey = 'reservation_id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Boot method to register model events
     */
    protected static function boot()
    {
        parent::boot();

        // Generate custom ID before creating
        static::creating(function ($reservation) {
            if (empty($reservation->reservation_id)) {
                $reservation->reservation_id = self::generateReservationId();
            }
        });

        // Create notification when a new reservation is created
        static::created(function ($reservation) {
            try {
                Notification::createNewReservationNotification([
                    'reservation_id' => $reservation->reservation_id,
                    'customer_name' => $reservation->customer_name ?? 'Unknown Customer',
                    'total_amount' => $reservation->total_amount ?? 0,
                    'status' => $reservation->status,
                    'created_at' => $reservation->created_at->toISOString()
                ]);
            } catch (\Exception $e) {
                // Log error but don't fail the reservation creation
                \Illuminate\Support\Facades\Log::error('Failed to create new reservation notification', [
                    'reservation_id' => $reservation->reservation_id,
                    'error' => $e->getMessage()
                ]);
            }
        });
    }

    /**
     * Get the prefix for auto-generated IDs
     */
    protected function getIdPrefix(): string
    {
        return 'RSV';
    }

    /**
     * Generate unique reservation ID with date-based format
     */
    public static function generateReservationId()
    {
        $date = Carbon::now()->format('Ymd');
        $count = self::whereDate('created_at', Carbon::today())->count() + 1;
        return 'RSV-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    protected $fillable = [
        'reservation_id',
        'customer_id', // Foreign key to customers table
        'items', // JSON field for multiple products
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

    /**
     * Get the customer that owns this reservation
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function product(): BelongsTo
    {
        // Since we store product details directly, this relationship is optional
        // Uses the main products table with inventory_type filtering
        return $this->belongsTo(Product::class, 'product_id')->withDefault();
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

    /**
     * Check if a customer has any pending reservations
     */
    public static function customerHasPendingReservations($email)
    {
        return static::whereHas('customer', function($query) use ($email) {
                    $query->where('email', $email);
                })
                ->where('status', 'pending')
                ->exists();
    }

    /**
     * Get customer's pending reservations
     */
    public static function getCustomerPendingReservations($email)
    {
        return static::whereHas('customer', function($query) use ($email) {
                    $query->where('email', $email);
                })
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();
    }

    /**
     * Accessor for backward compatibility - customer_name
     */
    public function getCustomerNameAttribute()
    {
        return $this->customer ? $this->customer->fullname : null;
    }

    /**
     * Accessor for backward compatibility - customer_email
     */
    public function getCustomerEmailAttribute()
    {
        return $this->customer ? $this->customer->email : null;
    }

    /**
     * Accessor for backward compatibility - customer_phone
     */
    public function getCustomerPhoneAttribute()
    {
        return $this->customer ? $this->customer->phone_number : null;
    }
}
