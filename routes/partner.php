<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Partner\DashboardController as PartnerDashboardController;
use App\Http\Controllers\Partner\OrderController as PartnerOrderController;
use App\Http\Controllers\Partner\ProductController as PartnerProductController;
use App\Http\Controllers\Partner\AddonController as PartnerAddonController;
use App\Http\Controllers\Partner\RegionController as PartnerRegionController;
use App\Http\Controllers\Partner\NodeController as PartnerNodeController;
use App\Http\Controllers\Partner\SettingsController as PartnerSettingsController;

/*
 * Partner Web Routes (Inertia Rendering Only)
 * Note: All business logic should be handled via API calls
 */
Route::middleware(['auth', 'role:partner'])->prefix('partner')->name('partner.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [PartnerDashboardController::class, 'index'])->name('dashboard');

    // Order management
    Route::get('/orders', [PartnerOrderController::class, 'index'])->name('orders');
    Route::get('/orders/{orderId}', [PartnerOrderController::class, 'show'])->name('orders.show');
    // Note: The updateStatus route is an API route, not a web route for Inertia rendering.
    // It should be defined in routes/api.php if needed as an API endpoint.

    // Product management
    Route::get('/products', [PartnerProductController::class, 'index'])->name('products');
    Route::get('/products/create', [PartnerProductController::class, 'create'])->name('products.create');
    Route::get('/products/{productId}/edit', [PartnerProductController::class, 'edit'])->name('products.edit');
    Route::get('/products/{productId}', [PartnerProductController::class, 'show'])->name('products.show');

    // Addon management
    Route::get('/addons', [PartnerAddonController::class, 'index'])->name('addons');
    Route::get('/addons/create', [PartnerAddonController::class, 'create'])->name('addons.create');
    Route::get('/addons/{addonId}/edit', [PartnerAddonController::class, 'edit'])->name('addons.edit');
    Route::get('/addons/{addonId}', [PartnerAddonController::class, 'show'])->name('addons.show');

    // Region management (for Logistics Partners)
    Route::get('/regions', [PartnerRegionController::class, 'index'])->name('regions');
    Route::get('/regions/create', [PartnerRegionController::class, 'create'])->name('regions.create');
    Route::get('/regions/{regionId}/edit', [PartnerRegionController::class, 'edit'])->name('regions.edit');
    Route::get('/regions/{regionId}', [PartnerRegionController::class, 'show'])->name('regions.show');

    // Node management (for Logistics Partners)
    Route::get('/nodes', [PartnerNodeController::class, 'index'])->name('nodes');
    Route::get('/nodes/create', [PartnerNodeController::class, 'create'])->name('nodes.create');
    Route::get('/nodes/{nodeId}/edit', [PartnerNodeController::class, 'edit'])->name('nodes.edit');
    Route::get('/nodes/{nodeId}', [PartnerNodeController::class, 'show'])->name('nodes.show');

    // Settings
    Route::get('/settings', [PartnerSettingsController::class, 'index'])->name('settings');
});
