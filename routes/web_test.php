<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\ReservationController;

// Public reservation routes (main website)
Route::get('/', [ReservationController::class, 'index'])->name('reservation.home');
Route::get('/portal', [ReservationController::class, 'portal'])->name('reservation.portal');
Route::get('/form', [ReservationController::class, 'form'])->name('reservation.form');

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
