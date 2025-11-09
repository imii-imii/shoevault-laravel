<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SupplyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'brand',
        'size',
        'quantity',
        'received_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'received_at' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }


}
