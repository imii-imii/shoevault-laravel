<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSize extends Model
{
    protected $primaryKey = 'product_size_id'; // Updated primary key name
    
    protected $fillable = [
        'product_id',
        'size',
        'stock',
        'is_available'
        // Removed 'price_adjustment' as requested
    ];

    protected $casts = [
        'is_available' => 'boolean'
        // Removed 'price_adjustment' cast
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
     * Check if this size is low stock based on threshold of 5.
     */
    public function isLowStock(): bool
    {
        return $this->stock <= 5 && $this->stock > 0;
    }

    /**
     * Get the effective price for this size (just the base product price since price_adjustment removed).
     */
    public function getEffectivePrice(): float
    {
        return $this->product->price; // No more price adjustment
    }
}
