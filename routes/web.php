<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Include debug routes
include __DIR__.'/debug.php';

// Include cloudinary test routes (remove after testing)
include __DIR__.'/test-cloudinary.php';

Route::get('/welcome', function () {
    return view('welcome');
});

// Serve sample images for development/testing
Route::get('/sample_images/{filename}', function ($filename) {
    $path = base_path('sample_images/' . $filename);
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    return response()->file($path);
})->where('filename', '.*');