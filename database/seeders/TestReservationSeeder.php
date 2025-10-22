<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Reservation;
use Carbon\Carbon;

class TestReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test reservations with JSON items structure
        $reservations = [
            [
                'reservation_id' => 'RSV-TEST-001',
                'customer_name' => 'John Doe',
                'customer_email' => 'john@example.com',
                'customer_phone' => '+1234567890',
                'total_amount' => 13000.00,
                'pickup_date' => Carbon::now()->addDays(3),
                'pickup_time' => '14:00',
                'status' => 'pending',
                'notes' => 'Test reservation for sale integration',
                'reserved_at' => Carbon::now(),
                'items' => [
                    [
                        'product_id' => 1,
                        'size_id' => 1,
                        'product_name' => 'Nike Air Max 270',
                        'product_brand' => 'Nike',
                        'product_size' => '9',
                        'product_color' => 'Black',
                        'product_category' => 'men',
                        'unit_price' => 6500.00,
                        'quantity' => 2,
                        'subtotal' => 13000.00,
                        'cost_price' => 4500.00
                    ]
                ]
            ],
            [
                'reservation_id' => 'RSV-TEST-002',
                'customer_name' => 'Jane Smith',
                'customer_email' => 'jane@example.com',
                'customer_phone' => '+1234567891',
                'total_amount' => 12499.00,
                'pickup_date' => Carbon::now()->addDays(5),
                'pickup_time' => '16:30',
                'status' => 'pending',
                'notes' => 'Multiple items test reservation',
                'reserved_at' => Carbon::now(),
                'items' => [
                    [
                        'product_id' => 2,
                        'size_id' => 4,
                        'product_name' => 'Adidas Ultraboost 22',
                        'product_brand' => 'Adidas',
                        'product_size' => '8',
                        'product_color' => 'White',
                        'product_category' => 'women',
                        'unit_price' => 8500.00,
                        'quantity' => 1,
                        'subtotal' => 8500.00,
                        'cost_price' => 6000.00
                    ],
                    [
                        'product_id' => 3,
                        'size_id' => 7,
                        'product_name' => 'Converse Chuck Taylor',
                        'product_brand' => 'Converse',
                        'product_size' => '7',
                        'product_color' => 'Black',
                        'product_category' => 'accessories',
                        'unit_price' => 3999.00,
                        'quantity' => 1,
                        'subtotal' => 3999.00,
                        'cost_price' => 2500.00
                    ]
                ]
            ]
        ];

        foreach ($reservations as $reservationData) {
            Reservation::create($reservationData);
        }

        echo "Created " . count($reservations) . " test reservations\n";
    }
}