<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InertiaController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\MenuController;

/*
 * Customer Web Routes (Inertia Rendering Only)
 * Note: All business logic should be handled via API calls
 */
Route::prefix('customer')->group(function () {
    // Dashboard - Inertia render only
    Route::get('/dashboard', [InertiaController::class, 'customerDashboard'])
        ->name('customer.dashboard');
    
    // Menu browsing - Inertia render only
    Route::get('/menu/{restaurant}', [MenuController::class, 'show'])
        ->name('customer.menu');
    
    // Checkout flow - Inertia render only
    Route::get('/checkout', [OrderController::class, 'checkout'])
        ->name('customer.checkout');
    Route::post('/checkout', [OrderController::class, 'placeOrder'])
        ->name('customer.place-order');
    
    // Order management - Inertia render only
    Route::get('/orders', [OrderController::class, 'index'])
        ->name('customer.orders');
    Route::get('/orders/{order}', [OrderController::class, 'show'])
        ->name('customer.order.show');
    
    // Profile - Inertia render only
    Route::get('/profile', [InertiaController::class, 'customerProfile'])
        ->name('customer.profile');
});
