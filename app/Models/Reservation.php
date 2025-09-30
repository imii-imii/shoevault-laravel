<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = [
        'product_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'quantity',
        'size',
        'status',
        'reservation_date',
        'notes'
    ];

    protected $casts = [
        'reservation_date' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
