<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'brand',
        'category',
        'color',
        'price',
        'image_url',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function sizes()
    {
        return $this->hasMany(ReservationProductSize::class, 'reservation_product_id');
    }

    public function getTotalStock()
    {
        return $this->sizes()->sum('stock');
    }

    public function isLowStock($threshold = 5)
    {
        return $this->getTotalStock() < $threshold;
    }

    public function scopeInStock($query)
    {
        return $query->whereHas('sizes', function ($q) {
            $q->where('stock', '>', 0);
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query, $threshold = 5)
    {
        return $query->whereHas('sizes', function ($q) use ($threshold) {
            $q->havingRaw('SUM(stock) < ?', [$threshold]);
        });
    }

    public function scopeOutOfStock($query)
    {
        return $query->whereDoesntHave('sizes', function ($q) {
            $q->where('stock', '>', 0);
        });
    }

    /**
     * Generate unique product ID for reservation products
     * Format: RSV-{CATEGORY}-{RANDOM}
     * Example: RSV-MEN-A1B2C3, RSV-WOM-X9Y8Z7, RSV-ACC-M5N6P7
     */
    public static function generateUniqueProductId($category)
    {
        $categoryPrefixes = [
            'men' => 'MEN',
            'women' => 'WOM',
            'accessories' => 'ACC'
        ];
        
        $prefix = $categoryPrefixes[$category] ?? 'GEN';
        
        do {
            // Generate random alphanumeric string (6 characters)
            $randomString = strtoupper(\Illuminate\Support\Str::random(6));
            $productId = "RSV-{$prefix}-{$randomString}";
        } while (self::where('product_id', $productId)->exists());
        
        return $productId;
    }

    public static function generateUniqueSku($category)
    {
        $categoryPrefixes = [
            'men' => 'MEN',
            'women' => 'WOM',
            'accessories' => 'ACC'
        ];
        
        $prefix = $categoryPrefixes[$category] ?? 'GEN';
        $timestamp = time();
        
        do {
            // Generate random alphanumeric string (4 characters)
            $randomString = strtoupper(\Illuminate\Support\Str::random(4));
            $sku = "RSKU-{$prefix}-{$timestamp}-{$randomString}";
        } while (self::where('sku', $sku)->exists());

        return $sku;
    }

    public function generateImageFilename($originalName)
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return 'reservation_product_' . $this->id . '_' . time() . '.' . $extension;
    }

    public function getImageUrlAttribute($value)
    {
        if ($value && !str_starts_with($value, 'http')) {
            return asset($value);
        }
        return $value;
    }
}