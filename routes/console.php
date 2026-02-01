<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule Cloudinary storage monitoring and cleanup
Schedule::command('cloudinary:manage usage')
    ->daily()
    ->withoutOverlapping();

Schedule::command('cloudinary:manage cleanup --days=90 --force')
    ->weekly()
    ->withoutOverlapping();

// Schedule expired guest cart cleanup
Schedule::command('cart:cleanup-expired-guest-carts --days=7')
    ->daily()
    ->withoutOverlapping();
