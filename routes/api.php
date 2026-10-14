<?php
use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductSearchController;
use App\Enums\UserRole;

Route::prefix('auth')->group(function () {
    Route::post('register/customer', [AuthController::class, 'registerCustomer']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    
    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);      
        Route::post('logout', [AuthController::class, 'logout']);
    });

    Route::middleware(['auth:api', 'role:'.UserRole::ADMIN->value])->group(function () {
        Route::post('register/admin', [AuthController::class, 'registerAdmin']);
    });
   
});

Route::prefix('products')->group(function() {
       
    Route::middleware(['auth:api', 'role:'.UserRole::ADMIN->value.'|'.UserRole::CUSTOMER->value])->group(function () {
         Route::get('search', [ProductSearchController::class, 'index']);
    });

    });