<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Models\Product;
use App\Models\ProductSize;
use App\Services\NotificationService;

class TestNotificationCleanup extends Command
{
    protected $signature = 'test:notification-cleanup';
    protected $description = 'Test notification cleanup functionality';

    public function handle()
    {
        $notificationService = app(NotificationService::class);
        
        $this->info('=== Testing Notification Cleanup ===');
        
        // Check current notifications
        $totalBefore = Notification::count();
        $lowStockBefore = Notification::where('type', 'low_stock')->count();
        $reservationBefore = Notification::where('type', 'new_reservation')->count();
        
        $this->info("Before cleanup:");
        $this->info("- Total notifications: {$totalBefore}");
        $this->info("- Low stock notifications: {$lowStockBefore}");
        $this->info("- New reservation notifications: {$reservationBefore}");
        
        // Test low stock cleanup
        if ($lowStockBefore > 0) {
            $this->info("\n=== Testing Low Stock Cleanup ===");
            $lowStockNotification = Notification::where('type', 'low_stock')->first();
            if ($lowStockNotification) {
                $data = json_decode($lowStockNotification->data, true);
                $productId = $data['product_id'] ?? null;
                
                if ($productId) {
                    $this->info("Testing cleanup for product ID: {$productId}");
                    $notificationService->cleanupLowStockNotifications($productId);
                    
                    $lowStockAfter = Notification::where('type', 'low_stock')
                        ->where('data->product_id', $productId)
                        ->count();
                    
                    $this->info("Low stock notifications for product {$productId} after cleanup: {$lowStockAfter}");
                }
            }
        }
        
        // Test reservation cleanup
        if ($reservationBefore > 0) {
            $this->info("\n=== Testing Reservation Cleanup ===");
            $reservationNotification = Notification::where('type', 'new_reservation')->first();
            if ($reservationNotification) {
                $data = json_decode($reservationNotification->data, true);
                $reservationId = $data['reservation_id'] ?? null;
                
                if ($reservationId) {
                    $this->info("Testing cleanup for reservation ID: {$reservationId}");
                    $notificationService->cleanupNewReservationNotifications($reservationId);
                    
                    $reservationAfter = Notification::where('type', 'new_reservation')
                        ->where('data->reservation_id', $reservationId)
                        ->count();
                    
                    $this->info("New reservation notifications for reservation {$reservationId} after cleanup: {$reservationAfter}");
                }
            }
        }
        
        // Final count
        $totalAfter = Notification::count();
        $this->info("\n=== Final Results ===");
        $this->info("Total notifications after cleanup: {$totalAfter}");
        $this->info("Notifications cleaned up: " . ($totalBefore - $totalAfter));
        
        return 0;
    }
}