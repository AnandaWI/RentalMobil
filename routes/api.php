<?php

use App\Http\Controllers\Master\MBankController;
use App\Http\Controllers\Master\MCarCategoryController;
use App\Http\Controllers\Master\MCarTypeController;
use App\Http\Controllers\Master\MDestinationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Bank routes
Route::apiResource('banks', MBankController::class)->middleware('auth:sanctum');

// Car Category routes
Route::apiResource('car-categories', MCarCategoryController::class)->middleware('auth:sanctum');

// Car Type routes
Route::apiResource('car-types', MCarTypeController::class)->middleware('auth:sanctum');

// Destination routes
Route::apiResource('destinations', MDestinationController::class)->middleware('auth:sanctum');
