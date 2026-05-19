<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ApiDocumentationController;
use App\Http\Controllers\Api\MarketController;
use App\Http\Controllers\Api\OpenApiController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\TradeController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/docs', ApiDocumentationController::class);
Route::get('/openapi.json', OpenApiController::class);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/market/btc', [MarketController::class, 'btc']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/wallet', WalletController::class);

    Route::post('/trade/buy', [TradeController::class, 'buy']);
    Route::post('/trade/sell', [TradeController::class, 'sell']);

    Route::get('/transactions', TransactionController::class);
});
