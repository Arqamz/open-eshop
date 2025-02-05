<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Middleware\AdminJWTMiddleware;
use App\Http\Middleware\CustomerJWTMiddleware;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ColorController;


// Auth routes
Route::prefix('auth')->group(function () {
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
        });
    });
});

use App\Http\Controllers\CustomerController;

Route::prefix('customer')->group(function () {
    Route::put('/customer/update-info', [CustomerController::class, 'updateCustomerInfo']);
    Route::get('/cart', [CustomerController::class, 'getCart']);
    Route::post('/cart/add-item', [CustomerController::class, 'addItemToCart']);
    Route::put('/cart/update-item/{cartItemId}', [CustomerController::class, 'updateCartItem']);
    Route::delete('/cart/remove-item/{cartItemId}', [CustomerController::class, 'removeItemFromCart']);
});

// Category routes
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'getAll']);
    Route::post('/', [CategoryController::class, 'create']);
    Route::patch('{id}/disable', [CategoryController::class, 'disable']);
    Route::patch('{id}/enable', [CategoryController::class, 'enable']);
    Route::delete('{id}', [CategoryController::class, 'delete']);
    Route::put('{id}', [CategoryController::class, 'edit']);
    Route::get('{id}', [CategoryController::class, 'getById']);
    Route::get('{id}/subcategories', [CategoryController::class, 'getSubcategories']);
    Route::get('{id}/products', [CategoryController::class, 'getAllProductsForCategory']);
});

// Slider routes
Route::prefix('sliders')->group(function () {
    Route::get('/', [SliderController::class, 'getAll']);
    Route::post('{position}', [SliderController::class, 'uploadOrReplace']);
    Route::delete('{position}', [SliderController::class, 'deleteByPosition']);
});

// Color routes
Route::prefix('color')->group(function () {
    Route::get('/', [ColorController::class, 'index']);
    Route::post('/', [ColorController::class, 'store']);
    Route::get('{id}', [ColorController::class, 'show']);
    Route::put('{id}', [ColorController::class, 'update']);
    Route::delete('{id}', [ColorController::class, 'destroy']);
});

// Product routes
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('{id}', [ProductController::class, 'show']);
    Route::get('slug/{slug}', [ProductController::class, 'getBySlug']);
    Route::get('group/{groupId}', [ProductController::class, 'getByGroupId']);
    Route::post('/', [ProductController::class, 'store']);
    Route::put('{id}', [ProductController::class, 'update']);
    Route::delete('{id}', [ProductController::class, 'destroy']);
    Route::put('{id}/price', [ProductController::class, 'updatePrice']);
    Route::put('{id}/stock', [ProductController::class, 'updateStock']);
    Route::put('{id}/group', [ProductController::class, 'assignGroup']);
    Route::put('{id}/status', [ProductController::class, 'setStatus']);
});