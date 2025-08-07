<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductCollection;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\Image;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'variants.images', 'images'])->get();
        return new ProductCollection($products);
    }

    public function show(Product $product)
    {
        $product->loadMissing(['category', 'variants.images', 'images']);
        return new ProductResource($product);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:products,name',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product = Product::create($validated);

        return new ProductResource($product);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:products,name,' . $product->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product->update($validated);

        return new ProductResource($product);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }

    /**
     * Bulk upload products from CSV (Admin only)
     */
    public function bulkUpload(Request $request)
    {
        $request->validate([
            'upload_file' => 'required|file|max:102400', // 100MB max
            'update_existing' => 'sometimes|in:true,false,1,0',
        ]);

        try {
            $file = $request->file('upload_file');
            $updateExisting = filter_var($request->input('update_existing', false), FILTER_VALIDATE_BOOLEAN);
            
            // Debug logging
            Log::info('Bulk upload started', [
                'filename' => $file->getClientOriginalName(),
                'update_existing' => $updateExisting,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType()
            ]);

            // Check if it's a ZIP file or CSV
            $isZipFile = in_array($file->getMimeType(), ['application/zip', 'application/x-zip-compressed']);
            
            if ($isZipFile) {
                return $this->handleZipUpload($file, $updateExisting);
            } else {
                // Legacy CSV upload
                return $this->handleCsvUpload($file, $updateExisting);
            }

        } catch (\Exception $e) {
            Log::error('Bulk upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle ZIP file upload with CSV and images
     */
    private function handleZipUpload($file, bool $updateExisting)
    {
        $tempDir = storage_path('app/temp/' . uniqid());
        mkdir($tempDir, 0755, true);

        try {
            // Extract ZIP file
            $zip = new \ZipArchive();
            if ($zip->open($file->getRealPath()) !== TRUE) {
                throw new \Exception('Could not open ZIP file');
            }

            $zip->extractTo($tempDir);
            $zip->close();

            // Find CSV file in extracted contents
            $csvFile = null;
            $imageFiles = [];
            
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tempDir)
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $extension = strtolower($file->getExtension());
                    if (in_array($extension, ['csv', 'txt'])) {
                        $csvFile = $file->getRealPath();
                    } elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $imageFiles[basename($file->getFilename())] = $file->getRealPath();
                    }
                }
            }

            if (!$csvFile) {
                throw new \Exception('No CSV file found in ZIP archive');
            }

            // Process CSV with image file references
            return $this->processCsvWithImages($csvFile, $imageFiles, $updateExisting);

        } finally {
            // Clean up temp directory
            $this->deleteDirectory($tempDir);
        }
    }

    /**
     * Handle legacy CSV-only upload
     */
    private function handleCsvUpload($file, bool $updateExisting)
    {
        return $this->processCsvWithImages($file->getRealPath(), [], $updateExisting);
    }

    /**
     * Process CSV data with optional image files
     */
    private function processCsvWithImages(string $csvPath, array $imageFiles, bool $updateExisting)
    {
        $csvData = array_map('str_getcsv', file($csvPath));
        $header = array_shift($csvData);
        
        // Validate CSV headers
        $requiredHeaders = ['name', 'description', 'price', 'category_name'];
        $imageHeaders = ['image_file_1', 'image_file_2', 'image_file_3', 'image_file_4', 'image_file_5'];
        $urlHeaders = ['image_url_1', 'image_url_2', 'image_url_3', 'image_url_4', 'image_url_5'];
        $optionalHeaders = ['sku', 'color', 'size', 'price_difference', 'stock'];
        $allValidHeaders = array_merge($requiredHeaders, $optionalHeaders, $imageHeaders, $urlHeaders);
        
        $missingHeaders = array_diff($requiredHeaders, $header);
        $invalidHeaders = array_diff($header, $allValidHeaders);
        
        if (!empty($missingHeaders)) {
            return response()->json([
                'message' => 'CSV missing required headers: ' . implode(', ', $missingHeaders),
                'required_headers' => $requiredHeaders,
                'optional_headers' => array_merge($optionalHeaders, $imageHeaders, $urlHeaders),
                'found_headers' => $header
            ], 422);
        }
        
        if (!empty($invalidHeaders)) {
            return response()->json([
                'message' => 'CSV contains invalid headers: ' . implode(', ', $invalidHeaders),
                'valid_headers' => $allValidHeaders,
                'found_headers' => $header
            ], 422);
        }

        $results = [
            'total_rows' => count($csvData),
            'successful' => 0,
            'failed' => 0,
            'errors' => []
        ];

        $processedProducts = []; // Track processed products by name
        
        DB::beginTransaction();

        foreach ($csvData as $index => $row) {
            try {
                $rowData = array_combine($header, $row);
                $rowNumber = $index + 2;

                // Find category
                $category = Category::where('name', $rowData['category_name'])->first();
                if (!$category) {
                    throw new \Exception("Category '{$rowData['category_name']}' not found");
                }

                $productName = $rowData['name'];
                $product = null;

                // Check if we've already processed this product in this upload
                if (isset($processedProducts[$productName])) {
                    $product = $processedProducts[$productName];
                } else {
                    // First time seeing this product name
                    $productData = [
                        'name' => $productName,
                        'description' => $rowData['description'] ?? null,
                        'price' => (float) $rowData['price'],
                        'category_id' => $category->id,
                    ];

                    $existingProduct = Product::where('name', $productName)->first();

                    if ($existingProduct) {
                        if ($updateExisting) {
                            $existingProduct->update($productData);
                            $product = $existingProduct;
                        } else {
                            throw new \Exception("Product '{$productName}' already exists");
                        }
                    } else {
                        $product = Product::create($productData);
                    }

                    $processedProducts[$productName] = $product;
                }

                // Handle variant data if provided
                $hasVariantData = isset($rowData['sku']) || isset($rowData['color']) || isset($rowData['size']);
                
                if ($hasVariantData && $product) {
                    $variantData = [
                        'product_id' => $product->id,
                        'sku' => $rowData['sku'] ?? ($product->name . '-' . uniqid()),
                        'color' => $rowData['color'] ?? null,
                        'size' => $rowData['size'] ?? null,
                        'price_difference' => isset($rowData['price_difference']) ? (float) $rowData['price_difference'] : 0.00,
                        'stock' => isset($rowData['stock']) ? (int) $rowData['stock'] : 0,
                    ];

                    // Check if variant already exists
                    $existingVariant = ProductVariant::where([
                        'product_id' => $product->id,
                        'sku' => $variantData['sku']
                    ])->first();

                    if ($existingVariant) {
                        if ($updateExisting) {
                            $existingVariant->update($variantData);
                            Log::info("Updated variant: {$variantData['sku']} for product: {$product->name}");
                        } else {
                            throw new \Exception("Variant with SKU '{$variantData['sku']}' already exists");
                        }
                    } else {
                        ProductVariant::create($variantData);
                        Log::info("Created variant: {$variantData['sku']} for product: {$product->name}");
                    }
                }

                // Handle product images if provided (only process once per product)
                if ($product && !isset($processedProducts[$productName . '_images_processed'])) {
                    $this->processProductImagesWithFiles($product, $rowData, $imageFiles, $updateExisting);
                    $processedProducts[$productName . '_images_processed'] = true;
                }

                $results['successful']++;
                Log::info("Successfully processed row {$rowNumber}: {$productName}");

            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'row' => $rowNumber,
                    'data' => $rowData ?? $row,
                    'error' => $e->getMessage()
                ];
            }
        }

        DB::commit();

        Log::info('Bulk upload completed', [
            'unique_products' => count($processedProducts),
            'total_rows_processed' => $results['successful'],
            'failed_rows' => $results['failed']
        ]);

        $results['unique_products'] = count(array_filter($processedProducts, function($key) {
            return !str_ends_with($key, '_images_processed');
        }, ARRAY_FILTER_USE_KEY));

        return response()->json($results);
    }

    /**
     * Process product images from CSV data with file uploads
     */
    private function processProductImagesWithFiles(Product $product, array $rowData, array $imageFiles, bool $updateExisting): void
    {
        $cloudinaryService = app(\App\Services\CloudinaryService::class);
        
        // Check storage usage before uploading
        $storageUsage = $cloudinaryService->getStorageUsage();
        if ($storageUsage && $storageUsage['percentage_used'] > 90) {
            Log::warning('Cloudinary storage nearly full', [
                'usage_percentage' => $storageUsage['percentage_used'],
                'used_mb' => $storageUsage['used_mb']
            ]);
            
            // Attempt cleanup if storage is getting full
            $cleanupResult = $cloudinaryService->cleanupOldImages(60); // Clean images older than 60 days
            Log::info('Cleanup performed due to storage limit', $cleanupResult);
        }
        
        // Handle file-based images first
        $imageFileColumns = ['image_file_1', 'image_file_2', 'image_file_3', 'image_file_4', 'image_file_5'];
        $uploadedImageUrls = [];

        foreach ($imageFileColumns as $column) {
            if (!empty($rowData[$column]) && isset($imageFiles[$rowData[$column]])) {
                $filePath = $imageFiles[$rowData[$column]];
                
                // Generate unique public ID with product name and variant info
                $variantInfo = '';
                if (!empty($rowData['color'])) $variantInfo .= '_' . strtolower($rowData['color']);
                if (!empty($rowData['size'])) $variantInfo .= '_' . strtolower($rowData['size']);
                
                $publicId = preg_replace('/[^a-zA-Z0-9_-]/', '_', 
                    strtolower($product->name) . $variantInfo . '_' . pathinfo($rowData[$column], PATHINFO_FILENAME)
                );
                
                // Check file size and choose appropriate upload method
                $fileSizeBytes = filesize($filePath);
                $fileSizeMB = $fileSizeBytes / 1024 / 1024;
                
                if ($fileSizeMB > 5) {
                    // Use standard compression for large files
                    $result = $cloudinaryService->uploadImage($filePath, $publicId);
                } else {
                    // Use high quality for smaller files
                    $result = $cloudinaryService->uploadHighQualityImage($filePath, $publicId);
                }
                
                if ($result) {
                    $uploadedImageUrls[] = $result['secure_url'];
                    Log::info('Image uploaded successfully', [
                        'product' => $product->name,
                        'original_file' => $rowData[$column],
                        'public_id' => $result['public_id'],
                        'size_reduction' => isset($result['bytes']) ? 
                            round((1 - $result['bytes'] / $fileSizeBytes) * 100, 1) . '%' : 'unknown'
                    ]);
                } else {
                    Log::error('Failed to upload image', [
                        'product' => $product->name,
                        'file' => $rowData[$column]
                    ]);
                }
            }
        }

        // Handle URL-based images (fallback to legacy behavior)
        $imageUrlColumns = ['image_url_1', 'image_url_2', 'image_url_3', 'image_url_4', 'image_url_5'];
        $imageUrls = [];

        foreach ($imageUrlColumns as $column) {
            if (!empty($rowData[$column]) && filter_var($rowData[$column], FILTER_VALIDATE_URL)) {
                $imageUrls[] = $rowData[$column];
            }
        }

        // Combine uploaded and URL images
        $allImageUrls = array_merge($uploadedImageUrls, $imageUrls);

        if (empty($allImageUrls)) {
            return; // No valid images provided
        }

        // If updating existing product, remove old images
        if ($updateExisting) {
            $product->images()->delete();
        }

        // Create new images
        foreach ($allImageUrls as $index => $imageUrl) {
            $altText = $product->name . ' - Image ' . ($index + 1);
            
            // Check if image already exists for this product
            $existingImage = $product->images()->where('url', $imageUrl)->first();
            
            if (!$existingImage) {
                $product->images()->create([
                    'url' => $imageUrl,
                    'alt_text' => $altText,
                    'sort_order' => $index + 1,
                ]);
            }
        }
    }

    /**
     * Recursively delete a directory
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    /**
     * Download CSV template for bulk upload
     */
    public function getBulkUploadTemplate()
    {
        $headers = [
            'name', 'description', 'price', 'category_name', 'sku', 'color', 'size', 'price_difference', 'stock', 
            'image_file_1', 'image_file_2', 'image_file_3', 'image_file_4', 'image_file_5',
            'image_url_1', 'image_url_2', 'image_url_3', 'image_url_4', 'image_url_5'
        ];
        $sampleData = [
            [
                'Nordic Wool Sweater', 
                'Warm and cozy wool sweater perfect for Nordic winters', 
                '89.99', 
                'Clothing', 
                'NWS-RED-M', 
                'Red', 
                'M', 
                '0.00', 
                '50',
                'nordic-sweater-red-front.jpg',  // Image file name
                'nordic-sweater-red-back.jpg',   // Image file name
                '',
                '',
                '',
                '', // Or use image URLs if no files
                '', 
                '',
                '',
                ''
            ],
            [
                'Nordic Wool Sweater', 
                'Warm and cozy wool sweater perfect for Nordic winters', 
                '89.99', 
                'Clothing', 
                'NWS-BLUE-L', 
                'Blue', 
                'L', 
                '5.00', 
                '30',
                'nordic-sweater-blue-front.jpg',
                'nordic-sweater-blue-back.jpg',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ],
            [
                'Winter Boots', 
                'Premium winter boots for harsh weather', 
                '149.99', 
                'Footwear', 
                'WB-BROWN-42', 
                'Brown', 
                '42', 
                '0.00', 
                '25',
                'winter-boots-brown-side.jpg',
                'winter-boots-brown-sole.jpg',
                'winter-boots-brown-detail.jpg',
                'winter-boots-brown-top.jpg',
                '',
                '',
                '',
                '',
                '',
                ''
            ]
        ];

        $csvContent = implode(',', $headers) . "\n";
        foreach ($sampleData as $row) {
            $csvContent .= '"' . implode('","', $row) . '"' . "\n";
        }

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="product_upload_template.csv"');
    }

    /**
     * Upload an image for a product
     */
    public function uploadImage(Request $request, Product $product)
    {
        $request->validate([
            'image' => 'required|image|max:10240', // 10MB max
            'alt_text' => 'nullable|string|max:255',
        ]);

        try {
            $imageFile = $request->file('image');
            $cloudinaryService = app(\App\Services\CloudinaryService::class);
            
            // Generate public ID
            $publicId = preg_replace('/[^a-zA-Z0-9_-]/', '_', 
                strtolower($product->name) . '_' . time() . '_' . uniqid()
            );
            
            // Upload to Cloudinary
            $result = $cloudinaryService->uploadImage($imageFile->getRealPath(), $publicId);
            
            if (!$result) {
                throw new \Exception('Failed to upload image to Cloudinary');
            }

            // Get the next sort order
            $maxSortOrder = $product->images()->max('sort_order') ?? 0;
            
            // Create image record
            $image = $product->images()->create([
                'url' => $result['secure_url'],
                'alt_text' => $request->alt_text ?? $product->name . ' Image',
                'sort_order' => $maxSortOrder + 1,
            ]);

            Log::info('Image uploaded successfully', [
                'product_id' => $product->id,
                'image_id' => $image->id,
                'cloudinary_public_id' => $result['public_id']
            ]);

            return response()->json($image, 201);

        } catch (\Exception $e) {
            Log::error('Image upload failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a product image with enhanced safety checks
     */
    public function deleteImage(Product $product, Image $image)
    {
        try {
            // Verify the image belongs to this product or its variants
            $belongsToProduct = $image->imageable_id === $product->id && $image->imageable_type === Product::class;
            $belongsToVariant = false;
            $variantInfo = null;

            if (!$belongsToProduct) {
                // Check if image belongs to any variant of this product
                $variant = ProductVariant::where('product_id', $product->id)
                    ->where('id', $image->imageable_id)
                    ->where(function() use ($image) {
                        return $image->imageable_type === ProductVariant::class;
                    })
                    ->first();
                
                if ($variant) {
                    $belongsToVariant = true;
                    $variantInfo = [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'color' => $variant->color,
                        'size' => $variant->size
                    ];
                }
            }

            if (!$belongsToProduct && !$belongsToVariant) {
                return response()->json(['message' => 'Image not found for this product or its variants'], 404);
            }

            // Delete from Cloudinary (optional - extract public_id from URL)
            $cloudinaryService = app(\App\Services\CloudinaryService::class);
            
            // Extract public ID from Cloudinary URL
            $publicId = $this->extractPublicIdFromUrl($image->url);
            if ($publicId) {
                $cloudinaryService->deleteImage($publicId);
            }

            // Delete from database
            $image->delete();

            $logData = [
                'product_id' => $product->id,
                'image_id' => $image->id,
                'belongs_to_product' => $belongsToProduct,
                'belongs_to_variant' => $belongsToVariant
            ];

            if ($variantInfo) {
                $logData['variant_info'] = $variantInfo;
            }

            Log::info('Image deleted successfully', $logData);

            $response = ['message' => 'Image deleted successfully'];
            if ($belongsToVariant && $variantInfo) {
                $response['variant_info'] = $variantInfo;
                $response['warning'] = 'This image belonged to a specific product variant';
            }

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Image deletion failed', [
                'product_id' => $product->id,
                'image_id' => $image->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to delete image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get categorized images for a product (product images and variant images)
     */
    public function getCategorizedImages(Product $product)
    {
        try {
            // Get product-level images
            $productImages = $product->images()->orderBy('sort_order')->get()->map(function($image) {
                return [
                    'id' => $image->id,
                    'url' => $image->url,
                    'alt_text' => $image->alt_text,
                    'sort_order' => $image->sort_order,
                    'type' => 'product',
                    'belongs_to' => 'Product',
                    'category' => 'General Product Image'
                ];
            });

            // Get variant-specific images
            $variantImages = collect();
            foreach ($product->variants as $variant) {
                $images = $variant->images()->orderBy('sort_order')->get();
                foreach ($images as $image) {
                    $variantImages->push([
                        'id' => $image->id,
                        'url' => $image->url,
                        'alt_text' => $image->alt_text,
                        'sort_order' => $image->sort_order,
                        'type' => 'variant',
                        'belongs_to' => 'ProductVariant',
                        'variant_id' => $variant->id,
                        'variant_sku' => $variant->sku,
                        'variant_info' => [
                            'color' => $variant->color,
                            'size' => $variant->size,
                            'sku' => $variant->sku
                        ],
                        'category' => "Variant: {$variant->color} ({$variant->size})"
                    ]);
                }
            }

            return response()->json([
                'product_images' => $productImages,
                'variant_images' => $variantImages,
                'total_images' => $productImages->count() + $variantImages->count(),
                'summary' => [
                    'product_image_count' => $productImages->count(),
                    'variant_image_count' => $variantImages->count(),
                    'variants_with_images' => $product->variants()->whereHas('images')->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get categorized images', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to get images: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder product images
     */
    public function reorderImages(Request $request, Product $product)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*.id' => 'required|exists:images,id',
            'images.*.sort_order' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->images as $imageData) {
                $image = Image::find($imageData['id']);
                
                // Verify the image belongs to this product
                if ($image->imageable_id !== $product->id || $image->imageable_type !== Product::class) {
                    throw new \Exception("Image {$imageData['id']} does not belong to this product");
                }

                $image->update(['sort_order' => $imageData['sort_order']]);
            }

            DB::commit();

            // Return updated images
            $updatedImages = $product->images()->orderBy('sort_order')->get();

            Log::info('Images reordered successfully', [
                'product_id' => $product->id,
                'image_count' => count($request->images)
            ]);

            return response()->json($updatedImages);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Image reordering failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to reorder images: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract public ID from Cloudinary URL
     */
    private function extractPublicIdFromUrl(string $url): ?string
    {
        // Extract public ID from Cloudinary URL
        // Example URL: https://res.cloudinary.com/cloud-name/image/upload/v1234567890/folder/public_id.jpg
        $pattern = '/\/v\d+\/(.+)\./';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Create a new variant for a product
     */
    public function storeVariant(Request $request, Product $product)
    {
        $validated = $request->validate([
            'size' => 'required|string|max:50',
            'color' => 'required|string|max:50',
            'sku' => 'nullable|string|max:100|unique:product_variants,sku',
            'actual_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        // Generate SKU if not provided or empty
        if (empty($validated['sku'])) {
            $validated['sku'] = $product->name . '-' . $validated['size'] . '-' . $validated['color'];
            $validated['sku'] = str_replace(' ', '-', strtolower($validated['sku']));
        }

        $validated['product_id'] = $product->id;

        // Check if variant with same size and color already exists
        $existingVariant = ProductVariant::where([
            'product_id' => $product->id,
            'size' => $validated['size'],
            'color' => $validated['color']
        ])->first();

        if ($existingVariant) {
            return response()->json([
                'message' => 'A variant with this size and color already exists for this product.'
            ], 422);
        }

        $variant = ProductVariant::create($validated);

        return response()->json([
            'message' => 'Variant created successfully',
            'variant' => $variant
        ], 201);
    }

    /**
     * Update a variant
     */
    public function updateVariant(Request $request, Product $product, ProductVariant $variant)
    {
        // Check if variant belongs to product
        if ($variant->product_id !== $product->id) {
            return response()->json(['message' => 'Variant does not belong to this product'], 404);
        }

        $validated = $request->validate([
            'size' => 'required|string|max:50',
            'color' => 'required|string|max:50',
            'sku' => 'nullable|string|max:100|unique:product_variants,sku,' . $variant->id,
            'actual_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        // Check if variant with same size and color already exists (excluding current variant)
        $existingVariant = ProductVariant::where([
            'product_id' => $product->id,
            'size' => $validated['size'],
            'color' => $validated['color']
        ])->where('id', '!=', $variant->id)->first();

        if ($existingVariant) {
            return response()->json([
                'message' => 'A variant with this size and color already exists for this product.'
            ], 422);
        }

        $variant->update($validated);

        return response()->json([
            'message' => 'Variant updated successfully',
            'variant' => $variant
        ]);
    }

    /**
     * Delete a variant
     */
    public function destroyVariant(Product $product, ProductVariant $variant)
    {
        // Check if variant belongs to product
        if ($variant->product_id !== $product->id) {
            return response()->json(['message' => 'Variant does not belong to this product'], 404);
        }

        $variant->delete();

        return response()->json(['message' => 'Variant deleted successfully']);
    }

    /**
     * Delete a variant (standalone endpoint)
     */
    public function destroyVariantStandalone(ProductVariant $variant)
    {
        $variant->delete();

        return response()->json(['message' => 'Variant deleted successfully']);
    }
}