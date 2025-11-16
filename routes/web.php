<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\OwnerUsersController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ForecastController;
use App\Http\Controllers\SitemapController;

// SEO Routes
Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

// SEO-friendly product and category routes - Automatically uses customer session
Route::get('/product/{product}', [ReservationController::class, 'showProduct'])->name('product.show');
Route::get('/category/{category}', [ReservationController::class, 'showCategory'])->name('category.show');
Route::get('/brand/{brand}', [ReservationController::class, 'showBrand'])->name('brand.show');

// Authentication routes

// Debug route for inventory types
Route::get('/debug/inventory', function() {
    return [
        'total_products' => \App\Models\Product::count(),
        'pos_products' => \App\Models\Product::posInventory()->count(),
        'products_with_types' => \App\Models\Product::select('id', 'name', 'inventory_type')->get()
    ];
});

// Debug route for customer authentication
Route::get('/debug/customers', function() {
    $customers = \App\Models\Customer::with('user')->get();
    return $customers->map(function($customer) {
        return [
            'customer_id' => $customer->customer_id,
            'email' => $customer->email,
            'user_id' => $customer->user_id,
            'has_user' => !is_null($customer->user),
            'username' => $customer->user->username ?? 'N/A',
            'user_password_length' => $customer->user ? strlen($customer->user->password) : 0,
            'email_verified' => $customer->hasVerifiedEmail(),
        ];
    });
});

// Debug route for session behavior
Route::get('/debug/session', function(Request $request) {
    return [
        'customer_authenticated' => Auth::guard('customer')->check(),
        'customer_id' => Auth::guard('customer')->id(),
        'staff_authenticated' => Auth::check(),
        'staff_user' => Auth::user() ? ['id' => Auth::user()->user_id, 'role' => Auth::user()->role] : null,
        'customer_remember_me_flag' => $request->session()->get('customer_remember_me', 'not_set'),
        'session_config' => [
            'driver' => config('session.driver'),
            'lifetime' => config('session.lifetime'),
            'expire_on_close' => config('session.expire_on_close'),
        ],
        'session_id' => $request->session()->getId(),
    ];
});

// Customer reservation routes - Automatically uses separate session from staff
Route::get('/', [ReservationController::class, 'index'])->name('reservation.home');
Route::get('/portal', [ReservationController::class, 'portal'])->name('reservation.portal');
Route::get('/form', [ReservationController::class, 'form'])->name('reservation.form');
Route::get('/size-converter', [ReservationController::class, 'sizeConverter'])->name('reservation.size-converter');

// AJAX routes for dynamic functionality - Automatically uses customer session
Route::get('/api/products/filter', [ReservationController::class, 'getFilteredProducts'])->name('api.products.filter');

