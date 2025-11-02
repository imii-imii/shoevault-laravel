<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Notification;
use App\Services\NotificationService;

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
     * Boot method to register model events
     */
    protected static function boot()
    {
        parent::boot();

        // Check for low stock when product stock is updated
        static::updated(function ($productSize) {
            if ($productSize->isDirty('stock') && $productSize->isLowStock()) {
                try {
                    // Check if we already have a recent notification for this specific product size
                    $existingNotification = Notification::where('type', 'low_stock')
                        ->where('data->product_size_id', $productSize->product_size_id)
                        ->where('created_at', '>=', now()->subHours(24))
                        ->first();

                    if (!$existingNotification) {
                        $product = $productSize->product;
                        Notification::createLowStockNotification([
                            'product_id' => $product->id,
                            'product_size_id' => $productSize->product_size_id,
                            'name' => $product->name,
                            'size' => $productSize->size,
                            'stock' => $productSize->stock,
                            'category' => $product->category
                        ]);
                    }
                } catch (\Exception $e) {
                    // Log error but don't fail the stock update
                    \Illuminate\Support\Facades\Log::error('Failed to create low stock notification', [
                        'product_size_id' => $productSize->product_size_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });
    }

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
