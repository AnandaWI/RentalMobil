<?php

use App\Http\Controllers\Auth\AddOwnerController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Master\MBankController;
use App\Http\Controllers\Master\MCarCategoryController;
use App\Http\Controllers\Master\MCarTypeController;
use App\Http\Controllers\Master\MDestinationController;
use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('guest')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::apiResource('banks', MBankController::class)->middleware('auth:sanctum');
    Route::apiResource('car-categories', MCarCategoryController::class)->middleware('auth:sanctum');
    Route::apiResource('car-types', MCarTypeController::class)->middleware('auth:sanctum');
    Route::apiResource('destinations', MDestinationController::class)->middleware('auth:sanctum');

    Route::post('add-owner', AddOwnerController::class);
    Route::post('logout', [AuthController::class, 'logout']);
});


Route::middleware('guest')->group(function () {
    Route::post('create-order', [PaymentController::class, 'store']);
    Route::post('test', function (Request $request) {
        return $request->all();
    });
});
