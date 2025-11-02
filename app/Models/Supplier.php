<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        // Restrict to only the supported/available columns
        'name',
        'contact_person',
        'email',
        'country',
    ];

    // Remove casts for dropped/unused columns
    protected $casts = [];

    /**
     * Scope for active suppliers
     */
    // Note: "active" scope removed since is_active column is not used

    /**
     * Get products from this supplier
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function supplyLogs()
    {
        return $this->hasMany(SupplyLog::class);
    }
}