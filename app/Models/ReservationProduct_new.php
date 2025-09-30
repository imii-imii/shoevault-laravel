<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ReservationProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'brand',
        'category',
        'color',
        'price',
        'sku',
        'image_url',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Get the sizes for this reservation product.
     */
    public function sizes(): HasMany
    {
        return $this->hasMany(ReservationProductSize::class);
    }

    /**
     * Get available sizes for this reservation product.
     */
    public function availableSizes(): HasMany
    {
        return $this->sizes()->where('is_available', true);
    }

    /**
     * Generate unique reservation product ID
     * Format: RSV-{CATEGORY_PREFIX}-{RANDOM_STRING}
     * Example: RSV-MEN-A1B2C3, RSV-WOM-X9Y8Z7, RSV-ACC-M5N6P7
     */
    public static function generateProductId($category)
    {
        $categoryPrefixes = [
            'men' => 'MEN',
            'women' => 'WOM',
            'accessories' => 'ACC'
        ];

        $prefix = $categoryPrefixes[$category] ?? 'GEN'; // General if category not found
        
        do {
            $randomString = strtoupper(Str::random(6));
            $productId = "RSV-{$prefix}-{$randomString}";
        } while (self::where('product_id', $productId)->exists());

        return $productId;
    }

    /**
     * Generate unique SKU for reservation products
     * Format: RSKU-{CATEGORY_PREFIX}-{TIMESTAMP}-{RANDOM}
     * Example: RSKU-MEN-1696233456-A1B2, RSKU-WOM-1696233457-X9Y8
     */
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
            $randomString = strtoupper(Str::random(4));
            $sku = "RSKU-{$prefix}-{$timestamp}-{$randomString}";
        } while (self::where('sku', $sku)->exists());
        
        return $sku;
    }

    /**
     * Generate image filename based on product ID
     * Format: {PRODUCT_ID}_{TIMESTAMP}.{EXTENSION}
     * Example: RSV-MEN-A1B2C3_1694952000.jpg
     */
    public function generateImageFilename($originalFilename)
    {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        return $this->product_id . '_' . time() . '.' . $extension;
    }

    /**
     * Get total stock across all sizes
     */
    public function getTotalStock()
    {
        return $this->sizes()->sum('stock');
    }

    /**
     * Check if product has any stock in any size
     */
    public function hasStock()
    {
        return $this->sizes()->where('stock', '>', 0)->exists();
    }

    /**
     * Check if product is low stock (any size below threshold of 5)
     */
    public function isLowStock()
    {
        return $this->sizes()->whereRaw('stock <= ? AND stock > 0', [5])->exists();
    }

    /**
     * Check if product is out of stock (all sizes have 0 stock)
     */
    public function isOutOfStock()
    {
        return !$this->hasStock();
    }

    /**
     * Get stock status across all sizes
     */
    public function getStockStatusAttribute()
    {
        if ($this->isOutOfStock()) {
            return 'out-of-stock';
        } elseif ($this->isLowStock()) {
            return 'low-stock';
        } else {
            return 'in-stock';
        }
    }

    /**
     * Get sizes with their stock information
     */
    public function getSizesWithStockAttribute()
    {
        return $this->sizes->map(function ($size) {
            return [
                'size' => $size->size,
                'stock' => $size->stock,
                'is_available' => $size->is_available,
                'effective_price' => $size->getEffectivePrice()
            ];
        });
    }

    /**
     * Get available size options based on category
     */
    public static function getSizeOptionsByCategory($category)
    {
        $sizeOptions = [
            'men' => ['7', '7.5', '8', '8.5', '9', '9.5', '10', '10.5', '11', '11.5', '12'],
            'women' => ['5', '5.5', '6', '6.5', '7', '7.5', '8', '8.5', '9', '9.5', '10'],
            'accessories' => ['XS', 'S', 'M', 'L', 'XL', 'One Size']
        ];

        return $sizeOptions[$category] ?? [];
    }

    /**
     * Format price with currency
     */
    public function getFormattedPriceAttribute()
    {
        return '₱' . number_format($this->price, 2);
    }

    /**
     * Scope for active products
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for products with stock
     */
    public function scopeInStock($query)
    {
        return $query->whereHas('sizes', function($q) {
            $q->where('stock', '>', 0)->where('is_available', true);
        });
    }

    /**
     * Scope for low stock products
     */
    public function scopeLowStock($query)
    {
        return $query->whereHas('sizes', function($q) {
            $q->whereRaw('stock <= ? AND stock > 0', [5]);
        });
    }

    /**
     * Scope for category filter
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
