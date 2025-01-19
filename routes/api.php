<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Middleware\AdminJWTMiddleware;
use App\Http\Middleware\CustomerJWTMiddleware;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\ProductController;

// Customer auth routes
Route::prefix('customer')->group(function () {
    Route::post('register', [CustomerAuthController::class, 'register']);
    Route::post('login', [CustomerAuthController::class, 'login']);

    Route::post('password/forgot', [CustomerAuthController::class, 'forgotPassword']);
    Route::post('password/reset', [CustomerAuthController::class, 'resetPassword'])->name('password.reset');
    Route::get('password/reset/{token}', [CustomerAuthController::class, 'showResetForm'])->name('password.reset');

    Route::middleware(CustomerJWTMiddleware::class)->group(function () {
        Route::get('me', [CustomerAuthController::class, 'me']);
        Route::post('logout', [CustomerAuthController::class, 'logout']);
    });
});

// Admin auth routes
Route::prefix('admin')->group(function () {
    Route::post('register', [AdminAuthController::class, 'register']);
    Route::post('login', [AdminAuthController::class, 'login']);
    
    Route::post('forgot-password', [AdminAuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AdminAuthController::class, 'resetPassword']);

    Route::middleware(AdminJWTMiddleware::class)->group(function () {
        Route::get('me', [AdminAuthController::class, 'me']);
        Route::post('logout', [AdminAuthController::class, 'logout']);
    });
});

// Category routes
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'getAll']);                 // Get all categories
    Route::post('/', [CategoryController::class, 'create']);                // Create category
    Route::patch('{id}/disable', [CategoryController::class, 'disable']);  // Disable category
    Route::patch('{id}/enable', [CategoryController::class, 'enable']);    // Enable category
    Route::delete('{id}', [CategoryController::class, 'delete']);          // Delete category
    Route::put('{id}', [CategoryController::class, 'edit']);               // Edit category
    Route::get('{id}', [CategoryController::class, 'getById']);            // Get category by ID
    Route::get('{id}/subcategories', [CategoryController::class, 'getSubcategories']); // Get all subcategories of a parent
});

// Slider routes
Route::prefix('sliders')->group(function () {
    Route::get('/', [SliderController::class, 'getAll']);              // Fetch all slider images
    Route::post('{id}', [SliderController::class, 'uploadOrReplace']); // Upload or replace a slider image by ID
    Route::delete('{id}', [SliderController::class, 'deleteById']);   // Delete a slider by ID
});

// Product routes
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::post('/create', [ProductController::class, 'store']);
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);

    // Variation routes
    Route::post('/{productId}/variations', [ProductController::class, 'storeVariation']);
    Route::put('/{productId}/variations/{variationId}', [ProductController::class, 'updateVariation']);
    Route::delete('/{productId}/variations/{variationId}', [ProductController::class, 'destroyVariation']);
});