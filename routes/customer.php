<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\MenuController as CustomerMenuController;
use App\Http\Controllers\Customer\CheckoutController as CustomerCheckoutController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\Customer\SupportController as CustomerSupportController;

Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/menu/{partnerId}', [CustomerMenuController::class, 'show'])->name('menu.show');
    Route::get('/checkout', [CustomerCheckoutController::class, 'index'])->name('checkout');
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders');
    Route::get('/orders/{orderId}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::get('/profile', [CustomerProfileController::class, 'index'])->name('profile');
    Route::get('/support', [CustomerSupportController::class, 'index'])->name('support');
});
