<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\AdminJWTMiddleware;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->get('me', [AuthController::class, 'me']);
Route::middleware('auth:api')->post('logout', [AuthController::class, 'logout']);

// Password reset routes
Route::post('password/forgot', [AuthController::class, 'forgotPassword']);
Route::post('password/reset', [AuthController::class, 'resetPassword'])->name('password.reset');

// Route to handle password reset. This route is only for confirming that the token is valid.
Route::get('password/reset/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');

// Admin routes
Route::prefix('admin')->group(function () {
    Route::post('register', [AdminController::class, 'register']);
    Route::post('login', [AdminController::class, 'login']);
    Route::post('forgot-password', [AdminController::class, 'forgotPassword']);
    Route::post('reset-password', [AdminController::class, 'resetPassword']);
    Route::middleware(AdminJWTMiddleware::class)->group(function () {
        Route::get('me', [AdminController::class, 'me']);
        Route::post('logout', [AdminController::class, 'logout']);
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