<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationProductSize extends Model
{
    protected $fillable = [
        'reservation_product_id',
        'size',
        'stock',
        'price_adjustment',
        'is_available'
    ];

    protected $casts = [
        'price_adjustment' => 'decimal:2',
        'is_available' => 'boolean'
    ];

    /**
     * Get the reservation product that owns this size variant.
     */
    public function reservationProduct(): BelongsTo
    {
        return $this->belongsTo(ReservationProduct::class, 'reservation_product_id');
    }

    /**
     * Calculate the total price for this size variant
     */
    public function getTotalPriceAttribute()
    {
        return $this->reservationProduct->price + $this->price_adjustment;
    }

    /**
     * Get the effective price for this size variant (includes price adjustment)
     */
    public function getEffectivePrice()
    {
        return $this->reservationProduct->price + $this->price_adjustment;
    }

    /**
     * Check if this size variant is in stock
     */
    public function isInStock()
    {
        return $this->stock > 0 && $this->is_available;
    }

    /**
     * Reduce stock by a given amount
     */
    public function reduceStock($quantity = 1)
    {
        if ($this->stock >= $quantity) {
            $this->stock -= $quantity;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Increase stock by a given amount
     */
    public function increaseStock($quantity = 1)
    {
        $this->stock += $quantity;
        $this->save();
        return true;
    }
}
