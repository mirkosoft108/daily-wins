<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\WinController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/wins', [WinController::class, 'index']);
Route::post('/wins', [WinController::class, 'store']);
Route::get('/wins/{win}', [WinController::class, 'show']);
Route::put('/wins/{win}', [WinController::class, 'update']);
Route::delete('/wins/{win}', [WinController::class, 'destroy']);
