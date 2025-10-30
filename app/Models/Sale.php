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
        'sale_type',
        'reservation_id',
        'cashier_id',
        'subtotal',
        'discount_amount',
        'total_amount',
        'amount_paid',
        'change_given',
        'sale_date'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_given' => 'decimal:2',
        'sale_date' => 'datetime'
    ];

    /**
     * Get the cashier who made this sale
     */
    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    /**
     * Get the reservation associated with this sale
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id', 'reservation_id');
    }

    /**
     * Get the sale items for this sale
     */
    public function items()
    {
        return $this->hasMany(SaleItem::class);
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
     * Computed Properties (calculated from relationships)
     */
    public function getTotalItemsAttribute()
    {
        return $this->items()->count();
    }

    public function getTotalQuantityAttribute()
    {
        return $this->items()->sum('quantity');
    }

    /**
     * Calculate total profit from items (for analytics)
     */
    public function getTotalProfitAttribute()
    {
        return $this->items()->get()->sum(function ($item) {
            return ($item->unit_price - ($item->cost_price ?? 0)) * $item->quantity;
        });
    }

    /**
     * Status is always 'completed' for sales (refunds would be separate records)
     */
    public function getStatusAttribute()
    {
        return 'completed';
    }

    /**
     * Payment method is always cash for this POS system
     */
    public function getPaymentMethodAttribute()
    {
        return 'cash';
    }

    /**
     * Format currency values
     */
    public function getFormattedTotalAttribute()
    {
        return '₱' . number_format($this->total_amount, 2);
    }

    public function getFormattedSubtotalAttribute()
    {
        return '₱' . number_format($this->subtotal, 2);
    }

    /**
     * Analytics methods
     */
    public static function getDailySalesData($date = null)
    {
        $date = $date ?? Carbon::today();
        
        return self::whereDate('sale_date', $date)
            ->withCount('items as total_items_count')
            ->withSum('items as total_quantity_sum', 'quantity')
            ->selectRaw('
                COUNT(*) as total_transactions,
                SUM(total_amount) as total_revenue,
                AVG(total_amount) as avg_transaction_value
            ')
            ->first();
    }

    public function getFormattedTaxAttribute()
    {
        return '₱' . number_format($this->tax, 2);
    }
}