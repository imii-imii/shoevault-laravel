<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\ReservationController;

// Authentication routes
Route::get('/', [ReservationController::class, 'index'])->name('reservation.home');
Route::get('/portal', [ReservationController::class, 'portal'])->name('reservation.portal');
Route::get('/form', [ReservationController::class, 'form'])->name('reservation.form');
Route::get('/size-converter', [ReservationController::class, 'sizeConverter'])->name('reservation.size-converter');

// AJAX routes for dynamic functionality
Route::get('/api/products/filter', [ReservationController::class, 'getFilteredProducts'])->name('api.products.filter');
Route::get('/api/products/{id}/details', [ReservationController::class, 'getProductDetails'])->name('api.products.details');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get'); // Alternative logout route

// POS routes (for cashiers and admin)
Route::middleware(['auth', 'role:cashier,admin'])->prefix('pos')->name('pos.')->group(function () {
    Route::get('/dashboard', [PosController::class, 'dashboard'])->name('dashboard');
    Route::get('/reservations', [PosController::class, 'reservations'])->name('reservations');
    Route::get('/settings', [PosController::class, 'settings'])->name('settings');
    Route::get('/products', [PosController::class, 'getProducts'])->name('products');
    Route::post('/process-sale', [PosController::class, 'processSale'])->name('process-sale');
});

// Inventory routes (for managers and admin)
Route::middleware(['auth', 'role:manager,admin'])->prefix('inventory')->name('inventory.')->group(function () {
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
    Route::get('/reservation-reports', [InventoryController::class, 'reservationReports'])->name('reservation-reports');
    Route::get('/settings', [InventoryController::class, 'settings'])->name('settings');
    Route::get('/data', [InventoryController::class, 'getInventoryData'])->name('data');
    Route::post('/products', [InventoryController::class, 'addProduct'])->name('products.store');
    Route::put('/products/{id}', [InventoryController::class, 'updateProduct'])->name('products.update');
    Route::delete('/products/{id}', [InventoryController::class, 'deleteProduct'])->name('products.destroy');
    Route::get('/sizes/{category}', [InventoryController::class, 'getSizesByCategory'])->name('sizes.by-category');
    
    // Reservation management routes
    Route::post('/reservations/{id}/status', [InventoryController::class, 'updateReservationStatus'])->name('reservations.update-status');
});

// Owner routes (for owners and admin)
Route::middleware(['auth', 'role:owner,admin'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerController::class, 'dashboard'])->name('dashboard');
    Route::get('/sales-history', [OwnerController::class, 'salesHistory'])->name('sales-history');
    Route::get('/reservation-logs', [OwnerController::class, 'reservationLogs'])->name('reservation-logs');
    Route::get('/supply-logs', [OwnerController::class, 'supplyLogs'])->name('supply-logs');
    Route::get('/inventory-overview', [OwnerController::class, 'inventoryOverview'])->name('inventory-overview');
    Route::get('/settings', [OwnerController::class, 'settings'])->name('settings');
});

// Analytics routes (for owners and admin) - Future implementation  
Route::middleware(['auth', 'role:owner,admin'])->prefix('analytics')->name('analytics.')->group(function () {
    Route::get('/dashboard', function () {
        return view('analytics.dashboard');
    })->name('dashboard');
});
