<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// ユーザー登録
Route::post('/register', [AuthController::class, 'register']);

// ログイン
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group( function () {
    // 商品登録
    Route::post('/items', [ItemController::class, 'create']);
    // 商品修正
    Route::put('/items/{id}', [ItemController::class, 'update']);
    // 商品削除
    Route::delete('/items/{id}',[ItemController::class, 'destroy']);

    // 購入
    Route::post('/orders', [OrderController::class, 'createOrder']);
    // 売買履歴
    Route::get('/orders', [OrderController::class, 'index']);
});

// 商品表示
Route::get('/items', [ItemController::class, 'index']);
// 商品表示（単品）
Route::get('/items/{id}', [ItemController::class, 'show']);
