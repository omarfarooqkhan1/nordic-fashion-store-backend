<?php

return [
    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
    'api_key' => env('CLOUDINARY_API_KEY'),
    'api_secret' => env('CLOUDINARY_API_SECRET'),
    'secure' => true,
    
    // Upload settings
    'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET', null),
    'folder' => env('CLOUDINARY_FOLDER', 'nordic-skin'),
    
    // Transformation settings
    'auto_optimize' => true,
    'auto_format' => true,
    
    // Storage management
    'max_file_size' => 10485760, // 10MB in bytes
    'allowed_formats' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
];
