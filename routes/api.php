<?php

use App\Http\Controllers\Auth\AddOwnerController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CronjobController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Guest\CarAvailabilityController;
use App\Http\Controllers\Guest\CarController;
use App\Http\Controllers\Guest\DriverAvailabilityController;
use App\Http\Controllers\Guest\FeatureController;
use App\Http\Controllers\Guest\DriverController;
use App\Http\Controllers\Guest\ServiceController;
use App\Http\Controllers\Master\MBankController;
use App\Http\Controllers\Master\MCarCategoryController;
use App\Http\Controllers\Master\MCarTypeController;
use App\Http\Controllers\Master\MDestinationController;
use App\Http\Controllers\Guest\MDestinationController as GuestMDestinationController;
use App\Http\Controllers\ManageDestinationController;
use App\Http\Controllers\ManageEventController;
use App\Http\Controllers\Master\MDriverController;
use App\Http\Controllers\Master\MServiceController;
use App\Http\Controllers\Master\MFeatureController;
use App\Http\Controllers\OwnerCarController;
use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('guest')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('banks', MBankController::class)->middleware('auth:sanctum');
    Route::apiResource('car-categories', MCarCategoryController::class)->middleware('auth:sanctum');
    Route::apiResource('car-types', MCarTypeController::class)->middleware('auth:sanctum');
    Route::apiResource('destinations', MDestinationController::class)->middleware('auth:sanctum');
    Route::apiResource('driver', MDriverController::class);
    Route::apiResource('service', MServiceController::class);
    Route::apiResource('features', MFeatureController::class);
    Route::apiResource('dashboard', DashboardController::class)->only(['index']);
    Route::apiResource('owner-cars', OwnerCarController::class);
    Route::apiResource('events', ManageEventController::class);
    Route::apiResource('destination', ManageDestinationController::class);
    Route::get('get-car-types', [ManageDestinationController::class, 'carTypeList']);

    Route::post('add-owner', AddOwnerController::class);
    Route::post('logout', [AuthController::class, 'logout']);
});


Route::middleware('guest')->group(function () {
    Route::post('create-order', [PaymentController::class, 'store']);
    Route::post('callback', [PaymentController::class, 'callback']);
    Route::prefix('guest')->group(function () {
        Route::apiResource('services', ServiceController::class)->only(['index', 'show']);
        Route::apiResource('features', FeatureController::class)->only(['index', 'show']);
        Route::apiResource('drivers', DriverController::class)->only(['index', 'show']);
        Route::apiResource('cars', CarController::class)->only(['index', 'show']);
        Route::apiResource('car-availability', CarAvailabilityController::class)->only(['index']);
        Route::apiResource('driver-availability', DriverAvailabilityController::class)->only(['index']);
        Route::apiResource('destinations', GuestMDestinationController::class)->only(['index']);
    });


    Route::apiResource('events', EventController::class);
    Route::get('cronjob/events', [CronjobController::class, 'sendEventEmail']);
});
