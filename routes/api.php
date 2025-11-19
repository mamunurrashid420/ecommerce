<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\AdminPurchaseController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SiteSettingController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\AdminDealController;
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

// Public Purchase Management Routes (No authentication required)
Route::prefix('purchase')->group(function () {
    Route::post('/check-availability', [PurchaseController::class, 'checkAvailability']);
    Route::post('/summary', [PurchaseController::class, 'getSummary']);
});

// Public site settings (for frontend)
Route::get('/site-settings/public', [SiteSettingController::class, 'public']);

// Public Policy Documents (No authentication required)
Route::get('/policies/terms-of-service', [SiteSettingController::class, 'getTermsOfService']);
Route::get('/policies/privacy-policy', [SiteSettingController::class, 'getPrivacyPolicy']);
Route::get('/policies/return-policy', [SiteSettingController::class, 'getReturnPolicy']);
Route::get('/policies/shipping-policy', [SiteSettingController::class, 'getShippingPolicy']);

// Public Contact APIs (No authentication required)
Route::get('/contact', [ContactController::class, 'getContactInfo']);
Route::post('/contact', [ContactController::class, 'submitContactForm']);

// Public Coupon routes
Route::get('/coupons/available', [CouponController::class, 'available']);

// Public Deal routes (No authentication required)
Route::prefix('deals')->group(function () {
    Route::get('/', [DealController::class, 'index']);
    Route::get('/featured', [DealController::class, 'featured']);
    Route::get('/flash', [DealController::class, 'flashDeals']);
    Route::get('/product/{productId}', [DealController::class, 'forProduct']);
    Route::get('/category/{categoryId}', [DealController::class, 'forCategory']);
    Route::get('/{identifier}', [DealController::class, 'show']);
});

// Admin User Management Routes (Protected - Admin only)
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::apiResource('users', UserController::class);
    Route::post('users/{user}/ban', [UserController::class, 'ban']);
    Route::post('users/{user}/unban', [UserController::class, 'unban']);
    Route::get('users-stats', [UserController::class, 'stats']);
    Route::put('users/{user}/password', [UserController::class, 'updatePassword']);
    Route::post('users/{user}/assign-role', [UserController::class, 'assignRole']);
    Route::put('users/{user}/change-role', [UserController::class, 'changeRole']);
});

