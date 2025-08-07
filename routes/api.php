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
});

// Order routes (accessible by both authenticated customers and guests with session ID)
Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/', [OrderController::class, 'store']);
    Route::post('/test', [\App\Http\Controllers\Api\OrderTestController::class, 'testStore']);
    Route::get('/{order}', [OrderController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| Public Routes - Read-only access for everyone
|--------------------------------------------------------------------------
*/
Route::apiResource('products', ProductController::class)->only(['index', 'show']);
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);

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
    
    // Cloudinary storage management
    Route::get('cloudinary/storage-usage', [CloudinaryController::class, 'getStorageUsage']);
    Route::post('cloudinary/cleanup', [CloudinaryController::class, 'cleanupStorage']);
    
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
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::put('/user', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    // User address management
    Route::prefix('user')->group(function () {
        Route::apiResource('addresses', AddressController::class);
        Route::patch('addresses/{address}/default', [AddressController::class, 'setDefault']);
    });
});
