<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'receipt_number',
        'sale_type',
        'reservation_id',
        'user_id',
        'subtotal',
        'tax',
        'discount_amount',
        'total',
        'amount_paid',
        'change_amount',
        'payment_method',
        'items',
        'total_items',
        'total_quantity',
        'status',
        'sale_date',
        'notes'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'items' => 'array',
        'sale_date' => 'datetime'
    ];

    /**
     * Get the user (cashier) who made this sale
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate unique transaction ID
     */
    public static function generateTransactionId()
    {
        $date = Carbon::now()->format('Ymd');
        $count = self::whereDate('created_at', Carbon::today())->count() + 1;
        return 'TXN-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique receipt number
     */
    public static function generateReceiptNumber()
    {
        $count = self::count() + 1;
        return 'RCP-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate total profit from items (for future analytics)
     */
    public function getTotalProfitAttribute()
    {
        return collect($this->items)->sum(function ($item) {
            $costPrice = $item['cost_price'] ?? 0;
            return ($item['unit_price'] - $costPrice) * $item['quantity'];
        });
    }

    /**
     * Set items with validation
     */
    public function setItemsAttribute($value)
    {
        // Ensure each item has required fields
        $validatedItems = array_map(function($item) {
            return [
                'product_id' => $item['product_id'] ?? null,
                'size_id' => $item['size_id'] ?? null,
                'product_name' => $item['product_name'] ?? 'Unknown Product',
                'product_brand' => $item['product_brand'] ?? '',
                'product_size' => $item['product_size'] ?? '',
                'product_color' => $item['product_color'] ?? '',
                'product_category' => $item['product_category'] ?? 'uncategorized',
                'unit_price' => (float) ($item['unit_price'] ?? 0),
                'quantity' => (int) ($item['quantity'] ?? 1),
                'subtotal' => (float) ($item['subtotal'] ?? 0),
                'cost_price' => (float) ($item['cost_price'] ?? 0),
                'sku' => $item['sku'] ?? null
            ];
        }, is_array($value) ? $value : []);
        
        $this->attributes['items'] = json_encode($validatedItems);
    }

    /**
     * Format currency values
     */
    public function getFormattedTotalAttribute()
    {
        return '₱' . number_format($this->total, 2);
    }

    public function getFormattedSubtotalAttribute()
    {
        return '₱' . number_format($this->subtotal, 2);
    }

    public function getFormattedTaxAttribute()
    {
        return '₱' . number_format($this->tax, 2);
    }
}
