<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\DeliveryController;
use App\Http\Controllers\API\DeliveryManagementController;
use App\Http\Controllers\API\DeliveryStatusController;
use App\Http\Controllers\API\DeliveryMessagingController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\RegionController;
use App\Http\Controllers\API\NodeController;
use App\Http\Controllers\API\VotingController;

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

// Rota de documentação
Route::get('/documentation', function () {
    return response()->json([
        'message' => 'TODOKE API Documentation',
        'version' => '1.0.0',
        'endpoints' => [
            '/api/v1/auth/register',
            '/api/v1/auth/login',
            '/api/v1/deliveries',
            '/api/v1/users/me'
        ]
    ]);
});

// Prefixo base: /api/v1
Route::prefix('v1')->group(function () {
    // 1. Autenticação e Usuário
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])
            ->middleware('throttle:10,1'); // 10 tentativas por minuto
        
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:10,1'); // 10 tentativas por minuto
    });

    // Rotas públicas de produtos
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
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
            Route::get('/', [DeliveryManagementController::class, 'index']);
            Route::post('/', [DeliveryManagementController::class, 'store']);
            Route::get('/{id}', [DeliveryManagementController::class, 'show']);
            Route::patch('/{id}/accept', [DeliveryStatusController::class, 'accept']);
            Route::patch('/{id}/status', [DeliveryStatusController::class, 'updateStatus']);
            Route::post('/{id}/messages', [DeliveryMessagingController::class, 'storeMessage']);
            Route::get('/{id}/messages', [DeliveryMessagingController::class, 'indexMessages']);
        });

        // Rotas de regiões
        Route::post('/regions', [RegionController::class, 'store']);

        // Rotas de nodes
        Route::post('/nodes', [NodeController::class, 'store']);

        // Rotas de produtos
        Route::prefix('products')->group(function () {
            Route::post('/', [ProductController::class, 'store']);
            Route::put('/{product}', [ProductController::class, 'update']);
        });

        // Rotas de pedidos
        Route::prefix('orders')->group(function () {
            Route::post('/', [OrderController::class, 'store']);
        });

        // Rotas de notificações
        Route::get('/notifications', [NotificationController::class, 'index']);

        // Rotas de votação
        Route::prefix('voting')->group(function () {
            Route::post('/vote', [VotingController::class, 'vote']);
            Route::get('/active', [VotingController::class, 'getActiveRounds']);
            Route::get('/results/{roundId}', [VotingController::class, 'getResults']);
        });

        // Rotas de admin
        Route::prefix('admin')->middleware('admin')->group(function () {
            Route::get('/users', [UserController::class, 'index']);
            Route::patch('/users/{id}/status', [UserController::class, 'updateStatus']);
            Route::get('/stats', [UserController::class, 'stats']);
        });

        // Rotas de parceiro
        Route::prefix('partner')->group(function () {
            Route::get('/metrics', [\App\Http\Controllers\API\PartnerController::class, 'metrics']);
        });
        
    });
});
