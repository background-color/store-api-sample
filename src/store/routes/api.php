<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ユーザー登録
Route::post('/register', [AuthController::class, 'register']);

// ログイン
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group( function () {
    Route::post('/item', [ItemController::class, 'createItem']);
    Route::put('/items/{id}', [ItemController::class, 'updateItem']);
    Route::delete('/items/{id}',[ItemController::class, 'deleteItem']);
});

Route::get('/items', [ItemController::class, 'getAllItems']);
Route::get('/items/{id}', [ItemController::class, 'getItem']);
