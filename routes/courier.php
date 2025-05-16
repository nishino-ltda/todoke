<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Courier\DashboardController as CourierDashboardController;
use App\Http\Controllers\Courier\DeliveryController as CourierDeliveryController;
use App\Http\Controllers\Courier\SettingsController as CourierSettingsController;

/*
 * Courier Web Routes (Inertia Rendering Only)
 * Note: All business logic should be handled via API calls
 */
Route::middleware(['auth', 'role:courier'])->prefix('courier')->name('courier.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [CourierDashboardController::class, 'index'])->name('dashboard');

    // Delivery management
    Route::get('/deliveries', [CourierDeliveryController::class, 'index'])->name('deliveries');
    Route::get('/deliveries/{deliveryId}', [CourierDeliveryController::class, 'show'])->name('deliveries.show');
    // Note: The updateStatus route is an API route, not a web route for Inertia rendering.
    // It should be defined in routes/api.php if needed as an API endpoint.

    // Hybrid delivery coordination
    Route::get('/hybrid-deliveries', [CourierDeliveryController::class, 'hybridIndex'])->name('hybrid-deliveries');
    Route::get('/hybrid-deliveries/{deliveryId}', [CourierDeliveryController::class, 'hybridShow'])->name('hybrid-deliveries.show');

    // Settings
    Route::get('/settings', [CourierSettingsController::class, 'index'])->name('settings');
});
