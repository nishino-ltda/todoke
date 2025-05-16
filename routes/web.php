<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\NodeController as AdminNodeController;
use App\Http\Controllers\Admin\RegionController as AdminRegionController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\DeliveryController as AdminDeliveryController;
use App\Http\Controllers\Support\DashboardController as SupportDashboardController;
use App\Http\Controllers\Support\TicketController as SupportTicketController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/dashboard', [HomeController::class, 'dashboard'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users');
    Route::get('/nodes', [AdminNodeController::class, 'index'])->name('nodes');
    Route::get('/regions', [AdminRegionController::class, 'index'])->name('regions');
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings');
    Route::get('/deliveries', [AdminDeliveryController::class, 'index'])->name('deliveries');
});

Route::middleware(['auth', 'role:support'])->prefix('support')->name('support.')->group(function () {
    Route::get('/dashboard', [SupportDashboardController::class, 'index'])->name('dashboard');
    Route::get('/tickets', [SupportTicketController::class, 'index'])->name('tickets');
    Route::get('/tickets/{ticketId}', [SupportTicketController::class, 'show'])->name('tickets.show');
});

require __DIR__.'/auth.php';
require __DIR__.'/customer.php';
require __DIR__.'/partner.php';
require __DIR__.'/courier.php';
