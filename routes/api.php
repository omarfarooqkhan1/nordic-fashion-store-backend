<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;

/*
|--------------------------------------------------------------------------
| Public Routes - Read-only
|--------------------------------------------------------------------------
*/
Route::apiResource('products', ProductController::class)->only(['index', 'show']);
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);

/*
|--------------------------------------------------------------------------
| Protected Routes - Authenticated via Auth0 (for future CRUD)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // Placeholder for when CRUD is added
    Route::apiResource('products', ProductController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('categories', CategoryController::class)->only(['store', 'update', 'destroy']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});