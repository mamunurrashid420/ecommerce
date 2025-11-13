<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SiteSettingController;
use App\Http\Controllers\Api\InventoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Admin/User Authentication (Email/Password)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Customer Authentication (Phone/OTP)
Route::prefix('customer')->group(function () {
    Route::post('/send-otp', [CustomerAuthController::class, 'sendOtp']);
    Route::post('/register', [CustomerAuthController::class, 'register']);
    Route::post('/login', [CustomerAuthController::class, 'login']);
});
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

// Public site settings (for frontend)
Route::get('/site-settings/public', [SiteSettingController::class, 'public']);

// API routes for frontend
Route::apiResource('users', UserController::class);
Route::post('users/{user}/ban', [UserController::class, 'ban']);
Route::post('users/{user}/unban', [UserController::class, 'unban']);
Route::get('users-stats', [UserController::class, 'stats']);

// Public Category routes
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/dropdown', [CategoryController::class, 'dropdown']);
Route::get('/categories/featured', [CategoryController::class, 'featured']);
Route::get('/categories/tree', [CategoryController::class, 'tree']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

// Customer routes - use customer middleware for Customer model authentication
Route::middleware('customer')->prefix('customer')->group(function () {
    Route::post('/logout', [CustomerAuthController::class, 'logout']);
    Route::put('/profile', [CustomerAuthController::class, 'updateProfile']);
});

// Customer order routes - use customer middleware
Route::middleware('customer')->group(function () {
    Route::get('/orders', [OrderController::class, 'customerOrders']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
});

// Protected routes (Admin/User authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Admin/User logout
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Site Settings (accessible to all authenticated users)
    Route::get('/site-settings', [SiteSettingController::class, 'show']);
    
    // Admin only routes
    Route::middleware('admin')->group(function () {
        Route::apiResource('customers', CustomerController::class);
        Route::apiResource('products', ProductController::class)->except(['index', 'show']);
        // Product Management (Admin)
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
        
        // Product Image Management
        Route::post('/products/{product}/images', [ProductController::class, 'uploadImages']);
        Route::delete('/products/{product}/images/{media}', [ProductController::class, 'removeImage']);
        Route::put('/products/{product}/images/{media}/thumbnail', [ProductController::class, 'setThumbnail']);
        Route::put('/products/{product}/images/{media}', [ProductController::class, 'updateImage']);
        
        // Admin Category Management
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
        Route::post('/categories/sort-order', [CategoryController::class, 'updateSortOrder']);
        Route::put('/categories/{category}/toggle-featured', [CategoryController::class, 'toggleFeatured']);
        Route::put('/categories/{category}/toggle-active', [CategoryController::class, 'toggleActive']);
        
        // Admin Order Management
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/stats', [OrderController::class, 'stats']);
        Route::put('/orders/{order}', [OrderController::class, 'update']);
        Route::delete('/orders/{order}', [OrderController::class, 'destroy']);
        
        // Site Settings Management (Admin only - Update)
        Route::post('/site-settings', [SiteSettingController::class, 'createOrUpdate']);
        
        // Inventory Management
        Route::prefix('inventory')->group(function () {
            // Get stock levels
            Route::get('/products/{product}', [InventoryController::class, 'getStock']);
            Route::post('/products/bulk', [InventoryController::class, 'getBulkStock']);
            Route::get('/products/{product}/check', [InventoryController::class, 'checkStock']);
            
            // Stock adjustments
            Route::post('/products/{product}/adjust', [InventoryController::class, 'adjustStock']);
            Route::put('/products/{product}/set', [InventoryController::class, 'setStock']);
            Route::post('/products/{product}/reserve', [InventoryController::class, 'reserveStock']);
            Route::post('/products/{product}/release', [InventoryController::class, 'releaseStock']);
            Route::post('/products/bulk-adjust', [InventoryController::class, 'bulkAdjustStock']);
            
            // Stock alerts
            Route::get('/low-stock', [InventoryController::class, 'getLowStock']);
            Route::get('/out-of-stock', [InventoryController::class, 'getOutOfStock']);
            
            // Inventory history
            Route::get('/products/{product}/history', [InventoryController::class, 'getHistory']);
        });
    });
});