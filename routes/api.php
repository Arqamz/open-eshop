<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Middleware\AdminJWTMiddleware;
use App\Http\Middleware\CustomerJWTMiddleware;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\SizeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ConfigController;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    /*
    |----------------------------------------------------------------------
    | Customer Auth Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('customer')->group(function () {
        // Public customer endpoints
        Route::post('register', [CustomerAuthController::class, 'register']);
        Route::post('login', [CustomerAuthController::class, 'login']);
        Route::post('password/forgot', [CustomerAuthController::class, 'forgotPassword']);
        Route::post('password/reset', [CustomerAuthController::class, 'resetPassword'])->name('password.reset');
        Route::get('password/reset/{token}', [CustomerAuthController::class, 'showResetForm'])->name('password.reset');

        // Protected customer endpoints
        Route::middleware(CustomerJWTMiddleware::class)->group(function () {
            Route::get('me', [CustomerAuthController::class, 'me']);
            Route::post('logout', [CustomerAuthController::class, 'logout']);
        });
    });

    /*
    |----------------------------------------------------------------------
    | Admin Auth Routes
    |----------------------------------------------------------------------
    */
    Route::prefix('admin')->group(function () {
        // Public admin endpoints
        Route::post('register', [AdminAuthController::class, 'register']);
        Route::post('login', [AdminAuthController::class, 'login']);
        Route::post('forgot-password', [AdminAuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AdminAuthController::class, 'resetPassword']);

        // Protected admin endpoints
        Route::middleware(AdminJWTMiddleware::class)->group(function () {
            Route::get('me', [AdminAuthController::class, 'me']);
        });
    });
});

/*
|--------------------------------------------------------------------------
| Admin Management Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(AdminJWTMiddleware::class)->group(function () {
    // Admin management endpoints
    Route::get('view-all', [AdminAuthController::class, 'viewAllAdmins']);
    Route::get('view-customers', [AdminAuthController::class, 'viewAllCustomers']);
    Route::delete('delete/{id}', [AdminAuthController::class, 'deleteAdmin']);

    // Orders management
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'indexAdmin']);
        Route::put('/{orderId}/status', [OrderController::class, 'updateStatus']);
    });

    // Config management
    Route::prefix('config')->group(function () {
        Route::get('{key}', [ConfigController::class, 'getConfigValue']);
        Route::put('{key}', [ConfigController::class, 'updateConfigValue']);
        Route::delete('{key}', [ConfigController::class, 'deleteConfigKey']);
        Route::post('/', [ConfigController::class, 'addConfigValue']);
    });
});

/*
|--------------------------------------------------------------------------
| Customer Routes
|--------------------------------------------------------------------------
*/
Route::prefix('customer')->group(function () {
    Route::middleware(CustomerJWTMiddleware::class)->group(function () {
        // Customer profile and cart
        Route::put('/update-info', [CustomerController::class, 'updateCustomerInfo']);
        Route::post('/cart/add-item', [CustomerController::class, 'addItemToCart']);
        Route::get('/cart', [CustomerController::class, 'getCart']);
        Route::put('/cart/update-item/{cartItemId}', [CustomerController::class, 'updateCartItem']);
        Route::delete('/cart/remove-item/{cartItemId}', [CustomerController::class, 'removeItemFromCart']);
        
        // Checkout
        Route::post('/checkout', [OrderController::class, 'checkout']);

        // Customer orders
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'indexCustomer']);
            Route::get('/{orderId}', [OrderController::class, 'getOrder']);
        });
    });
});

/*
|--------------------------------------------------------------------------
| Category Routes
|--------------------------------------------------------------------------
*/
Route::prefix('categories')->group(function () {
    // Protected category endpoints
    Route::middleware(AdminJWTMiddleware::class)->group(function () {
        Route::post('/', [CategoryController::class, 'create']);
        Route::patch('{id}/disable', [CategoryController::class, 'disable']);
        Route::patch('{id}/enable', [CategoryController::class, 'enable']);
        Route::delete('{id}', [CategoryController::class, 'delete']);
        Route::put('{id}', [CategoryController::class, 'edit']);
    });

    // Public category endpoints
    Route::get('/', [CategoryController::class, 'getAll']);
    Route::get('{id}', [CategoryController::class, 'getById']);
    Route::get('{id}/subcategories', [CategoryController::class, 'getSubcategories']);
    Route::get('{id}/products', [CategoryController::class, 'getAllProductsForCategory']);
});

/*
|--------------------------------------------------------------------------
| Slider Routes
|--------------------------------------------------------------------------
*/
Route::prefix('sliders')->group(function () {
    // Protected slider endpoints
    Route::middleware(AdminJWTMiddleware::class)->group(function () {
        Route::post('{position}', [SliderController::class, 'uploadOrReplace']);
        Route::delete('{position}', [SliderController::class, 'deleteByPosition']);
    });

    // Public slider endpoint
    Route::get('/', [SliderController::class, 'getAll']);
});

/*
|--------------------------------------------------------------------------
| Color Routes
|--------------------------------------------------------------------------
*/
Route::prefix('color')->group(function () {
    // Protected color endpoints
    Route::middleware(AdminJWTMiddleware::class)->group(function () {
        Route::post('/', [ColorController::class, 'store']);
        Route::put('{id}', [ColorController::class, 'update']);
        Route::delete('{id}', [ColorController::class, 'destroy']);
    });

    // Public color endpoints
    Route::get('/', [ColorController::class, 'index']);
    Route::get('{id}', [ColorController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| Size Routes
|--------------------------------------------------------------------------
*/
Route::prefix('size')->group(function () {
    // Protected size endpoints
    Route::middleware(AdminJWTMiddleware::class)->group(function () {
        Route::post('/', [SizeController::class, 'store']);
        Route::put('{id}', [SizeController::class, 'update']);
        Route::delete('{id}', [SizeController::class, 'destroy']);
    });

    // Public size endpoints
    Route::get('/', [SizeController::class, 'index']);
    Route::get('{id}', [SizeController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| Product Routes
|--------------------------------------------------------------------------
*/
Route::prefix('products')->group(function () {
    // Protected product endpoints
    Route::middleware(AdminJWTMiddleware::class)->group(function () {
        Route::post('/', [ProductController::class, 'store']);
        Route::put('{id}', [ProductController::class, 'update']);
        Route::delete('{id}', [ProductController::class, 'destroy']);
        Route::put('{id}/price', [ProductController::class, 'updatePrice']);
        Route::put('{id}/featured', [ProductController::class, 'setFeatured']);
        Route::put('{id}/stock', [ProductController::class, 'updateStock']);
        Route::put('{id}/group', [ProductController::class, 'assignGroup']);
        Route::put('{id}/status', [ProductController::class, 'setStatus']);
    });

    // Public product endpoints
    Route::get('/', [ProductController::class, 'index']);
    Route::get('{id}', [ProductController::class, 'show']);
    Route::get('slug/{slug}', [ProductController::class, 'getBySlug']);
    Route::get('group/{groupId}', [ProductController::class, 'getByGroupId']);
});
