<?php

use Illuminate\Support\Facades\Route;

// API routes are handled in api.php
// This file serves the React frontend

// CSRF cookie route for API calls
Route::get('/sanctum/csrf-cookie', function () {
    try {
        // Start session if not already started
        if (!session()->isStarted()) {
            session()->start();
        }
        
        return response()->json([
            'message' => 'CSRF cookie set', 
            'status' => 'success',
            'csrf_token' => csrf_token(),
            'timestamp' => now()
        ])->header('Access-Control-Allow-Origin', '*')
          ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
          ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN, X-XSRF-TOKEN')
          ->header('Access-Control-Allow-Credentials', 'true');
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error setting CSRF cookie',
            'error' => $e->getMessage(),
            'status' => 'error'
        ], 500)->header('Access-Control-Allow-Origin', '*')
          ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
          ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN, X-XSRF-TOKEN')
          ->header('Access-Control-Allow-Credentials', 'true');
    }
})->middleware(['web']);

// Health check route
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'message' => 'API is healthy'
    ])->header('Access-Control-Allow-Origin', '*')
      ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
      ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN, X-XSRF-TOKEN')
      ->header('Access-Control-Allow-Credentials', 'true');
});

// Serve storage files first (before catch-all route)
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    
    if (!file_exists($filePath)) {
        abort(404);
    }
    
    return response()->file($filePath);
})->where('path', '.*');

// Serve sample images for development/testing
Route::get('/sample_images/{filename}', function ($filename) {
    $path = storage_path('app/public/sample_images/' . $filename);
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    return response()->file($path);
})->where('filename', '.*');

// Serve real product images
Route::get('/images/{filename}', function ($filename) {
    $path = storage_path('app/public/images/' . $filename);
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    return response()->file($path);
})->where('filename', '.*');

// Include debug routes
include __DIR__.'/debug.php';

// Include cloudinary test routes (remove after testing)
include __DIR__.'/test-cloudinary.php';

// Serve React app for all other routes
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');