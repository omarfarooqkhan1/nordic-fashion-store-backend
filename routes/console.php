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
    ->withoutOverlapping()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Failed to check Cloudinary storage usage');
    });

Schedule::command('cloudinary:manage cleanup --days=90 --force')
    ->weekly()
    ->withoutOverlapping()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Failed to cleanup Cloudinary storage');
    });
