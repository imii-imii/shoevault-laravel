<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestReservationProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test products for POS
        $products = [
            [
                'product_id' => 'NIKE001',
                'sku' => 'NIKE-AM270-BLK',
                'name' => 'Nike Air Max 270',
                'brand' => 'Nike',
                'category' => 'men',
                'color' => 'Black',
                'price' => 6500.00,
                'is_active' => true,
                'sizes' => [
                    ['size' => '8', 'stock' => 5, 'price_adjustment' => 0],
                    ['size' => '9', 'stock' => 10, 'price_adjustment' => 0],
                    ['size' => '10', 'stock' => 3, 'price_adjustment' => 0],
                ]
            ],
            [
                'product_id' => 'ADID001',
                'sku' => 'ADID-UB22-WHT',
                'name' => 'Adidas Ultraboost 22',
                'brand' => 'Adidas',
                'category' => 'women',
                'color' => 'White',
                'price' => 8500.00,
                'is_active' => true,
                'sizes' => [
                    ['size' => '7', 'stock' => 8, 'price_adjustment' => 0],
                    ['size' => '8', 'stock' => 12, 'price_adjustment' => 0],
                    ['size' => '9', 'stock' => 6, 'price_adjustment' => 0],
                ]
            ],
            [
                'product_id' => 'CONV001',
                'sku' => 'CONV-CT-BLK',
                'name' => 'Converse Chuck Taylor',
                'brand' => 'Converse',
                'category' => 'accessories',
                'color' => 'Black',
                'price' => 3999.00,
                'is_active' => true,
                'sizes' => [
                    ['size' => '6', 'stock' => 15, 'price_adjustment' => 0],
                    ['size' => '7', 'stock' => 20, 'price_adjustment' => 0],
                    ['size' => '8', 'stock' => 18, 'price_adjustment' => 0],
                    ['size' => '9', 'stock' => 10, 'price_adjustment' => 0],
                ]
            ]
        ];

        foreach ($products as $productData) {
            $sizes = $productData['sizes'];
            unset($productData['sizes']);
            
            $product = \App\Models\ReservationProduct::create($productData);
            
            foreach ($sizes as $sizeData) {
                $product->sizes()->create([
                    'size' => $sizeData['size'],
                    'stock' => $sizeData['stock'],
                    'price_adjustment' => $sizeData['price_adjustment'],
                    'is_available' => true
                ]);
            }
        }
        
        echo "Created " . count($products) . " test products for POS\n";
    }
}
