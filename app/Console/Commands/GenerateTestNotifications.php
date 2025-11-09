<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;
use App\Models\Notification;

class GenerateTestNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:generate-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate test notifications for development';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService)
    {
        $this->info('Generating test notifications...');

        // Get real products from the database for low stock notifications (POS inventory only)
        $products = \App\Models\Product::with('sizes')->posInventory()->take(3)->get();
        $lowStockNotifications = [];
        
        foreach ($products as $product) {
            $firstSize = $product->sizes->first();
            if ($firstSize) {
                $lowStockNotifications[] = [
                    'product_id' => $product->product_id,
                    'product_size_id' => $firstSize->product_size_id,
                    'name' => $product->name,
                    'size' => $firstSize->size,
                    'stock' => 3, // Simulate low stock
                    'category' => $product->category
                ];
            }
        }
        
        // Fallback to mock data if no products exist
        if (empty($lowStockNotifications)) {
            $lowStockNotifications = [
                [
                    'product_id' => 'SV-TEST-001',
                    'product_size_id' => 'TEST-SIZE-001',
                    'name' => 'Test Product 1',
                    'size' => '9',
                    'stock' => 3,
                    'category' => 'men'
                ],
                [
                    'product_id' => 'SV-TEST-002',
                    'product_size_id' => 'TEST-SIZE-002',
                    'name' => 'Test Product 2',
                    'size' => '8',
                    'stock' => 2,
                    'category' => 'men'
                ]
            ];
        }

        foreach ($lowStockNotifications as $data) {
            Notification::createLowStockNotification($data);
            $this->info("Created low stock notification for {$data['name']} size {$data['size']}");
        }

        // Create some sample reservation notifications
        $reservationNotifications = [
            [
                'reservation_id' => 'REV-001',
                'customer_name' => 'John Doe',
                'total_amount' => 8499,
                'status' => 'pending',
                'created_at' => now()->toISOString()
            ],
            [
                'reservation_id' => 'REV-002',
                'customer_name' => 'Jane Smith',
                'total_amount' => 12999,
                'status' => 'pending',
                'created_at' => now()->subMinutes(30)->toISOString()
            ]
        ];

        foreach ($reservationNotifications as $data) {
            Notification::createNewReservationNotification($data);
            $this->info("Created new reservation notification for {$data['reservation_id']}");
        }

        // Create a custom notification
        $notificationService->createCustomNotification([
            'title' => 'System Maintenance',
            'message' => 'The system will undergo maintenance tonight from 2 AM to 4 AM.',
            'target_role' => 'all',
            'type' => 'system',
            'priority' => 'high',
            'icon' => 'fas fa-tools'
        ]);
        $this->info('Created system maintenance notification');

        $this->info('Test notifications generated successfully!');
        
        // Show summary
        $total = Notification::count();
        $unreadOwner = Notification::forRole('owner')->unread()->count();
        $unreadManager = Notification::forRole('manager')->unread()->count();
        $unreadCashier = Notification::forRole('cashier')->unread()->count();
        
        $this->table(
            ['Role', 'Unread Notifications'],
            [
                ['Owner', $unreadOwner],
                ['Manager', $unreadManager],
                ['Cashier', $unreadCashier],
                ['Total Notifications', $total]
            ]
        );
    }
}
