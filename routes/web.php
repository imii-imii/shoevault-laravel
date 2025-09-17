<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\InventoryController;

// Authentication routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// POS routes (for cashiers and admin)
Route::middleware(['auth', 'role:cashier,admin'])->prefix('pos')->name('pos.')->group(function () {
    Route::get('/dashboard', [PosController::class, 'dashboard'])->name('dashboard');
    Route::get('/sales-history', [PosController::class, 'salesHistory'])->name('sales-history');
    Route::get('/reservations', [PosController::class, 'reservations'])->name('reservations');
    Route::get('/products', [PosController::class, 'getProducts'])->name('products');
    Route::post('/process-sale', [PosController::class, 'processSale'])->name('process-sale');
});

// Inventory routes (for managers and admin)
Route::middleware(['auth', 'role:manager,admin'])->prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/dashboard', [InventoryController::class, 'dashboard'])->name('dashboard');
    Route::get('/suppliers', [InventoryController::class, 'suppliers'])->name('suppliers');
    Route::get('/settings', [InventoryController::class, 'settings'])->name('settings');
    Route::get('/data', [InventoryController::class, 'getInventoryData'])->name('data');
    Route::post('/products', [InventoryController::class, 'addProduct'])->name('products.store');
    Route::put('/products/{id}', [InventoryController::class, 'updateProduct'])->name('products.update');
    Route::delete('/products/{id}', [InventoryController::class, 'deleteProduct'])->name('products.destroy');
    Route::get('/sizes/{category}', [InventoryController::class, 'getSizesByCategory'])->name('sizes.by-category');
});

// Analytics routes (for owners and admin) - Future implementation
Route::middleware(['auth', 'role:owner,admin'])->prefix('analytics')->name('analytics.')->group(function () {
    Route::get('/dashboard', function () {
        return view('analytics.dashboard');
    })->name('dashboard');
});
