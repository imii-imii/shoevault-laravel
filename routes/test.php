<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Notification;
use App\Models\Product;
use App\Models\ProductSize;
use App\Services\NotificationService;

Route::get('/test-notification-cleanup', function () {
    $notificationService = app(NotificationService::class);
    
    $output = [];
    $output[] = "=== Testing Notification Cleanup ===";
    
    // Check current notifications
    $totalBefore = Notification::count();
    $lowStockBefore = Notification::where('type', 'low_stock')->count();
    $reservationBefore = Notification::where('type', 'new_reservation')->count();
    
    $output[] = "Before cleanup:";
    $output[] = "- Total notifications: {$totalBefore}";
    $output[] = "- Low stock notifications: {$lowStockBefore}";
    $output[] = "- New reservation notifications: {$reservationBefore}";
    
    // Test low stock cleanup
    if ($lowStockBefore > 0) {
        $output[] = "";
        $output[] = "=== Testing Low Stock Cleanup ===";
        $lowStockNotification = Notification::where('type', 'low_stock')->first();
        if ($lowStockNotification) {
            $data = json_decode($lowStockNotification->data, true);
            $productId = $data['product_id'] ?? null;
            
            if ($productId) {
                $output[] = "Testing cleanup for product ID: {$productId}";
                $notificationService->cleanupLowStockNotifications($productId);
                
                $lowStockAfter = Notification::where('type', 'low_stock')
                    ->whereJsonContains('data->product_id', $productId)
                    ->count();
                
                $output[] = "Low stock notifications for product {$productId} after cleanup: {$lowStockAfter}";
            }
        }
    }
    
    // Test reservation cleanup
    if ($reservationBefore > 0) {
        $output[] = "";
        $output[] = "=== Testing Reservation Cleanup ===";
        $reservationNotification = Notification::where('type', 'new_reservation')->first();
        if ($reservationNotification) {
            $data = json_decode($reservationNotification->data, true);
            $reservationId = $data['reservation_id'] ?? null;
            
            if ($reservationId) {
                $output[] = "Testing cleanup for reservation ID: {$reservationId}";
                $notificationService->cleanupNewReservationNotifications($reservationId);
                
                $reservationAfter = Notification::where('type', 'new_reservation')
                    ->whereJsonContains('data->reservation_id', $reservationId)
                    ->count();
                
                $output[] = "New reservation notifications for reservation {$reservationId} after cleanup: {$reservationAfter}";
            }
        }
    }
    
    // Final count
    $totalAfter = Notification::count();
    $output[] = "";
    $output[] = "=== Final Results ===";
    $output[] = "Total notifications after cleanup: {$totalAfter}";
    $output[] = "Notifications cleaned up: " . ($totalBefore - $totalAfter);
    
    return response()->json([
        'success' => true,
        'output' => $output
    ]);
});

// Test route for generating notifications
Route::get('/test-generate-notifications', function () {
    // Generate some test notifications
    $product = Product::first();
    if ($product) {
        $productSize = $product->productSizes()->first();
        if ($productSize) {
            Notification::create([
                'type' => 'low_stock',
                'message' => "Low stock alert for {$product->name}",
                'data' => json_encode([
                    'product_id' => $product->id,
                    'product_size_id' => $productSize->id,
                    'current_stock' => 2,
                    'threshold' => 5
                ]),
                'target_roles' => json_encode(['Owner', 'Manager'])
            ]);
        }
    }
    
    return response()->json([
        'success' => true,
        'message' => 'Test notification generated'
    ]);
});