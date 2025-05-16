<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public Controllers
use App\Http\Controllers\HomeController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SupportController;

// Customer Controllers
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\MenuController as CustomerMenuController;
use App\Http\Controllers\Customer\CheckoutController as CustomerCheckoutController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\Customer\SupportController as CustomerSupportController;
use App\Http\Controllers\Customer\TermsController as CustomerTermsController;
use App\Http\Controllers\Customer\PrivacyController as CustomerPrivacyController;

// Partner Controllers
use App\Http\Controllers\Partner\DashboardController as PartnerDashboardController;
use App\Http\Controllers\Partner\OrderController as PartnerOrderController;
use App\Http\Controllers\Partner\ProductController as PartnerProductController;
use App\Http\Controllers\Partner\AddonController as PartnerAddonController;
use App\Http\Controllers\Partner\RegionController as PartnerRegionController;
use App\Http\Controllers\Partner\NodeController as PartnerNodeController;
use App\Http\Controllers\Partner\SettingsController as PartnerSettingsController;

// Courier Controllers
use App\Http\Controllers\Courier\DashboardController as CourierDashboardController;
use App\Http\Controllers\Courier\DeliveryController as CourierDeliveryController;
use App\Http\Controllers\Courier\HybridDeliveryController as CourierHybridDeliveryController;
use App\Http\Controllers\Courier\SettingsController as CourierSettingsController;

// Admin Controllers
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\NodeController as AdminNodeController;
use App\Http\Controllers\Admin\RegionController as AdminRegionController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\DeliveryController as AdminDeliveryController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/welcome', [WelcomeController::class, 'index'])->name('welcome');

// Authenticated Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Customer Routes
    Route::prefix('customer')->name('customer.')->group(function () {
        Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/menu', [CustomerMenuController::class, 'index'])->name('menu');
        Route::get('/checkout', [CustomerCheckoutController::class, 'index'])->name('checkout');
        Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [CustomerOrderController::class, 'show'])->name('orders.show');
        Route::get('/profile', [CustomerProfileController::class, 'index'])->name('profile');
        Route::get('/support', [CustomerSupportController::class, 'index'])->name('support');
        Route::get('/terms', [CustomerTermsController::class, 'index'])->name('terms');
        Route::get('/privacy', [CustomerPrivacyController::class, 'index'])->name('privacy');
    });

    // Partner Routes
    Route::prefix('partner')->name('partner.')->group(function () {
        Route::get('/dashboard', [PartnerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/orders', [PartnerOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [PartnerOrderController::class, 'show'])->name('orders.show');
        Route::get('/products', [PartnerProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [PartnerProductController::class, 'create'])->name('products.create');
        Route::get('/products/{id}/edit', [PartnerProductController::class, 'edit'])->name('products.edit');
        Route::get('/products/{id}', [PartnerProductController::class, 'show'])->name('products.show');
        Route::get('/addons', [PartnerAddonController::class, 'index'])->name('addons.index');
        Route::get('/addons/create', [PartnerAddonController::class, 'create'])->name('addons.create');
        Route::get('/addons/{id}/edit', [PartnerAddonController::class, 'edit'])->name('addons.edit');
        Route::get('/addons/{id}', [PartnerAddonController::class, 'show'])->name('addons.show');
        Route::get('/regions', [PartnerRegionController::class, 'index'])->name('regions.index');
        Route::get('/regions/create', [PartnerRegionController::class, 'create'])->name('regions.create');
        Route::get('/regions/{id}/edit', [PartnerRegionController::class, 'edit'])->name('regions.edit');
        Route::get('/regions/{id}', [PartnerRegionController::class, 'show'])->name('regions.show');
        Route::get('/nodes', [PartnerNodeController::class, 'index'])->name('nodes.index');
        Route::get('/nodes/create', [PartnerNodeController::class, 'create'])->name('nodes.create');
        Route::get('/nodes/{id}/edit', [PartnerNodeController::class, 'edit'])->name('nodes.edit');
        Route::get('/nodes/{id}', [PartnerNodeController::class, 'show'])->name('nodes.show');
        Route::get('/settings', [PartnerSettingsController::class, 'index'])->name('settings.index');
    });

    // Courier Routes
    Route::prefix('courier')->name('courier.')->group(function () {
        Route::get('/dashboard', [CourierDashboardController::class, 'index'])->name('dashboard');
        Route::get('/deliveries', [CourierDeliveryController::class, 'index'])->name('deliveries.index');
        Route::get('/deliveries/{id}', [CourierDeliveryController::class, 'show'])->name('deliveries.show');
        Route::get('/hybrid-deliveries', [CourierHybridDeliveryController::class, 'index'])->name('hybrid-deliveries.index');
        Route::get('/hybrid-deliveries/{id}', [CourierHybridDeliveryController::class, 'show'])->name('hybrid-deliveries.show');
        Route::get('/settings', [CourierSettingsController::class, 'index'])->name('settings.index');
    });

    // Admin Routes
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/nodes', [AdminNodeController::class, 'index'])->name('nodes.index');
        Route::get('/regions', [AdminRegionController::class, 'index'])->name('regions.index');
        Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
        Route::get('/deliveries', [AdminDeliveryController::class, 'index'])->name('deliveries.index');
    });

    // Support Routes (common for all authenticated users)
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/', [SupportController::class, 'index'])->name('dashboard'); // Renamed to dashboard for consistency
        Route::get('/tickets', [SupportController::class, 'tickets'])->name('tickets.index');
        Route::get('/tickets/{id}', [SupportController::class, 'showTicket'])->name('tickets.show');
    });
});


require __DIR__.'/auth.php';
// require __DIR__.'/customer.php'; // Commented out as routes are now in web.php
// require __DIR__.'/partner.php'; // Commented out as routes are now in web.php
// require __DIR__.'/courier.php'; // Commented out as routes are now in web.php
