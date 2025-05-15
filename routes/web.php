<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use Inertia\Inertia;

// Include route files for different user types
require __DIR__.'/customer.php';
require __DIR__.'/courier.php';
require __DIR__.'/partner.php';

// Root route
Route::get('/', [PublicController::class, 'index'])->name('index');
// Auth routes
Route::get('/login', [PublicController::class, 'login'])->name('login');
Route::get('/register', [PublicController::class, 'register'])->name('register');
