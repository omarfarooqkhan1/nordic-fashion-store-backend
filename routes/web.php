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