<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductSize;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Nike Air Max 270',
                'brand' => 'Nike',
                'category' => 'men',
                'price' => 7500.00,
                'min_stock' => 5,
                'sku' => 'NIKE-AM270-BLK',
                'description' => 'Nike Air Max 270 men\'s running shoes with Air Max unit.',
                'is_active' => true,
                'sizes' => [
                    ['size' => '8', 'stock' => 5, 'price_adjustment' => 0],
                    ['size' => '9', 'stock' => 4, 'price_adjustment' => 0],
                    ['size' => '10', 'stock' => 3, 'price_adjustment' => 0],
                    ['size' => '11', 'stock' => 3, 'price_adjustment' => 0],
                ]
            ],
            [
                'name' => 'Adidas Ultraboost 22',
                'brand' => 'Adidas',
                'category' => 'women',
                'price' => 8200.00,
                'min_stock' => 5,
                'sku' => 'ADIDAS-UB22-WHT',
                'description' => 'Adidas Ultraboost 22 women\'s running shoes with Boost technology.',
                'is_active' => true,
                'sizes' => [
                    ['size' => '6', 'stock' => 2, 'price_adjustment' => 0],
                    ['size' => '7', 'stock' => 1, 'price_adjustment' => 0],
                    ['size' => '8', 'stock' => 0, 'price_adjustment' => 0], // Out of stock
                ]
            ],
            [
                'name' => 'Converse Chuck Taylor All Star',
                'brand' => 'Converse',
                'category' => 'men',
                'price' => 3500.00,
                'min_stock' => 8,
                'sku' => 'CONV-CT-BLK',
                'description' => 'Classic Converse Chuck Taylor All Star high-top sneakers.',
                'is_active' => true,
                'sizes' => [
                    ['size' => '7', 'stock' => 6, 'price_adjustment' => 0],
                    ['size' => '8', 'stock' => 5, 'price_adjustment' => 0],
                    ['size' => '9', 'stock' => 4, 'price_adjustment' => 0],
                    ['size' => '10', 'stock' => 5, 'price_adjustment' => 0],
                ]
            ],
            [
                'name' => 'Vans Old Skool',
                'brand' => 'Vans',
                'category' => 'women',
                'price' => 4200.00,
                'min_stock' => 6,
                'sku' => 'VANS-OS-BLK',
                'description' => 'Vans Old Skool classic skate shoes with signature side stripe.',
                'is_active' => true,
                'sizes' => [
                    ['size' => '5.5', 'stock' => 3, 'price_adjustment' => 0],
                    ['size' => '6', 'stock' => 4, 'price_adjustment' => 0],
                    ['size' => '7', 'stock' => 3, 'price_adjustment' => 0],
                    ['size' => '8', 'stock' => 2, 'price_adjustment' => 0],
                ]
            ],
            [
                'name' => 'Leather Belt Premium',
                'brand' => 'ShoeVault',
                'category' => 'accessories',
                'price' => 1200.00,
                'min_stock' => 10,
                'sku' => 'SV-BELT-BRN',
                'description' => 'Premium genuine leather belt with metal buckle.',
                'is_active' => true,
                'sizes' => [
                    ['size' => 'S', 'stock' => 8, 'price_adjustment' => 0],
                    ['size' => 'M', 'stock' => 10, 'price_adjustment' => 0],
                    ['size' => 'L', 'stock' => 7, 'price_adjustment' => 0],
                ]
            ]
        ];

        foreach ($products as $productData) {
            // Extract sizes data
            $sizes = $productData['sizes'];
            unset($productData['sizes']);
            
            // Generate unique product ID
            $productData['product_id'] = Product::generateUniqueProductId($productData['category']);
            
            // Create the product
            $product = Product::create($productData);
            
            // Create the sizes
            foreach ($sizes as $sizeData) {
                ProductSize::create([
                    'product_id' => $product->id,
                    'size' => $sizeData['size'],
                    'stock' => $sizeData['stock'],
                    'price_adjustment' => $sizeData['price_adjustment'],
                    'is_available' => true
                ]);
            }
        }
    }
}
