<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    |
    | Intervention Image supports "GD Library" and "Imagick" to process images
    | internally. You may choose one of them according to your PHP
    | configuration. By default PHP's "GD Library" implementation is used.
    |
    | Supported: "gd", "imagick"
    |
    */

    'driver' => env('IMAGE_DRIVER', 'gd'),

    /*
    |--------------------------------------------------------------------------
    | Image Quality
    |--------------------------------------------------------------------------
    |
    | Default quality for image compression
    |
    */

    'quality' => env('IMAGE_QUALITY', 85),

    /*
    |--------------------------------------------------------------------------
    | Image Sizes
    |--------------------------------------------------------------------------
    |
    | Default image sizes for different use cases
    |
    */

    'sizes' => [
        'thumbnail' => [
            'width' => 300,
            'height' => 300,
        ],
        'medium' => [
            'width' => 600,
            'height' => 600,
        ],
        'large' => [
            'width' => 1200,
            'height' => 1200,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Formats
    |--------------------------------------------------------------------------
    |
    | Supported image formats
    |
    */

    'formats' => [
        'jpeg',
        'jpg',
        'png',
        'gif',
        'webp',
    ],
];

