<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\CloudinaryController;
use App\Http\Controllers\Api\Admin\AdminUserController;

/*
|--------------------------------------------------------------------------
| CSRF Cookie Route
|--------------------------------------------------------------------------
*/
Route::get('csrf-cookie', function () {
    try {
        return response()->json([
            'message' => 'CSRF cookie set', 
            'status' => 'success',
            'timestamp' => now()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error setting CSRF cookie',
            'error' => $e->getMessage(),
            'status' => 'error'
        ], 500);
    }
});

// Test route to check if API is working
Route::get('test', function () {
    return response()->json(['message' => 'API is working', 'timestamp' => now()]);
});

// Debug route to check session and CSRF
Route::get('debug-session', function () {
    return response()->json([
        'session_id' => session()->getId(),
        'session_data' => session()->all(),
        'csrf_token' => csrf_token(),
        'app_key' => config('app.key'),
        'session_driver' => config('session.driver'),
        'timestamp' => now()
    ]);
});

/*
|--------------------------------------------------------------------------
| Authentication Check Routes
|--------------------------------------------------------------------------
*/
Route::post('check-auth-method', [AuthController::class, 'checkAuthMethod']);

/*
|--------------------------------------------------------------------------
| Customer Authentication Routes (Both Auth0 and Password)
|--------------------------------------------------------------------------
*/
// Traditional password signup/login for customers
Route::post('customer/register', [AuthController::class, 'registerCustomer']);
Route::post('customer/login', [AuthController::class, 'loginCustomer']);
Route::post('customer/verify-email', [AuthController::class, 'verifyEmailCode']);
Route::post('customer/resend-verification', [AuthController::class, 'resendVerificationCode']);
Route::post('password/send-reset-code', [\App\Http\Controllers\Api\AuthController::class, 'sendResetCode']);
Route::post('password/reset', [\App\Http\Controllers\Api\AuthController::class, 'resetPassword']);

// Auth0 signup/login for customers
Route::post('customer/register-auth0', [AuthController::class, 'registerCustomerAuth0']);
Route::post('auth0-callback', [AuthController::class, 'auth0Callback']);

// Universal login (detects auth method)
Route::post('login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Admin Authentication Routes (Password only)
|--------------------------------------------------------------------------
*/
Route::post('admin/register', [AuthController::class, 'registerAdmin']);
Route::post('admin/login', [AuthController::class, 'loginAdmin']);


// Cart routes (accessible by both authenticated customers and guests with session ID)
Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index']);
    Route::post('/', [CartController::class, 'store']);
    Route::put('/{item}', [CartController::class, 'update']);
    Route::delete('/{item}', [CartController::class, 'destroy']);
    Route::delete('/', [CartController::class, 'clear']);
    Route::post('/cleanup-expired', [CartController::class, 'cleanupExpiredGuestCarts']);
    Route::post('/migrate-guest', [CartController::class, 'migrateGuestCart'])->middleware('auth:sanctum');
    
    // Custom jacket routes
    Route::post('custom-jacket', [\App\Http\Controllers\Api\CustomJacketController::class, 'addToCart']);
    Route::get('custom-jackets', [\App\Http\Controllers\Api\CustomJacketController::class, 'getCart']);
    Route::put('custom-jacket/{customItem}', [\App\Http\Controllers\Api\CustomJacketController::class, 'updateQuantity']);
    Route::delete('custom-jacket/{customItem}', [\App\Http\Controllers\Api\CustomJacketController::class, 'removeFromCart']);
});

// Order routes (accessible by both authenticated customers and guests with session ID)
Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/', [OrderController::class, 'store']);
    Route::get('/{order}', [OrderController::class, 'show']);
    Route::post('/validate-checkout', [OrderController::class, 'validateCheckout']);
    Route::post('/release-stock-reservation', [OrderController::class, 'releaseStockReservation']);
});

/*
|--------------------------------------------------------------------------
| Public Routes - Read-only access for everyone
|--------------------------------------------------------------------------
*/

// Public product routes with proper controller handling
Route::apiResource('products', ProductController::class)->only(['index', 'show']);

Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);

// Contact form submission
Route::post('contact', [\App\Http\Controllers\Api\ContactController::class, 'submit']);

// Blog routes (public access)
Route::get('blogs', [\App\Http\Controllers\Api\BlogController::class, 'index']);
Route::get('blogs/{slug}', [\App\Http\Controllers\Api\BlogController::class, 'show']);
Route::get('blogs/{slug}/related', [\App\Http\Controllers\Api\BlogController::class, 'related']);
Route::post('blogs/{slug}/like', [\App\Http\Controllers\Api\BlogController::class, 'like']);
Route::post('blogs/{slug}/view', [\App\Http\Controllers\Api\BlogController::class, 'view']);
Route::get('blog-tags', [\App\Http\Controllers\Api\BlogController::class, 'tags']);

