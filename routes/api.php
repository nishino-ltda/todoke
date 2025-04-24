<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\DeliveryController;
use App\Http\Controllers\API\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Prefixo base: /api/v1
Route::prefix('v1')->group(function () {
    // 1. Autenticação e Usuário
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])
            ->middleware('throttle:10,1'); // 10 tentativas por minuto
        
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:10,1'); // 10 tentativas por minuto
    });

    // Rotas protegidas por autenticação
    Route::middleware('auth:sanctum')->group(function () {
        // Rotas de usuário
        Route::prefix('users')->group(function () {
            Route::get('/me', [UserController::class, 'profile']);
            Route::patch('/me', [UserController::class, 'update']);
        });

        // Rotas de entregas
        Route::prefix('deliveries')->group(function () {
            Route::get('/', [DeliveryController::class, 'index']);
            Route::post('/', [DeliveryController::class, 'store']);
            Route::get('/{id}', [DeliveryController::class, 'show']);
            Route::patch('/{id}/accept', [DeliveryController::class, 'accept']);
            Route::patch('/{id}/status', [DeliveryController::class, 'updateStatus']);
        });

        // Rotas de pedidos
        Route::prefix('orders')->group(function () {
            Route::post('/', [OrderController::class, 'store']);
        });

        // Rotas de admin
        Route::prefix('admin')->middleware('admin')->group(function () {
            Route::get('/users', [UserController::class, 'index']);
            Route::patch('/users/{id}/status', [UserController::class, 'updateStatus']);
            Route::get('/stats', [UserController::class, 'stats']);
        });
        
    });
});
