<?php

use Illuminate\Support\Facades\Route;
use App\Services\CloudinaryService;

// Test route for Cloudinary integration (remove after testing)
Route::get('/test-cloudinary', function () {
    try {
        $cloudinaryService = app(CloudinaryService::class);
        
        // Test basic connectivity
        $result = $cloudinaryService->getStorageUsage();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Cloudinary connection successful',
            'storage_info' => $result,
            'config_check' => [
                'cloud_name' => config('cloudinary.cloud_name') ? 'Set' : 'Missing',
                'api_key' => config('cloudinary.api_key') ? 'Set' : 'Missing',
                'api_secret' => config('cloudinary.api_secret') ? 'Set' : 'Missing',
            ]
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Cloudinary connection failed',
            'error' => $e->getMessage(),
            'config_check' => [
                'cloud_name' => config('cloudinary.cloud_name') ? 'Set' : 'Missing',
                'api_key' => config('cloudinary.api_key') ? 'Set' : 'Missing',
                'api_secret' => config('cloudinary.api_secret') ? 'Set' : 'Missing',
            ]
        ], 500);
    }
});