/*
|--------------------------------------------------------------------------
| Admin Protected Routes - Password-authenticated admins only
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    // FAQ admin endpoints
    Route::get('faqs', [\App\Http\Controllers\Api\FaqController::class, 'index']);
    Route::post('faqs', [\App\Http\Controllers\Api\FaqController::class, 'store']);
    Route::put('faqs/{faq}', [\App\Http\Controllers\Api\FaqController::class, 'update']);
    Route::delete('faqs/{faq}', [\App\Http\Controllers\Api\FaqController::class, 'destroy']);
    // Product management (admin only)
    Route::apiResource('products', ProductController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('categories', CategoryController::class)->only(['store', 'update', 'destroy']);
    
    // Variant management for products
    Route::post('products/{product}/variants', [ProductController::class, 'storeVariant']);
    Route::put('products/{product}/variants/{variant}', [ProductController::class, 'updateVariant']);
    Route::delete('products/{product}/variants/{variant}', [ProductController::class, 'destroyVariant']);
    
    // Standalone variant management (alternative endpoints)
    Route::delete('variants/{variant}', [ProductController::class, 'destroyVariantStandalone']);
    
    // Product image management
    Route::get('products/{product}/images/categorized', [ProductController::class, 'getCategorizedImages']);
    Route::post('products/{product}/images', [ProductController::class, 'uploadImage']);
    Route::delete('products/{product}/images/{image}', [ProductController::class, 'deleteImage']);
    Route::put('products/{product}/images/reorder', [ProductController::class, 'reorderImages']);
    
    // Product statistics
    Route::get('products/stats', [ProductController::class, 'getProductStats']);
    Route::get('products/low-stock', [ProductController::class, 'getLowStockProducts']);
    Route::get('products/out-of-stock', [ProductController::class, 'getOutOfStockProducts']);
    
    // Bulk upload routes
    Route::post('products/bulk-upload', [ProductController::class, 'bulkUpload']);
    Route::get('products/bulk-upload/template', [ProductController::class, 'getBulkUploadTemplate']);
    
    // Local storage management
    Route::get('local-storage/usage', [\App\Http\Controllers\Api\LocalStorageController::class, 'getStorageUsage']);
    Route::post('local-storage/cleanup', [\App\Http\Controllers\Api\LocalStorageController::class, 'cleanupStorage']);
    Route::delete('local-storage/image', [\App\Http\Controllers\Api\LocalStorageController::class, 'deleteImage']);
    Route::get('local-storage/optimized-url', [\App\Http\Controllers\Api\LocalStorageController::class, 'getOptimizedUrl']);
    
    // Order management (admin only)
    Route::prefix('admin')->group(function () {
        Route::get('orders', [OrderController::class, 'adminIndex']);
        Route::put('orders/{order}', [OrderController::class, 'adminUpdate']);
    });
    
    // User management routes
    Route::get('admin/users', [AdminUserController::class, 'index']);
    Route::get('admin/users/{user}', [AdminUserController::class, 'show']);
    Route::post('admin/users', [AdminUserController::class, 'store']);
    Route::put('admin/users/{user}', [AdminUserController::class, 'update']);
    Route::delete('admin/users/{user}', [AdminUserController::class, 'destroy']);
    Route::patch('admin/users/{user}/status', [AdminUserController::class, 'updateStatus']);
    Route::post('admin/users/{user}/reset-password', [AdminUserController::class, 'resetPassword']);
    
    // User statistics and bulk operations
    Route::get('admin/users/stats', [AdminUserController::class, 'getUserStats']);
    Route::post('admin/users/bulk-status', [AdminUserController::class, 'bulkUpdateStatus']);
    Route::post('admin/users/bulk-delete', [AdminUserController::class, 'bulkDelete']);
    
    // Admin dashboard stats
    Route::get('admin/stats', [\App\Http\Controllers\Api\AdminDashboardController::class, 'getStats']);
    Route::get('admin/recent-registrations', [\App\Http\Controllers\Api\AdminDashboardController::class, 'getRecentRegistrations']);
    Route::get('admin/recent-orders', [\App\Http\Controllers\Api\AdminDashboardController::class, 'getRecentOrders']);
    Route::post('admin/users/{user}/mark-notified', [\App\Http\Controllers\Api\AdminDashboardController::class, 'markRegistrationAsNotified']);
    
    // Admin blog management
    Route::apiResource('admin/blogs', \App\Http\Controllers\Api\Admin\AdminBlogController::class);
    Route::get('admin/blog-stats', [\App\Http\Controllers\Api\Admin\AdminBlogController::class, 'stats']);
    Route::post('admin/blogs/bulk-action', [\App\Http\Controllers\Api\Admin\AdminBlogController::class, 'bulkAction']);
    Route::get('admin/blogs/analytics', [\App\Http\Controllers\Api\Admin\AdminBlogController::class, 'analytics']);
    Route::get('admin/blogs/export', [\App\Http\Controllers\Api\Admin\AdminBlogController::class, 'export']);
    
    // Admin contact form management
    Route::get('admin/contact-forms', [\App\Http\Controllers\Api\Admin\AdminContactController::class, 'index']);
    Route::put('admin/contact-forms/{id}', [\App\Http\Controllers\Api\Admin\AdminContactController::class, 'update']);
    Route::delete('admin/contact-forms/{id}', [\App\Http\Controllers\Api\Admin\AdminContactController::class, 'destroy']);
    Route::post('admin/contact-forms/{id}/reply', [\App\Http\Controllers\Api\Admin\AdminContactController::class, 'reply']);
    Route::get('admin/contact-stats', [\App\Http\Controllers\Api\Admin\AdminContactController::class, 'stats']);
    Route::post('admin/contact-forms/bulk-update', [\App\Http\Controllers\Api\Admin\AdminContactController::class, 'bulkUpdate']);
    Route::post('admin/contact-forms/bulk-delete', [\App\Http\Controllers\Api\Admin\AdminContactController::class, 'bulkDelete']);
});

// Variant video upload
Route::post('products/{product}/variant-video', [\App\Http\Controllers\Api\VariantVideoController::class, 'upload']);

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/
Route::get('products/{product}/reviews', [\App\Http\Controllers\Api\ProductReviewController::class, 'index']);

// Contact form submission (public route)
Route::post('contact', [\App\Http\Controllers\Api\ContactController::class, 'submit']);

Route::middleware(['auth:sanctum'])->group(function () {
    $userController = '\App\Http\Controllers\Api\UserController';
    
    Route::get('/user', [AuthController::class, 'me']);
    Route::put('/user', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    // Get user addresses
    Route::get('/user/addresses', [$userController, 'getAddresses']);
    
    // Order payment status update
    Route::put('orders/{order}/payment-status', [OrderController::class, 'updatePaymentStatus']);
    
    // Clear cart after successful payment
    Route::post('orders/{order}/clear-cart', [OrderController::class, 'clearCartAfterPayment']);
    
    // User address management
    Route::prefix('user')->group(function () {
        Route::apiResource('addresses', AddressController::class);
        Route::patch('addresses/{address}/default', [AddressController::class, 'setDefault']);
    });
    
    // Product review management
    Route::prefix('products/{product}')->group(function () {
        Route::post('reviews', [\App\Http\Controllers\Api\ProductReviewController::class, 'store']);
        Route::put('reviews/{review}', [\App\Http\Controllers\Api\ProductReviewController::class, 'update']);
        Route::delete('reviews/{review}', [\App\Http\Controllers\Api\ProductReviewController::class, 'destroy']);
        Route::get('can-review', [\App\Http\Controllers\Api\ProductReviewController::class, 'canReview']);
    });
    
    // Admin review moderation
    Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
        Route::get('reviews/pending', [\App\Http\Controllers\Api\ProductReviewController::class, 'pendingReviews']);
        Route::post('reviews/{review}/approve', [\App\Http\Controllers\Api\ProductReviewController::class, 'approve']);
        Route::post('reviews/{review}/reject', [\App\Http\Controllers\Api\ProductReviewController::class, 'reject']);
    });
});

// FAQ public endpoints
Route::get('faqs', [\App\Http\Controllers\Api\FaqController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Stripe Payment Routes (Guest and Authenticated)
|--------------------------------------------------------------------------
*/
// Guest-friendly Stripe routes (no auth required)
Route::prefix('stripe')->group(function () {
    Route::post('create-payment-intent', [\App\Http\Controllers\Api\StripeController::class, 'createPaymentIntent']);
    Route::post('confirm-payment', [\App\Http\Controllers\Api\StripeController::class, 'confirmPayment']);
    Route::get('payment-intent/{paymentIntentId}', [\App\Http\Controllers\Api\StripeController::class, 'getPaymentIntentStatus']);
});

