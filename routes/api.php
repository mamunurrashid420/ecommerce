<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SiteSettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
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

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Customer routes
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    
    // Admin only routes
    Route::middleware('admin')->group(function () {
        Route::apiResource('customers', CustomerController::class);
        Route::apiResource('products', ProductController::class)->except(['index', 'show']);
        
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
        
        Route::get('/orders', [OrderController::class, 'index']);
        Route::put('/orders/{order}', [OrderController::class, 'update']);
        
        // Site Settings Management
        Route::get('/site-settings', [SiteSettingController::class, 'show']);
        Route::post('/site-settings', [SiteSettingController::class, 'createOrUpdate']);
    });
});