// Public Category routes
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/with-products', [CategoryController::class, 'withLatestProducts']);
Route::get('/categories/dropdown', [CategoryController::class, 'dropdown']);
Route::get('/categories/featured', [CategoryController::class, 'featured']);
Route::get('/categories/tree', [CategoryController::class, 'tree']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

// Public Search route (available for both authenticated and guest users)
Route::get('/search', [SearchController::class, 'search']);

// Customer routes - use customer middleware for Customer model authentication
Route::middleware('customer')->prefix('customer')->group(function () {
    Route::get('/profile', [CustomerAuthController::class, 'profile']);
    Route::put('/profile', [CustomerAuthController::class, 'updateProfile']);
    Route::post('/logout', [CustomerAuthController::class, 'logout']);
});

// Order routes - allow both customers and admins
// GET /orders and GET /orders/{order} - accessible to both customers and admins
// (customerOrders and show methods handle both cases)
Route::middleware('auth.any')->group(function () {
    Route::get('/orders', [OrderController::class, 'customerOrders']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
});

// Customer-only order routes - use customer middleware
Route::middleware('customer')->group(function () {
    Route::post('/orders', [OrderController::class, 'store']);
    
    // Customer Purchase Management Routes
    Route::prefix('purchase')->group(function () {
        Route::post('/validate', [PurchaseController::class, 'validateItems']);
        Route::post('/summary', [PurchaseController::class, 'getSummary']);
    });
    
    // Customer Coupon Routes
    Route::prefix('coupons')->group(function () {
        Route::post('/validate', [CouponController::class, 'validate']);
    });
    
    // Customer Deal Routes
    Route::prefix('deals')->group(function () {
        Route::post('/validate', [DealController::class, 'validate']);
    });
});

// Protected routes (Admin/User authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Admin/User logout
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Authenticated Admin User Profile Management
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/profile/password', [AuthController::class, 'updatePassword']);
    
    // Site Settings (accessible to all authenticated users)
    Route::get('/site-settings', [SiteSettingController::class, 'show']);
    
    // Notifications (accessible to all authenticated users)
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread', [NotificationController::class, 'unread']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::get('/stats', [NotificationController::class, 'stats']);
        Route::get('/{id}', [NotificationController::class, 'show']);
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::put('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
        Route::delete('/', [NotificationController::class, 'deleteAll']);
    });
    
    // Admin only routes
    Route::middleware('admin')->group(function () {
        // Customer Management
        Route::apiResource('customers', CustomerController::class);
        Route::post('/customers/{customer}/ban', [CustomerController::class, 'ban']);
        Route::post('/customers/{customer}/unban', [CustomerController::class, 'unban']);
        Route::post('/customers/{customer}/suspend', [CustomerController::class, 'suspend']);
        Route::post('/customers/{customer}/unsuspend', [CustomerController::class, 'unsuspend']);
        Route::get('/customers/{customer}/orders', [CustomerController::class, 'orderHistory']);
        Route::get('/customers-search', [CustomerController::class, 'search']);
        
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
        // Note: GET /orders is handled by customerOrders route above (supports both customers and admins)
        Route::get('/orders/stats', [OrderController::class, 'stats']);
        Route::put('/orders/{order}', [OrderController::class, 'update']);
        Route::delete('/orders/{order}', [OrderController::class, 'destroy']);
        
        // Admin Contact Management
        Route::prefix('admin')->group(function () {
            Route::get('/contacts', [ContactController::class, 'index']);
            Route::get('/contacts/stats', [ContactController::class, 'stats']);
            Route::get('/contacts/{contact}', [ContactController::class, 'show']);
            Route::match(['put', 'patch'], '/contacts/{contact}/status', [ContactController::class, 'updateStatus']);
            Route::delete('/contacts/{contact}', [ContactController::class, 'destroy']);
        });
        
        // Site Settings Management (Admin only - Update)
        Route::post('/site-settings', [SiteSettingController::class, 'createOrUpdate']);
        Route::delete('/site-settings/slider-items', [SiteSettingController::class, 'removeSliderItems']);
        
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
        
        // Admin Product Purchase Management (Supplier Purchases)
        Route::prefix('admin-purchases')->group(function () {
            Route::post('/', [AdminPurchaseController::class, 'recordPurchase']);
            Route::post('/bulk', [AdminPurchaseController::class, 'recordBulkPurchases']);
            Route::get('/history', [AdminPurchaseController::class, 'getAllPurchaseHistory']);
            Route::get('/products/{product}/history', [AdminPurchaseController::class, 'getProductPurchaseHistory']);
            Route::get('/stats', [AdminPurchaseController::class, 'getPurchaseStats']);
        });
        
        // Admin Coupon Management
        Route::prefix('coupons')->group(function () {
            Route::get('/', [CouponController::class, 'index']);
            Route::post('/', [CouponController::class, 'store']);
            Route::get('/stats', [CouponController::class, 'stats']);
            Route::get('/{coupon}', [CouponController::class, 'show']);
            Route::put('/{coupon}', [CouponController::class, 'update']);
            Route::delete('/{coupon}', [CouponController::class, 'destroy']);
            Route::post('/{coupon}/toggle-active', [CouponController::class, 'toggleActive']);
        });
        
        // Admin Deal Management
        Route::prefix('deals')->group(function () {
            Route::get('/', [AdminDealController::class, 'index']);
            Route::post('/', [AdminDealController::class, 'store']);
            Route::get('/stats', [AdminDealController::class, 'stats']);
            Route::get('/{deal}', [AdminDealController::class, 'show']);
            Route::put('/{deal}', [AdminDealController::class, 'update']);
            Route::delete('/{deal}', [AdminDealController::class, 'destroy']);
            Route::post('/{deal}/toggle-active', [AdminDealController::class, 'toggleActive']);
            Route::post('/{deal}/toggle-featured', [AdminDealController::class, 'toggleFeatured']);
        });
        
        // Roles & Permissions Management (Admin only - requires roles.manage permission)
        Route::middleware('permission:roles.manage')->group(function () {
            // Roles Management
            Route::apiResource('roles', RoleController::class);
            Route::post('roles/{role}/permissions', [RoleController::class, 'assignPermissions']);
            Route::delete('roles/{role}/permissions', [RoleController::class, 'removePermissions']);
            Route::get('roles/{role}/permissions', [RoleController::class, 'permissions']);
            Route::get('roles/{role}/users', [RoleController::class, 'users']);
            Route::post('roles/{role}/toggle-active', [RoleController::class, 'toggleActive']);
            
            // Permissions Management
            // Specific routes must come before apiResource to avoid route conflicts
            Route::get('permissions/grouped', [PermissionController::class, 'grouped']);
            Route::get('permissions/groups', [PermissionController::class, 'groups']);
            Route::apiResource('permissions', PermissionController::class);
            Route::post('permissions/{permission}/toggle-active', [PermissionController::class, 'toggleActive']);
        });
    });
});