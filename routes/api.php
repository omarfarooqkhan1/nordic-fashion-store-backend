<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Publicly accessible Product Endpoints
Route::apiResource('products', ProductController::class)->only(['index', 'show']);

// Publicly accessible Category Endpoints
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);

// --- Auth0 Protected Routes will go here ---