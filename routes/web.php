<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InertiaController;

// Include route files for different user types
require __DIR__.'/customer.php';
require __DIR__.'/courier.php';
require __DIR__.'/partner.php';

Route::get('/{any}', InertiaController::class)->where('any', '.*');
