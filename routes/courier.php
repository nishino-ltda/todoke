<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InertiaController;
use App\Http\Controllers\DeliveryController;

/*
 * Courier Web Routes (Inertia Rendering Only)
 * Note: All business logic should be handled via API calls
 */
Route::prefix('courier')->group(function () {
    // Dashboard - Inertia render only
    Route::get('/dashboard', [InertiaController::class, 'courierDashboard'])
        ->name('courier.dashboard');
    
    // Delivery management - Inertia render only
    Route::get('/deliveries', [DeliveryController::class, 'index'])
        ->name('courier.deliveries');
    Route::get('/deliveries/{delivery}', [DeliveryController::class, 'show'])
        ->name('courier.delivery.show');
    Route::patch('/deliveries/{delivery}/status', [DeliveryController::class, 'updateStatus'])
        ->name('courier.delivery.update-status');
    
    // Hybrid delivery coordination - Inertia render only
    Route::get('/hybrid-deliveries', [DeliveryController::class, 'hybridIndex'])
        ->name('courier.hybrid-deliveries');
    Route::get('/hybrid-deliveries/{delivery}', [DeliveryController::class, 'hybridShow'])
        ->name('courier.hybrid-delivery.show');
    
    // Settings - Inertia render only
    Route::get('/settings', [InertiaController::class, 'courierSettings'])
        ->name('courier.settings');
});
