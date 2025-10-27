<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

// API routes for frontend
Route::apiResource('users', UserController::class);
Route::post('users/{user}/ban', [UserController::class, 'ban']);
Route::post('users/{user}/unban', [UserController::class, 'unban']);
Route::get('users-stats', [UserController::class, 'stats']);
Route::apiResource('categories', CategoryController::class);

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
        Route::get('/orders', [OrderController::class, 'index']);
        Route::put('/orders/{order}', [OrderController::class, 'update']);
    });
});