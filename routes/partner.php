<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InertiaController;
use App\Http\Controllers\OrderController;

/*
 * Partner Web Routes (Inertia Rendering Only)
 * Note: All business logic should be handled via API calls
 */
Route::prefix('partner')->group(function () {
    // Dashboard - Inertia render only
    Route::get('/dashboard', [InertiaController::class, 'partnerDashboard'])
        ->name('partner.dashboard');
    
    // Order management - Inertia render only
    Route::get('/orders', [OrderController::class, 'partnerIndex'])
        ->name('partner.orders');
    Route::get('/orders/{order}', [OrderController::class, 'partnerShow'])
        ->name('partner.order.show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])
        ->name('partner.order.update-status');
    
    // Settings - Inertia render only
    Route::get('/settings', [InertiaController::class, 'partnerSettings'])
        ->name('partner.settings');
});
