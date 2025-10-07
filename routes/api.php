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
// use App\Http\Controllers\Api\Admin\AdminUserController; // Controller not created yet

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
// Debug route to test database connectivity
Route::get('debug/products', function() {
    try {
        $count = \App\Models\Product::count();
        return response()->json([
            'status' => 'ok',
            'products_count' => $count,
            'database' => 'connected'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'error' => $e->getMessage()
        ], 500);
    }
});

// Simple test endpoint for XHR debugging
Route::get('test', function() {
    return response()->json([
        'message' => 'API is working',
        'timestamp' => now(),
        'cors' => 'enabled'
    ]);
});

// Simplified products endpoint for debugging
Route::get('products', function() {
    try {
        $products = \App\Models\Product::with(['category', 'images'])->get();
        return response()->json([
            'data' => $products->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'gender' => $product->gender,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name
                    ] : null,
                    'images' => $product->images->map(function($image) {
                        return [
                            'id' => $image->id,
                            'url' => $image->url,
                            'alt_text' => $image->alt_text
                        ];
                    })
                ];
            })
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to load products',
            'message' => $e->getMessage()
        ], 500);
    }
});

Route::get('products/{id}', function($id) {
    try {
        $product = \App\Models\Product::with(['category', 'images'])->find($id);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        return response()->json([
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'gender' => $product->gender,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name
                ] : null,
                'images' => $product->images->map(function($image) {
                    return [
                        'id' => $image->id,
                        'url' => $image->url,
                        'alt_text' => $image->alt_text
                    ];
                })
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to load product',
            'message' => $e->getMessage()
        ], 500);
    }
});
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
    
    // User management routes (commented out - controller needs to be created)
    // Route::get('admin/users', [AdminUserController::class, 'index']);
    // Route::post('admin/users', [AdminUserController::class, 'store']);
    // Route::put('admin/users/{user}/role', [AdminUserController::class, 'updateRole']);
    // Route::delete('admin/users/{user}', [AdminUserController::class, 'destroy']);
    
    // Admin dashboard stats
    Route::get('admin/stats', function () {
        return response()->json([
            'total_products' => \App\Models\Product::count(),
            'total_categories' => \App\Models\Category::count(),
            'total_variants' => \App\Models\ProductVariant::count(),
            'total_customers' => \App\Models\User::customers()->count(),
            'low_stock_variants' => \App\Models\ProductVariant::where('stock', '<', 10)->count(),
        ]);
    });
});

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
        
        // Admin contact form management
        Route::get('contact-forms', [\App\Http\Controllers\Api\Admin\AdminContactController::class, 'index']);
        Route::put('contact-forms/{id}', [\App\Http\Controllers\Api\Admin\AdminContactController::class, 'update']);
        Route::delete('contact-forms/{id}', [\App\Http\Controllers\Api\Admin\AdminContactController::class, 'destroy']);
        Route::post('contact-forms/{id}/reply', [\App\Http\Controllers\Api\Admin\AdminContactController::class, 'reply']);
        
        // Admin blog management
        Route::apiResource('blogs', \App\Http\Controllers\Api\Admin\AdminBlogController::class);
        Route::get('blog-stats', [\App\Http\Controllers\Api\Admin\AdminBlogController::class, 'stats']);
    });
});

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
