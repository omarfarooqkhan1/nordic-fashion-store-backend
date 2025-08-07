<?php

function createVariantImage($width, $height, $color, $productName, $viewType, $filename) {
    $image = imagecreatetruecolor($width, $height);
    
    // Define colors
    $colors = [
        'red' => [255, 50, 50],
        'blue' => [50, 100, 255],
        'black' => [30, 30, 30],
        'white' => [245, 245, 245],
        'green' => [50, 180, 50],
        'brown' => [139, 69, 19],
        'navy' => [0, 0, 128],
        'gray' => [128, 128, 128]
    ];
    
    $colorRGB = $colors[strtolower($color)] ?? [128, 128, 128];
    $bgColor = imagecolorallocate($image, $colorRGB[0], $colorRGB[1], $colorRGB[2]);
    
    // Fill background
    imagefill($image, 0, 0, $bgColor);
    
    // Add some texture/pattern for realism
    $textColor = imagecolorallocate($image, 255, 255, 255);
    if (strtolower($color) === 'white' || strtolower($color) === 'gray') {
        $textColor = imagecolorallocate($image, 0, 0, 0);
    }
    
    // Add product name
    $font_size = 5;
    $text = strtoupper($productName);
    $text_width = imagefontwidth($font_size) * strlen($text);
    $text_x = ($width - $text_width) / 2;
    imagestring($image, $font_size, $text_x, 20, $text, $textColor);
    
    // Add color name
    $color_text = strtoupper($color) . " - " . strtoupper($viewType);
    $color_width = imagefontwidth(4) * strlen($color_text);
    $color_x = ($width - $color_width) / 2;
    imagestring($image, 4, $color_x, $height/2 - 20, $color_text, $textColor);
    
    // Add view type
    $view_text = $viewType === 'front' ? 'FRONT VIEW' : ($viewType === 'back' ? 'BACK VIEW' : 'SIDE VIEW');
    $view_width = imagefontwidth(3) * strlen($view_text);
    $view_x = ($width - $view_width) / 2;
    imagestring($image, 3, $view_x, $height/2 + 20, $view_text, $textColor);
    
    // Add some decorative elements
    if ($viewType === 'front') {
        // Add buttons or front details
        $buttonColor = imagecolorallocate($image, min(255, $colorRGB[0] + 50), min(255, $colorRGB[1] + 50), min(255, $colorRGB[2] + 50));
        imagefilledellipse($image, $width/2 - 20, $height/2 + 60, 10, 10, $buttonColor);
        imagefilledellipse($image, $width/2, $height/2 + 60, 10, 10, $buttonColor);
        imagefilledellipse($image, $width/2 + 20, $height/2 + 60, 10, 10, $buttonColor);
    } elseif ($viewType === 'back') {
        // Add back details
        $detailColor = imagecolorallocate($image, max(0, $colorRGB[0] - 30), max(0, $colorRGB[1] - 30), max(0, $colorRGB[2] - 30));
        imagerectangle($image, $width/2 - 30, $height/2 + 50, $width/2 + 30, $height/2 + 80, $detailColor);
    }
    
    // Save image
    imagejpeg($image, $filename, 90);
    imagedestroy($image);
    
    echo "Created: $filename\n";
}

// Create directory for images
$imageDir = __DIR__ . '/variant_product_images';
if (!is_dir($imageDir)) {
    mkdir($imageDir, 0755, true);
}

// Product variants with colors
$products = [
    [
        'name' => 'Nordic Wool Sweater',
        'colors' => ['red', 'blue', 'black', 'white'],
        'sizes' => ['S', 'M', 'L', 'XL']
    ],
    [
        'name' => 'Scandinavian Jacket',
        'colors' => ['black', 'navy', 'brown'],
        'sizes' => ['M', 'L', 'XL']
    ],
    [
        'name' => 'Arctic Boots',
        'colors' => ['black', 'brown'],
        'sizes' => ['38', '39', '40', '41', '42', '43']
    ],
    [
        'name' => 'Nordic Scarf',
        'colors' => ['red', 'blue', 'gray', 'white'],
        'sizes' => ['One Size']
    ],
    [
        'name' => 'Winter Gloves',
        'colors' => ['black', 'brown', 'gray'],
        'sizes' => ['S', 'M', 'L']
    ]
];

// Generate images for each product variant
foreach ($products as $product) {
    foreach ($product['colors'] as $color) {
        // Create front and back view for each color
        $productSlug = strtolower(str_replace(' ', '-', $product['name']));
        
        // Front view
        $frontFilename = $imageDir . '/' . $productSlug . '-' . $color . '-front.jpg';
        createVariantImage(800, 600, $color, $product['name'], 'front', $frontFilename);
        
        // Back view
        $backFilename = $imageDir . '/' . $productSlug . '-' . $color . '-back.jpg';
        createVariantImage(800, 600, $color, $product['name'], 'back', $backFilename);
    }
}

echo "\nAll variant images created successfully!\n";
echo "Images saved in: $imageDir\n";

?>
