<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSize extends Model
{
    protected $fillable = [
        'product_id',
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
     * Get the product that owns this size variant.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Check if this size is in stock.
     */
    public function isInStock(): bool
    {
        return $this->stock > 0 && $this->is_available;
    }

    /**
     * Check if this size is low stock based on product's min_stock.
     */
    public function isLowStock(): bool
    {
        return $this->stock <= $this->product->min_stock && $this->stock > 0;
    }

    /**
     * Get the effective price for this size (base price + adjustment).
     */
    public function getEffectivePrice(): float
    {
        return $this->product->price + $this->price_adjustment;
    }
}
