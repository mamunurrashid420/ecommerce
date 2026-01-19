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
use App\Http\Controllers\Api\SupportTicketController;
use App\Http\Controllers\Api\SupportMessageController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\CustomerDashboardController;
use App\Http\Controllers\Api\ExportReportController;
use App\Http\Controllers\Api\LandingPageController;
use App\Http\Controllers\Dropship\ProductController as DropshipProductController;
use App\Http\Controllers\Dropship\OrderController as DropshipOrderController;
use App\Http\Controllers\Dropship\ShopController as DropshipShopController;
use App\Http\Controllers\Dropship\CategoryController as DropshipCategoryController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController as ApiOrderController;
use App\Http\Controllers\Api\ShippingRateController;
use App\Http\Controllers\Api\AdminShippingRateController;
use App\Http\Controllers\Api\SavedProductController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\UpazilaController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\AdminOfferController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Admin/User Authentication (Email/Password)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/product-list', [DropshipProductController::class, 'searchProducts']);
Route::get('/product-details/{itemId}', [DropshipProductController::class, 'productDetails']);
Route::get('/product-description/{itemId}', [DropshipProductController::class, 'productDescription']);

// Product search by image API (upload image file)
Route::post('/product-search-by-image', [DropshipProductController::class, 'searchProductsByImage']);

// Shop Routes (Public)
Route::get('/shop/{sellerId}', [DropshipShopController::class, 'show']);
Route::get('/shop/{sellerId}/products', [DropshipShopController::class, 'products']);



// Customer Authentication (Mobile OTP)
Route::prefix('customer')->group(function () {
    Route::post('/login', [CustomerAuthController::class, 'login']); // Send OTP to mobile
    Route::post('/verify-otp', [CustomerAuthController::class, 'verifyOtp']); // Verify OTP and get token
    
});

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

// Public Purchase Management Routes (No authentication required)
Route::prefix('purchase')->group(function () {
    Route::post('/check-availability', [PurchaseController::class, 'checkAvailability']);
    Route::post('/summary', [PurchaseController::class, 'getSummary']);
});

