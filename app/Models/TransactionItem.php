<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    protected $table = 'transaction_items';

    protected $fillable = [
        'transaction_id',
        'product_id',
        'size_id',
        'product_name',
        'product_brand',
        'product_size',
        'product_color',
        'product_category',
        'sku',
        'unit_price',
        'cost_price',
        'quantity',
        'size',
        'subtotal'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
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
        return $this->belongsTo(ProductSize::class, 'size_id');
    }
}