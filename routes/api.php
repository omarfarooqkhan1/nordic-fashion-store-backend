<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\AdminUserController;

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

// Cart routes (authenticated customers only)
Route::middleware(['auth'])->prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index']);
    Route::post('/', [CartController::class, 'store']);
    Route::put('/{item}', [CartController::class, 'update']);
    Route::delete('/{item}', [CartController::class, 'destroy']);
    Route::delete('/', [CartController::class, 'clear']);
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
Route::middleware(['auth', 'admin'])->group(function () {
    // Product management (admin only)
    Route::apiResource('products', ProductController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('categories', CategoryController::class)->only(['store', 'update', 'destroy']);
    
    // Bulk upload routes
    Route::post('products/bulk-upload', [ProductController::class, 'bulkUpload']);
    Route::get('products/bulk-upload/template', [ProductController::class, 'getBulkUploadTemplate']);
    
    // User management routes
    Route::get('admin/users', [AdminUserController::class, 'index']);
    Route::post('admin/users', [AdminUserController::class, 'store']);
    Route::put('admin/users/{user}/role', [AdminUserController::class, 'updateRole']);
    Route::delete('admin/users/{user}', [AdminUserController::class, 'destroy']);
    
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
Route::middleware(['auth'])->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
});