// Landing Page API (Public - No authentication required)
Route::prefix('landing')->group(function () {
    Route::get('/', [LandingPageController::class, 'index']);
    Route::get('/hero', [LandingPageController::class, 'hero']);
    Route::get('/featured-products', [LandingPageController::class, 'featuredProducts']);
    Route::get('/top-selling-products', [LandingPageController::class, 'topSellingProducts']);
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

// Public Shipping Rate routes (No authentication required)
Route::prefix('shipping-rates')->group(function () {
    Route::get('/', [ShippingRateController::class, 'index']);
    Route::get('/grouped', [ShippingRateController::class, 'grouped']);
    Route::get('/category/{category}', [ShippingRateController::class, 'byCategory']);
});

// Public District and Upazila routes (No authentication required)
Route::prefix('districts')->group(function () {
    Route::get('/', [DistrictController::class, 'index']);
    Route::get('/divisions', [DistrictController::class, 'divisions']);
    Route::get('/{id}', [DistrictController::class, 'show']);
});

Route::prefix('upazillas')->group(function () {
    Route::get('/', [UpazilaController::class, 'index']);
    Route::get('/district/{districtId}', [UpazilaController::class, 'byDistrict']);
    Route::get('/{id}', [UpazilaController::class, 'show']);
});

// Public Payment Method routes (No authentication required)
Route::prefix('payment-methods')->group(function () {
    Route::get('/', [PaymentMethodController::class, 'index']);
    Route::get('/{id}', [PaymentMethodController::class, 'show']);
});

// Public Deal routes (No authentication required)
Route::prefix('deals')->group(function () {
    Route::get('/', [DealController::class, 'index']);
    Route::get('/featured', [DealController::class, 'featured']);
    Route::get('/flash', [DealController::class, 'flashDeals']);
    Route::get('/product/{productId}', [DealController::class, 'forProduct']);
    Route::get('/category/{categoryId}', [DealController::class, 'forCategory']);
    Route::get('/{identifier}', [DealController::class, 'show']);
});

// Public Offer routes (No authentication required)
Route::prefix('offers')->group(function () {
    Route::get('/', [OfferController::class, 'index']);
    Route::get('/customer', [OfferController::class, 'customerOffers']);
    Route::get('/{id}', [OfferController::class, 'show']);
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

// Customer-specific Category routes (simplified data)
Route::get('/customer/categories', [CategoryController::class, 'customerIndex']);

// Public Search route (available for both authenticated and guest users)
Route::get('/search', [SearchController::class, 'search']);

// Customer routes - use customer middleware for Customer model authentication
Route::middleware('customer')->prefix('customer')->group(function () {
    // Profile
    Route::get('/profile', [CustomerAuthController::class, 'profile']);
    Route::put('/profile', [CustomerAuthController::class, 'updateProfile']);
    Route::post('/profile-picture', [CustomerAuthController::class, 'updateProfilePicture']);
    Route::post('/logout', [CustomerAuthController::class, 'logout']);

    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::post('/cart/add', [CartController::class, 'addItem']);
    Route::put('/cart/items/{cartItemId}', [CartController::class, 'updateItem']);
    Route::delete('/cart/items/{cartItemId}', [CartController::class, 'removeItem']);
    Route::delete('/cart/clear', [CartController::class, 'clearCart']);

    // Orders
    Route::post('/orders/create', [ApiOrderController::class, 'createFromCart']);
    Route::get('/orders', [ApiOrderController::class, 'index']);
    Route::get('/orders/{orderId}', [ApiOrderController::class, 'show']);

    // Saved Products (Wishlist)
    Route::get('/saved-products', [SavedProductController::class, 'index']);
    Route::post('/saved-products', [SavedProductController::class, 'store']);
    Route::post('/saved-products/toggle', [SavedProductController::class, 'toggle']);
    Route::get('/saved-products/check/{productId}', [SavedProductController::class, 'check']);
    Route::delete('/saved-products/{id}', [SavedProductController::class, 'destroy']);


    // Customer Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [CustomerDashboardController::class, 'stats']);
        Route::get('/recent-activity', [CustomerDashboardController::class, 'recentActivity']);
        Route::get('/profile-summary', [CustomerDashboardController::class, 'profile']);
        Route::get('/order-status-breakdown', [CustomerDashboardController::class, 'orderStatusBreakdown']);
        Route::get('/spending-trend', [CustomerDashboardController::class, 'spendingTrend']);
    });
    
});

// Order routes - allow both customers and admins
// GET /orders and GET /orders/{order} - accessible to both customers and admins
// (customerOrders and show methods handle both cases)
Route::middleware('auth.any')->group(function () {
    Route::get('/orders', [OrderController::class, 'customerOrders']);
    // Exclude 'stats' and 'pending-cancellations' from matching as order ID
    Route::get('/orders/{order}', [OrderController::class, 'show'])
        ->where('order', '^(?!stats$|pending-cancellations$).*$');
});

// Customer-only order routes - use customer middleware
Route::middleware('customer')->group(function () {
    Route::post('/orders', [OrderController::class, 'store']);

    // Customer Order Cancellation Routes
    // Exclude 'stats' and 'pending-cancellations' from matching as order ID
    Route::post('/orders/{order}/request-cancellation', [OrderController::class, 'requestCancellation'])
        ->where('order', '^(?!stats$|pending-cancellations$).*$');

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

    // Customer Support Ticket Routes
    Route::post('/support-tickets', [SupportTicketController::class, 'store']);
});

// Order cancellation route - accessible to both customers and admins
Route::middleware('auth.any')->group(function () {
    // Exclude 'stats' and 'pending-cancellations' from matching as order ID
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancelOrder'])
        ->where('order', '^(?!stats$|pending-cancellations$).*$');

    // Support Ticket routes - accessible to both customers and admins
    // GET /support-tickets - accessible to both customers and admins
    // (customerTickets and index methods handle both cases)
    Route::get('/support-tickets', [SupportTicketController::class, 'customerTickets']);

    // Support Ticket Navbar routes - must come before parameterized routes
    Route::get('/support-tickets/navbar/count', [SupportTicketController::class, 'navbarCount']);
    Route::get('/support-tickets/navbar/latest', [SupportTicketController::class, 'navbarLatest']);

    // Support Ticket routes - accessible to both customers and admins
    // Exclude 'stats' and 'navbar' from matching as ticket ID
    Route::get('/support-tickets/{ticket}', [SupportTicketController::class, 'show'])
        ->where('ticket', '^(?!stats$|navbar).*$');

    // Support Message routes - accessible to both customers and admins
    // Exclude 'stats' and 'navbar' from matching as ticket ID
    Route::get('/support-tickets/{ticket}/messages', [SupportMessageController::class, 'index'])
        ->where('ticket', '^(?!stats$|navbar).*$');
    Route::post('/support-tickets/{ticket}/messages', [SupportMessageController::class, 'store'])
        ->where('ticket', '^(?!stats$|navbar).*$');
    Route::put('/support-messages/{message}/read', [SupportMessageController::class, 'markAsRead']);
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
        Route::post('/products/bulk', [ProductController::class, 'bulkStore']);
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
        // Specific routes must come before parameterized routes
        Route::get('/orders/stats', [OrderController::class, 'stats']);
        Route::get('/orders/pending-cancellations', [OrderController::class, 'pendingCancellations']);
        Route::get('/orders/status-transitions', [OrderController::class, 'getStatusTransitions']);
        // Routes with additional path segments must come before single parameter routes
        Route::get('/orders/{order}/next-statuses', [OrderController::class, 'getNextStatuses'])
            ->where('order', '^(?!stats$|pending-cancellations$|status-transitions$).*$');
        Route::put('/orders/{order}/amounts', [OrderController::class, 'updateAmounts'])
            ->where('order', '^(?!stats$|pending-cancellations$|status-transitions$).*$');
        Route::post('/orders/{order}/approve-cancellation', [OrderController::class, 'approveCancellation'])
            ->where('order', '^(?!stats$|pending-cancellations$|status-transitions$).*$');
        Route::post('/orders/{order}/reject-cancellation', [OrderController::class, 'rejectCancellation'])
            ->where('order', '^(?!stats$|pending-cancellations$|status-transitions$).*$');
        // Exclude 'stats', 'pending-cancellations', and 'status-transitions' from matching as order ID
        Route::put('/orders/{order}', [OrderController::class, 'update'])
            ->where('order', '^(?!stats$|pending-cancellations$|status-transitions$).*$');
        Route::delete('/orders/{order}', [OrderController::class, 'destroy'])
            ->where('order', '^(?!stats$|pending-cancellations$|status-transitions$).*$');

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

        // Admin Shipping Rate Management
        Route::prefix('admin/shipping-rates')->group(function () {
            Route::get('/', [AdminShippingRateController::class, 'index']);
            Route::post('/', [AdminShippingRateController::class, 'store']);
            Route::get('/{shippingRate}', [AdminShippingRateController::class, 'show']);
            Route::put('/{shippingRate}', [AdminShippingRateController::class, 'update']);
            Route::delete('/{shippingRate}', [AdminShippingRateController::class, 'destroy']);
            Route::post('/{shippingRate}/toggle-active', [AdminShippingRateController::class, 'toggleActive']);
        });

        // Admin District Management
        Route::prefix('admin/districts')->group(function () {
            Route::get('/', [DistrictController::class, 'index']);
            Route::post('/', [DistrictController::class, 'store']);
            Route::get('/{id}', [DistrictController::class, 'show']);
            Route::put('/{id}', [DistrictController::class, 'update']);
            Route::delete('/{id}', [DistrictController::class, 'destroy']);
            Route::post('/{id}/toggle-active', [DistrictController::class, 'toggleActive']);
        });

        // Admin Upazila Management
        Route::prefix('admin/upazillas')->group(function () {
            Route::get('/', [UpazilaController::class, 'index']);
            Route::post('/', [UpazilaController::class, 'store']);
            Route::get('/{id}', [UpazilaController::class, 'show']);
            Route::put('/{id}', [UpazilaController::class, 'update']);
            Route::delete('/{id}', [UpazilaController::class, 'destroy']);
            Route::post('/{id}/toggle-active', [UpazilaController::class, 'toggleActive']);
        });

        // Admin Payment Method Management
        Route::prefix('admin/payment-methods')->group(function () {
            Route::get('/', [PaymentMethodController::class, 'index']);
            Route::post('/', [PaymentMethodController::class, 'store']);
            Route::get('/{id}', [PaymentMethodController::class, 'show']);
            Route::post('/{id}', [PaymentMethodController::class, 'update']); // POST for form-data with file
            Route::put('/{id}', [PaymentMethodController::class, 'update']); // PUT for JSON
            Route::delete('/{id}', [PaymentMethodController::class, 'destroy']);
            Route::post('/{id}/toggle-active', [PaymentMethodController::class, 'toggleActive']);
            Route::delete('/{id}/logo', [PaymentMethodController::class, 'deleteLogo']);
            Route::post('/sort-order', [PaymentMethodController::class, 'updateSortOrder']);
        });

        // Admin Deal Management
        Route::prefix('admin/deals')->group(function () {
            Route::get('/', [AdminDealController::class, 'index']);
            Route::post('/', [AdminDealController::class, 'store']);
            Route::get('/stats', [AdminDealController::class, 'stats']);
            Route::get('/{deal}', [AdminDealController::class, 'show']);
            Route::put('/{deal}', [AdminDealController::class, 'update']);
            Route::delete('/{deal}', [AdminDealController::class, 'destroy']);
            Route::post('/{deal}/toggle-active', [AdminDealController::class, 'toggleActive']);
            Route::post('/{deal}/toggle-featured', [AdminDealController::class, 'toggleFeatured']);
        });

        // Admin Offer Management
        Route::prefix('admin/offers')->group(function () {
            Route::get('/', [AdminOfferController::class, 'index']);
            Route::post('/', [AdminOfferController::class, 'store']);
            Route::get('/stats', [AdminOfferController::class, 'stats']);
            Route::get('/{offer}', [AdminOfferController::class, 'show']);
            Route::put('/{offer}', [AdminOfferController::class, 'update']);
            Route::post('/{offer}', [AdminOfferController::class, 'update']); // POST for form-data with file
            Route::delete('/{offer}', [AdminOfferController::class, 'destroy']);
            Route::post('/{offer}/toggle-active', [AdminOfferController::class, 'toggleActive']);
            Route::post('/{offer}/toggle-featured', [AdminOfferController::class, 'toggleFeatured']);
            Route::post('/sort-order', [AdminOfferController::class, 'updateSortOrder']);
        });

        // Admin Support Ticket Management
        Route::prefix('support-tickets')->group(function () {
            // Note: GET /support-tickets is handled by customerTickets route above (supports both customers and admins)
            Route::get('/stats', [SupportTicketController::class, 'stats']); // Must come before /{ticket} routes
            Route::put('/{ticket}/status', [SupportTicketController::class, 'updateStatus']);
            Route::put('/{ticket}/priority', [SupportTicketController::class, 'updatePriority']);
            Route::post('/{ticket}/assign', [SupportTicketController::class, 'assign']);
            Route::delete('/{ticket}', [SupportTicketController::class, 'destroy']);
        });

        // Admin Dashboard Routes
        Route::prefix('dashboard')->group(function () {
            Route::get('/stats', [AdminDashboardController::class, 'stats']);
            Route::get('/sales-trends', [AdminDashboardController::class, 'salesTrends']);
            Route::get('/top-products', [AdminDashboardController::class, 'topProducts']);
            Route::get('/top-customers', [AdminDashboardController::class, 'topCustomers']);
            Route::get('/recent-orders', [AdminDashboardController::class, 'recentOrders']);
            Route::get('/low-stock-alerts', [AdminDashboardController::class, 'lowStockAlerts']);
            Route::get('/category-sales', [AdminDashboardController::class, 'categorySales']);
        });

        // Export Report Routes
        Route::prefix('exports')->group(function () {
            Route::get('/orders', [ExportReportController::class, 'exportOrders']);
            Route::get('/products', [ExportReportController::class, 'exportProducts']);
            Route::get('/customers', [ExportReportController::class, 'exportCustomers']);
            Route::get('/sales-report', [ExportReportController::class, 'exportSalesReport']);
            Route::get('/product-sales-report', [ExportReportController::class, 'exportProductSalesReport']);
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

/*
|--------------------------------------------------------------------------
| Dropship API Routes
|--------------------------------------------------------------------------
|
| Routes for dropshipping integration with Taobao, 1688, Tmall platforms
| Protected by admin authentication
|
*/
Route::middleware(['auth:sanctum', 'admin'])->prefix('dropship')->group(function () {

    // Product Routes
    Route::prefix('products')->group(function () {
        // Search products by keyword
        Route::get('/search', [DropshipProductController::class, 'search']);

        // Search products by image
        Route::post('/search-by-image', [DropshipProductController::class, 'searchByImage']);

        // Import product to local store
        Route::post('/import', [DropshipProductController::class, 'import']);

        // Get product details by ID
        Route::get('/{numIid}', [DropshipProductController::class, 'show']);

        // Get product description/images
        Route::get('/{numIid}/description', [DropshipProductController::class, 'description']);

        // Get product reviews
        Route::get('/{numIid}/reviews', [DropshipProductController::class, 'reviews']);

        // Get shipping fee
        Route::get('/{numIid}/shipping', [DropshipProductController::class, 'shipping']);
    });

    // Order Sourcing Routes
    Route::prefix('orders')->group(function () {
        // Bulk check sourcing availability
        Route::post('/bulk-source-check', [DropshipOrderController::class, 'bulkSourceCheck']);

        // Get order sourcing details
        Route::get('/{order}/source', [DropshipOrderController::class, 'source']);

        // Get price comparison for order
        Route::get('/{order}/price-comparison', [DropshipOrderController::class, 'priceComparison']);

        // Mark order items as sourced
        Route::post('/{order}/mark-sourced', [DropshipOrderController::class, 'markSourced']);
    });

    // Shop Routes
    Route::prefix('shops')->group(function () {
        // Get shop information
        Route::get('/{sellerId}', [DropshipShopController::class, 'show']);

        // Get shop products
        Route::get('/{sellerId}/products', [DropshipShopController::class, 'products']);
    });

    // Category Routes
    Route::prefix('categories')->group(function () {
        // Get category information
        Route::get('/{catId}', [DropshipCategoryController::class, 'show']);

        // Get category products
        Route::get('/{catId}/products', [DropshipCategoryController::class, 'products']);
    });
});