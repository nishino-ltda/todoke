<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public Controllers
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MenuController;

// Common Controllers
use App\Http\Controllers\SupportController;

// Customer Controllers
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\MenuController as CustomerMenuController;
use App\Http\Controllers\Customer\CheckoutController as CustomerCheckoutController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
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
use App\Http\Controllers\Partner\VariationController as PartnerVariationController;

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
use App\Http\Controllers\Admin\SystemMonitorController as AdminSystemMonitorController;

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

// Partner Menu Routes (Public)
Route::get('/menu/{partner}', [MenuController::class, 'show'])->name('menu.show');
Route::get('/{partner}', [MenuController::class, 'show'])->name('partner.menu');

// Authenticated Routes
Route::middleware(['auth', 'verified'])->group(function () {
        // Support Routes
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/', [SupportController::class, 'index'])->name('dashboard');
        Route::get('/tickets', [SupportController::class, 'tickets'])->name('tickets.index');
        Route::get('/tickets/create', [SupportController::class, 'create'])->name('tickets.create');
        Route::get('/tickets/{id}', [SupportController::class, 'show'])->name('tickets.show');
        Route::get('/tickets/{id}/reply', [SupportController::class, 'reply'])->name('tickets.reply');
        Route::get('/faq', [SupportController::class, 'faq'])->name('faq');
    });

    // Customer Routes
    Route::prefix('customer')->name('customer.')->group(function () {
        Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/menu', [CustomerMenuController::class, 'index'])->name('menu');
        Route::get('/checkout', [CustomerCheckoutController::class, 'index'])->name('checkout');
        
        // Customer Orders
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [CustomerOrderController::class, 'index'])->name('index');
            Route::get('/{id}', [CustomerOrderController::class, 'show'])->name('show');
        });

        Route::get('/profile', [CustomerProfileController::class, 'index'])->name('profile');
        Route::get('/terms', [CustomerTermsController::class, 'index'])->name('terms');
        Route::get('/privacy', [CustomerPrivacyController::class, 'index'])->name('privacy');
    });

    // Partner Routes
    Route::prefix('partner')->name('partner.')->group(function () {
        Route::get('/dashboard', [PartnerDashboardController::class, 'index'])->name('dashboard');
        
        // Partner Orders
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [PartnerOrderController::class, 'index'])->name('index');
            Route::get('/create', [PartnerOrderController::class, 'create'])->name('create');
            Route::get('/{id}', [PartnerOrderController::class, 'show'])->name('show');
            Route::get('/batch-create', [PartnerOrderController::class, 'batchCreate'])->name('batch.create');
        });

        // Partner Products
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [PartnerProductController::class, 'index'])->name('index');
            Route::get('/create', [PartnerProductController::class, 'create'])->name('create');
            Route::get('/{id}/edit', [PartnerProductController::class, 'edit'])->name('edit');
            Route::get('/{id}', [PartnerProductController::class, 'show'])->name('show');
            
            // Product Variations
            Route::prefix('{product}/variations')->name('variations.')->group(function () {
                Route::get('/', [PartnerVariationController::class, 'index'])->name('index');
                Route::get('/create', [PartnerVariationController::class, 'create'])->name('create');
                Route::get('/{variation}/edit', [PartnerVariationController::class, 'edit'])->name('edit');
            });
        });

        // Partner Addons
        Route::prefix('addons')->name('addons.')->group(function () {
            Route::get('/', [PartnerAddonController::class, 'index'])->name('index');
            Route::get('/create', [PartnerAddonController::class, 'create'])->name('create');
            Route::get('/{id}/edit', [PartnerAddonController::class, 'edit'])->name('edit');
            Route::get('/{id}', [PartnerAddonController::class, 'show'])->name('show');
        });

        // Partner Regions
        Route::prefix('regions')->name('regions.')->group(function () {
            Route::get('/', [PartnerRegionController::class, 'index'])->name('index');
            Route::get('/create', [PartnerRegionController::class, 'create'])->name('create');
            Route::get('/{id}/edit', [PartnerRegionController::class, 'edit'])->name('edit');
            Route::get('/{id}', [PartnerRegionController::class, 'show'])->name('show');
        });

        // Partner Nodes
        Route::prefix('nodes')->name('nodes.')->group(function () {
            Route::get('/', [PartnerNodeController::class, 'index'])->name('index');
            Route::get('/create', [PartnerNodeController::class, 'create'])->name('create');
            Route::get('/{id}/edit', [PartnerNodeController::class, 'edit'])->name('edit');
            Route::get('/{id}', [PartnerNodeController::class, 'show'])->name('show');
        });

        Route::get('/settings', [PartnerSettingsController::class, 'index'])->name('settings.index');
    });

    // Courier Routes
    Route::prefix('courier')->name('courier.')->group(function () {
        Route::get('/dashboard', [CourierDashboardController::class, 'index'])->name('dashboard');
        
        // Courier Deliveries
        Route::prefix('deliveries')->name('deliveries.')->group(function () {
            Route::get('/', [CourierDeliveryController::class, 'index'])->name('index');
            Route::get('/{id}', [CourierDeliveryController::class, 'show'])->name('show');
        });

        // Courier Hybrid Deliveries
        Route::prefix('hybrid-deliveries')->name('hybrid-deliveries.')->group(function () {
            Route::get('/', [CourierHybridDeliveryController::class, 'index'])->name('index');
            Route::get('/create', [CourierHybridDeliveryController::class, 'create'])->name('create');
            Route::get('/{id}', [CourierHybridDeliveryController::class, 'show'])->name('show');
            Route::get('/{id}/handoff', [CourierHybridDeliveryController::class, 'handoff'])->name('handoff');
        });

        Route::get('/settings', [CourierSettingsController::class, 'index'])->name('settings.index');
    });

    // Admin Routes
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        // Admin Users
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('index');
            Route::get('/{id}', [AdminUserController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [AdminUserController::class, 'edit'])->name('edit');
        });

        // Admin Nodes
        Route::prefix('nodes')->name('nodes.')->group(function () {
            Route::get('/', [AdminNodeController::class, 'index'])->name('index');
            Route::get('/{id}', [AdminNodeController::class, 'show'])->name('show');
            Route::get('/{id}/approve', [AdminNodeController::class, 'approve'])->name('approve');
        });

        // Admin Regions
        Route::prefix('regions')->name('regions.')->group(function () {
            Route::get('/', [AdminRegionController::class, 'index'])->name('index');
            Route::get('/{id}', [AdminRegionController::class, 'show'])->name('show');
        });

        // Admin Deliveries
        Route::prefix('deliveries')->name('deliveries.')->group(function () {
            Route::get('/', [AdminDeliveryController::class, 'index'])->name('index');
            Route::get('/monitor', [AdminDeliveryController::class, 'monitor'])->name('monitor');
            Route::get('/{id}', [AdminDeliveryController::class, 'show'])->name('show');
        });

        // System Monitoring
        Route::prefix('system')->name('system.')->group(function () {
            Route::get('/monitor', [AdminSystemMonitorController::class, 'index'])->name('monitor');
            Route::get('/logs', [AdminSystemMonitorController::class, 'logs'])->name('logs');
        });

        // Admin Stats
        Route::prefix('stats')->name('stats.')->group(function () {
            Route::get('/', [AdminDashboardController::class, 'stats'])->name('index');
        });

        // Platform Configuration
        Route::prefix('platform')->name('platform.')->group(function () {
            Route::get('/settings', [AdminSettingsController::class, 'platform'])->name('settings');
            Route::get('/settings/edit', [AdminSettingsController::class, 'editPlatform'])->name('settings.edit');
        });

        Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    });
});

require __DIR__.'/auth.php';
