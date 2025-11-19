<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    protected $table = 'transaction_items';

    protected $fillable = [
        'transaction_id',
        'product_size_id',
        'product_name',
        'product_brand',
        'product_color',
        'product_category',
        'quantity',
        'size',
        'unit_price',
        'cost_price',
        'discount_amount',
        'subtotal'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    // Relationships
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'transaction_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productSize(): BelongsTo
    {
        return $this->belongsTo(ProductSize::class, 'product_size_id', 'product_size_id');
    }

    /**
     * Calculate subtotal after discount
     * discount_amount is always the exact currency amount to subtract
     */
    public function calculateSubtotal(): float
    {
        $baseAmount = $this->quantity * $this->unit_price;
        return round($baseAmount - $this->discount_amount, 2);
    }

    /**
     * Set discount by exact currency amount
     */
    public function setDiscountAmount(float $amount): void
    {
        $this->discount_amount = round($amount, 2);
        $this->subtotal = $this->calculateSubtotal();
    }

    /**
     * Set discount by percentage - converts to exact currency amount
     * The percentage is calculated on the frontend and converted to exact amount before saving
     */
    public function setDiscountByPercentage(float $percentage): void
    {
        $baseAmount = $this->quantity * $this->unit_price;
        $discountAmount = round($baseAmount * ($percentage / 100), 2);
        $this->setDiscountAmount($discountAmount);
    }

    /**
     * Get discount percentage (calculated from discount amount)
     */
    public function getDiscountPercentage(): float
    {
        $baseAmount = $this->quantity * $this->unit_price;
        return $baseAmount > 0 ? round(($this->discount_amount / $baseAmount) * 100, 2) : 0;
    }

    /**
     * Get the base amount before discount
     */
    public function getBaseAmount(): float
    {
        return $this->quantity * $this->unit_price;
    }
}