// Test routes for notification cleanup
Route::get('/test-notification-cleanup', function () {
    $notificationService = app(\App\Services\NotificationService::class);
    
    $output = [];
    $output[] = "=== Testing Notification Cleanup ===";
    
    // Check current notifications
    $totalBefore = \App\Models\Notification::count();
    $lowStockBefore = \App\Models\Notification::where('type', 'low_stock')->count();
    $reservationBefore = \App\Models\Notification::where('type', 'new_reservation')->count();
    
    $output[] = "Before cleanup:";
    $output[] = "- Total notifications: {$totalBefore}";
    $output[] = "- Low stock notifications: {$lowStockBefore}";
    $output[] = "- New reservation notifications: {$reservationBefore}";
    
    // Test low stock cleanup
    if ($lowStockBefore > 0) {
        $output[] = "";
        $output[] = "=== Testing Low Stock Cleanup ===";
        $lowStockNotification = \App\Models\Notification::where('type', 'low_stock')->first();
        if ($lowStockNotification) {
            $data = json_decode($lowStockNotification->data, true);
            $productId = $data['product_id'] ?? null;
            
            if ($productId) {
                $output[] = "Testing cleanup for product ID: {$productId}";
                $notificationService->cleanupLowStockNotifications($productId);
                
                $lowStockAfter = \App\Models\Notification::where('type', 'low_stock')
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
        $reservationNotification = \App\Models\Notification::where('type', 'new_reservation')->first();
        if ($reservationNotification) {
            $data = json_decode($reservationNotification->data, true);
            $reservationId = $data['reservation_id'] ?? null;
            
            if ($reservationId) {
                $output[] = "Testing cleanup for reservation ID: {$reservationId}";
                $notificationService->cleanupNewReservationNotifications($reservationId);
                
                $reservationAfter = \App\Models\Notification::where('type', 'new_reservation')
                    ->whereJsonContains('data->reservation_id', $reservationId)
                    ->count();
                
                $output[] = "New reservation notifications for reservation {$reservationId} after cleanup: {$reservationAfter}";
            }
        }
    }
    
    // Final count
    $totalAfter = \App\Models\Notification::count();
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
    $product = \App\Models\Product::first();
    if ($product) {
        $productSize = $product->productSizes()->first();
        if ($productSize) {
            \App\Models\Notification::create([
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
// Customer API routes - Automatically uses separate customer session
Route::get('/api/products/{id}/details', [ReservationController::class, 'getProductDetails'])->name('api.products.details');

// Check for pending reservations before allowing cart checkout
Route::post('/api/check-pending-reservations', [ReservationController::class, 'checkPendingReservations'])->middleware('customer.auth')->name('api.check.pending.reservations');

// Reservation submission endpoint - protected by customer auth
Route::post('/api/reservations', [ReservationController::class, 'store'])->middleware('customer.auth')->name('api.reservations.store');

// Send reservation confirmation email
Route::post('/api/send-reservation-email', [ReservationController::class, 'sendConfirmationEmail'])->name('api.reservations.send-email');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get'); // Alternative logout route

// Force password change routes (for users with default passwords)
Route::middleware(['auth'])->group(function () {
    Route::get('/force-password-change', [AuthController::class, 'forcePasswordChange'])->name('force-password-change');
    Route::post('/force-password-change', [AuthController::class, 'updateForcedPassword'])->name('force-password-change.update');
});

// Notification routes (available to all authenticated users)
Route::middleware(['auth'])->prefix('api/notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
    Route::get('/stats', [NotificationController::class, 'stats'])->name('stats');
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    
    // Owner/Manager-only notification routes
    Route::middleware(['role:owner,manager'])->group(function () {
        Route::post('/', [NotificationController::class, 'create'])->name('create');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });
    
    // Owner-only notification routes
    Route::middleware(['role:owner'])->group(function () {
        Route::post('/trigger-checks', [NotificationController::class, 'triggerChecks'])->name('trigger-checks');
        Route::post('/cleanup', [NotificationController::class, 'cleanup'])->name('cleanup');
    });
});

// Test notification endpoint (temporary)
Route::middleware(['auth'])->get('/test-notifications', function() {
    $user = Auth::user();
    $controller = new App\Http\Controllers\NotificationController(new App\Services\NotificationService());
    return $controller->index(request());
});

// Customer authentication routes - Automatically uses separate session from staff
Route::prefix('customer')->name('customer.')->group(function () {
    Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [CustomerAuthController::class, 'login'])->name('login.post');
    Route::post('/register', [CustomerAuthController::class, 'register'])->name('register');
    Route::post('/verify-code', [CustomerAuthController::class, 'verifyCode'])->name('verify-code');
    Route::post('/resend-verification-code', [CustomerAuthController::class, 'resendVerificationCode'])->name('resend-verification-code');
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->middleware('customer.auth')->name('logout');
    Route::get('/user', [CustomerAuthController::class, 'user'])->name('user'); // Remove middleware to allow status checking
    Route::post('/update-password', [CustomerAuthController::class, 'updatePassword'])->name('update-password'); // For existing customers
    Route::post('/send-password-reset-code', [CustomerAuthController::class, 'sendPasswordResetCode'])->name('send-password-reset-code');
});

// POS routes (for cashiers only)
Route::middleware(['auth', 'role:cashier', 'force.password.change', 'operating.hours'])->prefix('pos')->name('pos.')->group(function () {
    Route::get('/dashboard', [PosController::class, 'dashboard'])->name('dashboard');
    Route::get('/reservations', [PosController::class, 'reservations'])->name('reservations');
    Route::get('/settings', [PosController::class, 'settings'])->name('settings');
    Route::post('/profile/update', [PosController::class, 'updateProfile'])->name('profile.update');
    Route::delete('/profile/picture', [PosController::class, 'removeProfilePicture'])->name('profile.picture.remove');
    Route::post('/password/update', [PosController::class, 'updatePassword'])->name('password.update');
    Route::get('/products', [PosController::class, 'getProducts'])->name('products');
    Route::post('/process-sale', [PosController::class, 'processSale'])->name('process-sale');
    
    // Void transaction routes
    Route::get('/void/recent-transactions', [PosController::class, 'getRecentTransactions'])->name('void.recent-transactions');
    Route::post('/void/authenticate-manager', [PosController::class, 'authenticateManager'])->name('void.authenticate-manager');
    Route::delete('/void/transaction/{id}', [PosController::class, 'voidTransaction'])->name('void.transaction');

    // POS reservation management endpoints (reuse same database)
    Route::post('/reservations/{id}/status', [InventoryController::class, 'updateReservationStatus'])->name('reservations.update-status');
    // Reservation details API for POS (modal)
    Route::get('/api/reservations/{id}', [InventoryController::class, 'getReservationDetails'])->name('api.reservations.show');
});

// Inventory routes (for managers only)
Route::middleware(['auth', 'role:manager', 'force.password.change', 'operating.hours'])->prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/dashboard', [InventoryController::class, 'dashboard'])->name('dashboard');
    Route::get('/enhanced', function() {
        // Hard-coded products data for UI testing
        $products = collect([
            (object)[
                'id' => 1,
                'name' => 'Nike Air Max 270',
                'brand' => 'Nike',
                'category' => 'Men',
                'price' => 8499,
                'image' => null,
                'sizes' => collect([
                    (object)['size' => '7', 'stock' => 3],
                    (object)['size' => '8', 'stock' => 5],
                    (object)['size' => '9', 'stock' => 2],
                    (object)['size' => '10', 'stock' => 2]
                ])
            ],
            (object)[
                'id' => 2,
                'name' => 'Adidas Ultraboost 22',
                'brand' => 'Adidas',
                'category' => 'Men',
                'price' => 12999,
                'image' => null,
                'sizes' => collect([
                    (object)['size' => '8', 'stock' => 2],
                    (object)['size' => '9', 'stock' => 3],
                    (object)['size' => '10', 'stock' => 2],
                    (object)['size' => '11', 'stock' => 1]
                ])
            ],
            (object)[
                'id' => 3,
                'name' => 'Converse Chuck Taylor',
                'brand' => 'Converse',
                'category' => 'Accessories',
                'price' => 3999,
                'image' => null,
                'sizes' => collect([
                    (object)['size' => '6', 'stock' => 5],
                    (object)['size' => '7', 'stock' => 5],
                    (object)['size' => '8', 'stock' => 5],
                    (object)['size' => '9', 'stock' => 5]
                ])
            ]
        ]);
        
        // Hard-coded suppliers data
        $suppliers = collect([
            (object)[
                'id' => 1,
                'name' => 'Nike Philippines',
                'contact_person' => 'John Smith',
                'brand' => 'Nike',
                'total_stock' => 100,
                'country' => 'Philippines',
                'available_sizes' => '7-12',
                'email' => 'supplier@nike.com.ph',
                'phone' => '+63 2 123 4567',
                'status' => 'active'
            ],
            (object)[
                'id' => 2,
                'name' => 'Adidas Distributor',
                'contact_person' => 'Jane Doe',
                'brand' => 'Adidas',
                'total_stock' => 85,
                'country' => 'Philippines',
                'available_sizes' => '6-11',
                'email' => 'contact@adidas-ph.com',
                'phone' => '+63 2 987 6543',
                'status' => 'active'
            ]
        ]);
        
        // Hard-coded reservations data
        $reservations = collect([
            (object)[
                'id' => 1,
                'reservation_id' => 'REV-ABC123',
                'status' => 'pending',
                'created_at' => now()->subDays(1),
                'pickup_date' => now()->addDays(2),
                'customer' => (object)[
                    'name' => 'John Doe',
                    'email' => 'john.doe@email.com'
                ],
                'product' => (object)[
                    'name' => 'Nike Air Max 270'
                ]
            ],
            (object)[
                'id' => 2,
                'reservation_id' => 'REV-DEF456',
                'status' => 'confirmed',
                'created_at' => now()->subDays(2),
                'pickup_date' => now()->addDay(),
                'customer' => (object)[
                    'name' => 'Jane Smith',
                    'email' => 'jane.smith@email.com'
                ],
                'product' => (object)[
                    'name' => 'Adidas Ultraboost 22'
                ]
            ],
            (object)[
                'id' => 3,
                'reservation_id' => 'REV-GHI789',
                'status' => 'completed',
                'created_at' => now()->subDays(3),
                'pickup_date' => now()->subDay(),
                'customer' => (object)[
                    'name' => 'Mike Johnson',
                    'email' => 'mike.j@email.com'
                ],
                'product' => (object)[
                    'name' => 'Converse Chuck Taylor'
                ]
            ]
        ]);
        
        // Hard-coded reservation statistics
        $reservationStats = [
            'incomplete' => 24,
            'expiring_soon' => 8,
            'expiring_today' => 3
        ];
        
        return view('inventory.dashboard', compact('products', 'suppliers', 'reservations', 'reservationStats'));
    })->name('enhanced');
    Route::get('/suppliers', [InventoryController::class, 'suppliers'])->name('suppliers');
    Route::post('/suppliers', [InventoryController::class, 'storeSupplier'])->name('suppliers.store');
    // Supplier CRUD and Supply Logs
    Route::put('/suppliers/{supplier}', [InventoryController::class, 'updateSupplier'])->name('suppliers.update');
    Route::delete('/suppliers/{supplier}', [InventoryController::class, 'deleteSupplier'])->name('suppliers.destroy');
    Route::get('/suppliers/{supplier}/logs', [InventoryController::class, 'getSupplierLogs'])->name('suppliers.logs.index');
    Route::post('/suppliers/{supplier}/logs', [InventoryController::class, 'addSupplierLog'])->name('suppliers.logs.store');
    Route::get('/reservation-reports', [InventoryController::class, 'reservationReports'])->name('reservation-reports');
    Route::get('/settings', [InventoryController::class, 'settings'])->name('settings');
    Route::post('/profile/update', [InventoryController::class, 'updateProfile'])->name('profile.update');
    Route::delete('/profile/picture', [InventoryController::class, 'removeProfilePicture'])->name('profile.picture.remove');
    Route::post('/password/update', [InventoryController::class, 'updatePassword'])->name('password.update');
    Route::get('/data', [InventoryController::class, 'getInventoryData'])->name('data');
    Route::post('/products', [InventoryController::class, 'addProduct'])->name('products.store');
    Route::get('/products/{id}', [InventoryController::class, 'getProduct'])->name('products.show');
    Route::put('/products/{id}', [InventoryController::class, 'updateProduct'])->name('products.update');
    Route::delete('/products/{id}', [InventoryController::class, 'deleteProduct'])->name('products.destroy');
    Route::get('/sizes/{category}', [InventoryController::class, 'getSizesByCategory'])->name('sizes.by-category');
    Route::get('/predefined-sizes', [InventoryController::class, 'getPredefinedSizes'])->name('predefined-sizes');
    
    // Reservation management routes
    Route::post('/reservations/{id}/status', [InventoryController::class, 'updateReservationStatus'])->name('reservations.update-status');
    // Reservation details API for inventory (modal)
    Route::get('/api/reservations/{id}', [InventoryController::class, 'getReservationDetails'])->name('api.reservations.show');
});

// Owner routes (for owners only)
Route::middleware(['auth', 'role:owner', 'force.password.change'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerController::class, 'dashboard'])->name('dashboard');
    Route::get('/reports', [OwnerController::class, 'reports'])->name('reports');
    Route::get('/sales-history', [OwnerController::class, 'salesHistory'])->name('sales-history');
    Route::get('/reservation-logs', [OwnerController::class, 'reservationLogs'])->name('reservation-logs');
    Route::get('/supply-logs', [OwnerController::class, 'supplyLogs'])->name('supply-logs');
    Route::get('/inventory-overview', [OwnerController::class, 'inventoryOverview'])->name('inventory-overview');
    Route::get('/popular-products', [OwnerController::class, 'popularProducts'])->name('popular-products');
    Route::get('/settings', [OwnerController::class, 'settings'])->name('settings');
    Route::post('/profile/update', [OwnerController::class, 'updateProfile'])->name('profile.update');
    Route::delete('/profile/picture', [OwnerController::class, 'removeProfilePicture'])->name('profile.picture.remove');
    Route::post('/password/update', [OwnerController::class, 'updatePassword'])->name('password.update');
    Route::post('/notifications/clear', [OwnerController::class, 'clearNotifications'])->name('notifications.clear');
    
    // API routes for dashboard data
    Route::post('/api/dashboard-data', [OwnerController::class, 'getDashboardData'])->name('api.dashboard-data');
    Route::get('/api/stock-levels', [OwnerController::class, 'getStockLevels'])->name('api.stock-levels');
    Route::get('/api/transaction-date-range', [OwnerController::class, 'getTransactionDateRange'])->name('api.transaction-date-range');
    
    // Export API routes
    Route::get('/api/export-sales', [OwnerController::class, 'exportSales'])->name('api.export-sales');
    Route::get('/api/users-with-transactions', [OwnerController::class, 'getUsersWithTransactions'])->name('api.users-with-transactions');
    Route::get('/api/export-reservations', [OwnerController::class, 'exportReservations'])->name('api.export-reservations');
    Route::get('/api/export-supply', [OwnerController::class, 'exportSupplyLogs'])->name('api.export-supply');
    Route::get('/api/supply-filters', [OwnerController::class, 'getSupplyFilters'])->name('api.supply-filters');
    // Forecast data (sales revenue and demand)
    Route::get('/api/forecast', [ForecastController::class, 'index'])->name('api.forecast');
    
    // ML-based forecast endpoints
    Route::get('/api/ml-forecast', [\App\Http\Controllers\Owner\MLForecastController::class, 'forecast'])->name('api.ml-forecast');
    Route::post('/api/ml-forecast/train', [\App\Http\Controllers\Owner\MLForecastController::class, 'trainModel'])->name('api.ml-forecast.train');
    Route::get('/api/ml-forecast/status', [\App\Http\Controllers\Owner\MLForecastController::class, 'modelStatus'])->name('api.ml-forecast.status');
    Route::get('/api/ml-forecast/export-data', [\App\Http\Controllers\Owner\MLForecastController::class, 'exportData'])->name('api.ml-forecast.export-data');


    // User management APIs
    Route::get('/users', [OwnerUsersController::class, 'index'])->name('users.index');
    Route::post('/users', [OwnerUsersController::class, 'store'])->name('users.store');
    Route::post('/users/toggle', [OwnerUsersController::class, 'toggle'])->name('users.toggle');
    Route::post('/users/reset-password', [OwnerUsersController::class, 'resetPassword'])->name('users.reset-password');
    
    // Customer management APIs
    Route::get('/customers', [OwnerUsersController::class, 'customersIndex'])->name('customers.index');
    Route::post('/customers/toggle', [OwnerUsersController::class, 'customersToggle'])->name('customers.toggle');
    
    // Operating hours management APIs
    Route::get('/operating-hours', [OwnerController::class, 'getOperatingHoursSettings'])->name('operating-hours.get');
    Route::post('/operating-hours', [OwnerController::class, 'updateOperatingHoursSetting'])->name('operating-hours.update');
    Route::post('/emergency-access/enable', [OwnerController::class, 'enableEmergencyAccess'])->name('emergency-access.enable');
    Route::post('/emergency-access/disable', [OwnerController::class, 'disableEmergencyAccess'])->name('emergency-access.disable');
});

// Analytics routes (for owners only) - Future implementation  
Route::middleware(['auth', 'role:owner'])->prefix('analytics')->name('analytics.')->group(function () {
    Route::get('/dashboard', function () {
        return view('analytics.dashboard');
    })->name('dashboard');
});
