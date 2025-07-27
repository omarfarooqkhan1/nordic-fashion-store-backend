<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Include debug routes
include __DIR__.'/debug.php';

Route::get('/welcome', function () {
    return view('welcome');
});