// Stripe webhook (no auth required)
Route::post('stripe/webhook', [\App\Http\Controllers\Api\StripeController::class, 'handleWebhook']);

// Test Stripe connection (temporary, remove in production)
Route::get('stripe/test', function() {
    try {
        $stripeSecret = config('services.stripe.secret');
        if (!$stripeSecret) {
            return response()->json(['error' => 'Stripe secret not configured'], 500);
        }
        
        \Stripe\Stripe::setApiKey($stripeSecret);
        
        // Try to create a simple test payment intent
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => 100, // 1 EUR in cents
            'currency' => 'eur',
            'automatic_payment_methods' => ['enabled' => true],
        ]);
        
        return response()->json([
            'success' => true,
            'payment_intent_id' => $paymentIntent->id,
            'stripe_secret_length' => strlen($stripeSecret),
            'stripe_secret_start' => substr($stripeSecret, 0, 10) . '...',
            'stripe_secret_end' => '...' . substr($stripeSecret, -10),
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'stripe_secret_length' => strlen($stripeSecret ?? ''),
            'stripe_secret_start' => $stripeSecret ? (substr($stripeSecret, 0, 10) . '...') : 'N/A',
            'stripe_secret_end' => $stripeSecret ? ('...' . substr($stripeSecret, -10)) : 'N/A',
        ], 500);
    }
});