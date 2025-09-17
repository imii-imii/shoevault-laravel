<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'user_id',
        'subtotal',
        'tax',
        'total',
        'amount_paid',
        'change_amount',
        'payment_method',
        'items',
        'notes'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'items' => 'array',
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
        return 'TXN-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
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
