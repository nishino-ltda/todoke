<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
use App\Http\Controllers\API\AddonController;

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

        // Email verification routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationEmail'])
                ->middleware('throttle:6,1');
            
            Route::get('/verify-email', [AuthController::class, 'verifyEmail'])
                ->middleware('signed')
                ->name('verification.verify');

            // Password routes
            Route::post('/confirm-password', [AuthController::class, 'confirmPassword']);
            Route::put('/password', [AuthController::class, 'updatePassword']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
        
        // Convert API token to session
        Route::post('/token-to-session', function (Request $request) {
            $user = $request->user();
            
            if ($user) {
                // Start session and login using web guard
                auth('web')->login($user);
                $request->session()->regenerate();
                return response()->json(['success' => true]);
            }
            
            return response()->json(['success' => false], 401);
        })->middleware(['auth:sanctum', 'web']);
    });

    // Rotas públicas de produtos
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{product}/addons', [ProductController::class, 'getAddons']);
    });

    // Rotas públicas de restaurantes
    Route::prefix('restaurants')->group(function () {
        Route::get('/{slug}', [\App\Http\Controllers\API\RestaurantController::class, 'show']);
    });

    // Rotas protegidas por autenticação
    Route::middleware('auth:sanctum')->group(function () {
        // Rotas de usuário
        Route::prefix('users')->group(function () {
            Route::get('/me', [UserController::class, 'profile']);
            Route::patch('/me', [UserController::class, 'update']);
            Route::patch('/locale', [\App\Http\Controllers\UserController::class, 'updateLocale']);
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
            Route::post('/{product}/addons', [ProductController::class, 'addAddons']);
        });
        
        // Rotas de addons
        Route::prefix('addons')->group(function () {
            Route::get('/', [AddonController::class, 'index']);
            Route::post('/', [AddonController::class, 'store']);
            Route::put('/{addon}', [AddonController::class, 'update']);
            Route::delete('/{addon}', [AddonController::class, 'destroy']);
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
        Route::prefix('admin')->middleware(['auth:sanctum', 'can:admin'])->group(function () {
            Route::get('/users', [UserController::class, 'index']);
            Route::patch('/users/{id}/status', [UserController::class, 'updateStatus']);
            Route::get('/stats', [UserController::class, 'stats']);
            Route::patch('/nodes/{node}/approve', [NodeController::class, 'approve']);
            Route::post('/users/{id}/unlock', [UserController::class, 'unlock']);
        });

        // Rotas de parceiro
        Route::prefix('partner')->group(function () {
            Route::get('/metrics', [\App\Http\Controllers\API\PartnerController::class, 'metrics']);
        });

        // Test routes - only available in development environment
        if (app()->environment('local', 'testing')) {
            Route::prefix('test')->group(function () {
                Route::post('/setup-orders', [\App\Http\Controllers\API\TestController::class, 'setupOrders']);
            });
        }
        
    });
});
