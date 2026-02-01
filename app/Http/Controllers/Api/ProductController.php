<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductVariantResource;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\Image;
use App\Services\LocalImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'variants.images', 'allImages']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by price range (using variant prices)
        if ($request->has('min_price') && $request->min_price) {
            $query->whereHas('variants', function($q) use ($request) {
                $q->where('price', '>=', $request->min_price);
            });
        }
        if ($request->has('max_price') && $request->max_price) {
            $query->whereHas('variants', function($q) use ($request) {
                $q->where('price', '<=', $request->max_price);
            });
        }

        // Filter by gender (include unisex products for male/female filters)
        if ($request->has('gender') && $request->gender) {
            $gender = $request->gender;
            if ($gender === 'male' || $gender === 'female') {
                // Include both the selected gender and unisex products
                $query->whereIn('gender', [$gender, 'unisex']);
            } else {
                // For 'unisex' filter, show only unisex products
                $query->where('gender', $gender);
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['name', 'price', 'created_at'])) {
            if ($sortBy === 'price') {
                // Sort by minimum variant price using subquery
                // Products without variants will have NULL min_price and appear at the end
                $query->addSelect([
                    'min_price' => \App\Models\ProductVariant::selectRaw('MIN(price)')
                        ->whereColumn('product_variants.product_id', 'products.id')
                ])
                ->orderByRaw("min_price IS NULL, min_price {$sortOrder}");
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        }

        // Pagination
        $perPage = $request->get('per_page', 12);
        $products = $query->paginate($perPage);
return response()->json([
            'data' => new ProductCollection($products),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'has_more_pages' => $products->hasMorePages(),
            ]
        ]);
    }

public function show(Product $product)
    {
        // Support include parameter for loading specific relationships
        $includes = request()->input('include');
        
        if ($includes) {
            $includeArray = is_array($includes) ? $includes : explode(',', $includes);
            $product->loadMissing($includeArray);
        } else {
            // Default relationships to load
            $product->loadMissing(['category', 'variants.images', 'allImages']);
        }
        
        return new ProductResource($product);
    }

public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:products,name',
            'description' => 'nullable|string',
            'size_guide_image' => 'nullable|string',
            'gender' => 'required|in:male,female,unisex',
            'category_id' => 'required|exists:categories,id',
            'variants' => 'nullable|array',
            'variants.*.color' => 'required_with:variants|string',
            'variants.*.size' => 'required_with:variants|string',
            
            // add other variant fields as needed
        ]);

        // Create product first
        $product = Product::create($validated);

        // If variants are present, create them
        if (!empty($validated['variants'])) {
            foreach ($validated['variants'] as $variantData) {
                $product->variants()->create($variantData);
            }
        }

        // Reload with relationships for response
        $product->load(['category', 'variants.images', 'allImages']);

        return new ProductResource($product);
    }

public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:products,name,' . $product->id,
            'description' => 'nullable|string',
            'size_guide_image' => 'nullable|string',
            'gender' => 'required|in:male,female,unisex',
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
     * Bulk upload products from CSV (Admin only) - Temporarily commented out
     */
    /*
    public function bulkUpload(Request $request)
    {
        $request->validate([
            'upload_file' => 'required|file|max:102400', // 100MB max
            'update_existing' => 'sometimes|in:true,false,1,0',
        ]);

        try {
            $file = $request->file('upload_file');
            $updateExisting = filter_var($request->input('update_existing', false), FILTER_VALIDATE_BOOLEAN);

            // Debug logging// Check if it's a ZIP file or CSV
            $isZipFile = in_array($file->getMimeType(), ['application/zip', 'application/x-zip-compressed']);

            if ($isZipFile) {
                return $this->handleZipUpload($file, $updateExisting);
            } else {
                // Legacy CSV upload
                return $this->handleCsvUpload($file, $updateExisting);
            }

        } catch (\Exception $e) { return response()->json([
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
    */

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
        $optionalHeaders = ['sku', 'color', 'size', 'price', 'stock'];
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
                        'price' => isset($rowData['price']) ? (float) $rowData['price'] : 0.00,
                        
                        'video_url' => $rowData['video_url'] ?? null,
                    ];

                    // Check if variant already exists
                    $existingVariant = ProductVariant::where([
                        'product_id' => $product->id,
                        'sku' => $variantData['sku']
                    ])->first();

                    if ($existingVariant) {
                        if ($updateExisting) {
                            $existingVariant->update($variantData);} else {
                            throw new \Exception("Variant with SKU '{$variantData['sku']}' already exists");
                        }
                    } else {
                        ProductVariant::create($variantData);}
                }

                // Handle product images if provided (only process once per product)
                if ($product && !isset($processedProducts[$productName . '_images_processed'])) {
                    $this->processProductImagesWithFiles($product, $rowData, $imageFiles, $updateExisting);
                    $processedProducts[$productName . '_images_processed'] = true;
                }

                $results['successful']++;} catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'row' => $rowNumber,
                    'data' => $rowData ?? $row,
                    'error' => $e->getMessage()
                ];
            }
        }

        DB::commit();$results['unique_products'] = count(array_filter($processedProducts, function($key) {
            return !str_ends_with($key, '_images_processed');
        }, ARRAY_FILTER_USE_KEY));
return response()->json($results);
    }

    /**
     * Process product images from CSV data with file uploads
     */
    private function processProductImagesWithFiles(Product $product, array $rowData, array $imageFiles, bool $updateExisting): void
    {
        $localImageService = app(\App\Services\LocalImageService::class);
        
        // Check storage usage before uploading
        $storageUsage = $localImageService->getStorageUsage();
        if ($storageUsage && $storageUsage['total_size_gb'] > 5) { // 5GB limit for local storage
            // Attempt cleanup if storage is getting full
            $cleanupResult = $localImageService->cleanupOldImages(60); // Clean images older than 60 days
        }
        
        // Handle file-based images first
        $imageFileColumns = ['image_file_1', 'image_file_2', 'image_file_3', 'image_file_4', 'image_file_5'];
        $uploadedImageUrls = [];

        foreach ($imageFileColumns as $column) {
            if (!empty($rowData[$column]) && isset($imageFiles[$rowData[$column]])) {
                $filePath = $imageFiles[$rowData[$column]];
                
                // Upload to local storage (let service generate filename with proper extension)
                $result = $localImageService->uploadImage($filePath, 'products');
                
                if ($result) {
                    $uploadedImageUrls[] = $result['secure_url'];
                } else {
                    // Handle upload failure silently
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
            'name', 'description', 'price', 'category_name', 'sku', 'color', 'size', 'price', 'stock', 
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
        // Accept both single and multiple image upload
        $isMultiple = is_array($request->file('image'));
        $imageFiles = $isMultiple ? $request->file('image') : [$request->file('image')];
        $responses = [];
        $localImageService = app(\App\Services\LocalImageService::class);
        foreach ($imageFiles as $imageFile) {
            // Validate each image
            $validator = \Validator::make([
                'image' => $imageFile
            ], [
                'image' => 'required|image|max:10240',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
            try {
                // Upload to local storage
                $result = $localImageService->uploadImage($imageFile);
                if (!$result) {
                    throw new \Exception('Failed to upload image to local storage');
                }
                $imageType = $request->input('image_type', 'main');
                $isMobile = $request->input('is_mobile', false);
                // If image_type is 'mobile', set to 'detailed' and is_mobile true
                if ($imageType === 'mobile') {
                    $imageType = 'detailed';
                    $isMobile = true;
                }
                $imageable = $product;
                if ($request->filled('variant_id')) {
                    $variant = ProductVariant::find($request->input('variant_id'));
                    if ($variant && $variant->product_id === $product->id) {
                        $imageable = $variant;
                    }
                }
                // If this is a size guide image, only allow one (backward compatible)
                if ($imageType === 'size_guide' && $imageable instanceof Product) {
                    $existingSizeGuideImages = $imageable->images()->where('image_type', 'size_guide')->get();
                    foreach ($existingSizeGuideImages as $existingImage) {
                        $localPath = $this->extractLocalPathFromUrl($existingImage->url);
                        if ($localPath) {
                            $localImageService->deleteImage($localPath);
                        }
                        $existingImage->delete();
                    }
                }
                $maxSortOrder = $imageable->images()->where('image_type', $imageType)->max('sort_order') ?? 0;
                $image = $imageable->images()->create([
                    'url' => $result['secure_url'],
                    'alt_text' => $request->alt_text ?? ($imageable instanceof Product ? $product->name : $imageable->color . ' ' . $imageable->size) . ' Image',
                    'sort_order' => $maxSortOrder + 1,
                    'image_type' => $imageType,
                    'is_mobile' => $isMobile,
                ]);$responses[] = $image;
            } catch (\Exception $e) { return response()->json([
                    'message' => 'Failed to upload image: ' . $e->getMessage()
                ], 500);
            }
        }
        // If only one image, return as object for backward compatibility
        if (count($responses) === 1) {
            return response()->json($responses[0], 201);
        }
return response()->json($responses, 201);
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

            // Delete from local storage
            $localImageService = app(\App\Services\LocalImageService::class);
            
            // Extract local path from URL
            $localPath = $this->extractLocalPathFromUrl($image->url);
            if ($localPath) {
                $localImageService->deleteImage($localPath);
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
            }$response = ['message' => 'Image deleted successfully'];
            if ($belongsToVariant && $variantInfo) {
                $response['variant_info'] = $variantInfo;
                $response['warning'] = 'This image belonged to a specific product variant';
            }
return response()->json($response);

        } catch (\Exception $e) { return response()->json([
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
            // Get main product images
            $mainImages = $product->images()->orderBy('sort_order')->get()->map(function($image) {
                return [
                    'id' => $image->id,
                    'url' => $image->url,
                    'alt_text' => $image->alt_text,
                    'sort_order' => $image->sort_order,
                    'image_type' => $image->image_type ?? 'main',
                    'type' => 'product',
                    'belongs_to' => 'Product',
                    'category' => 'Main Product Image'
                ];
            });
            
            // Get detailed images
            $detailedImages = $product->detailedImages()->orderBy('sort_order')->get()->map(function($image) {
                return [
                    'id' => $image->id,
                    'url' => $image->url,
                    'alt_text' => $image->alt_text,
                    'sort_order' => $image->sort_order,
                    'image_type' => $image->image_type ?? 'detailed',
                    'is_mobile' => $image->is_mobile ?? false,
                    'type' => 'product',
                    'belongs_to' => 'Product',
                    'category' => 'Detailed Product Image'
                ];
            });
            
            // Get styling images
            $stylingImages = $product->stylingImages()->orderBy('sort_order')->get()->map(function($image) {
                return [
                    'id' => $image->id,
                    'url' => $image->url,
                    'alt_text' => $image->alt_text,
                    'sort_order' => $image->sort_order,
                    'image_type' => $image->image_type ?? 'styling',
                    'type' => 'product',
                    'belongs_to' => 'Product',
                    'category' => 'Styling Inspiration Image'
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
                        'image_type' => $image->image_type ?? 'main',
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

            $totalImages = $mainImages->count() + $detailedImages->count() + $stylingImages->count() + $variantImages->count();
return response()->json([
                'main_images' => $mainImages,
                'detailed_images' => $detailedImages,
                'styling_images' => $stylingImages,
                'variant_images' => $variantImages,
                'total_images' => $totalImages,
                'summary' => [
                    'main_image_count' => $mainImages->count(),
                    'detailed_image_count' => $detailedImages->count(),
                    'styling_image_count' => $stylingImages->count(),
                    'variant_image_count' => $variantImages->count(),
                    'variants_with_images' => $product->variants()->whereHas('images')->count()
                ]
            ]);

        } catch (\Exception $e) { return response()->json([
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
return response()->json($updatedImages);

        } catch (\Exception $e) {
            DB::rollBack();
return response()->json([
                'message' => 'Failed to reorder images: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract public ID from Cloudinary URL (legacy support)
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
     * Extract local path from local storage URL
     */
    private function extractLocalPathFromUrl(string $url): ?string
    {
        // Extract local path from URL
        // Handle both /storage/ and /images/ URL patterns
        $baseUrl = config('app.url');
        
        // Remove the base URL to get the path
        if (strpos($url, $baseUrl) === 0) {
            $path = str_replace($baseUrl, '', $url);
            
            // Handle /images/ pattern (maps to storage/app/public/images/)
            if (strpos($path, '/images/') === 0) {
                return 'images/' . basename($path);
            }
            
            // Handle /storage/ pattern
            if (strpos($path, '/storage/') === 0) {
                return str_replace('/storage/', '', $path);
            }
        }
        
        return null;
    }

    /**
     * Store a newly created variant in storage.
     */
    public function storeVariant(Request $request, Product $product)
    {
        $validated = $request->validate([
            'size' => 'required|string|max:50',
            'color' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'sku' => 'nullable|string|max:100|unique:product_variants',
            
            'temp_image_ids' => 'nullable|array',
            'temp_image_ids.*' => 'integer|exists:images,id',
        ]);

        // Generate SKU if not provided
        if (empty($validated['sku'])) {
            $validated['sku'] = $this->generateSKU($product, $validated['size'], $validated['color']);
        }

        $variant = $product->variants()->create($validated);

        // Handle image reassignment for new variants
        if ($request->has('temp_image_ids')) {
            $this->reassignTempImages($variant, $request->input('temp_image_ids'));
        }
return response()->json([
            'message' => 'Variant created successfully',
            'variant' => new ProductVariantResource($variant)
        ], 201);
    }

    /**
     * Generate a SKU for a product variant
     */
    private function generateSKU(Product $product, string $size, string $color): string
    {
        // Create a base SKU using the product name and variant attributes
        $base = Str::upper(Str::limit(Str::slug($product->name, ''), 10, '')); // Limit product name to 10 chars
        $sizeCode = Str::upper(Str::limit(Str::slug($size, ''), 3, '')); // Limit size to 3 chars
        $colorCode = Str::upper(Str::limit(Str::slug($color, ''), 3, '')); // Limit color to 3 chars
        
        // Generate a unique SKU by appending a random number if needed
        $sku = "{$base}-{$sizeCode}-{$colorCode}";
        
        // Ensure uniqueness
        $counter = 1;
        $originalSku = $sku;
        while (ProductVariant::where('sku', $sku)->exists()) {
            $sku = "{$originalSku}-" . str_pad((string)$counter, 2, '0', STR_PAD_LEFT);
            $counter++;
        }
        
        return $sku;
    }

    /**
     * Reassign temporary images to a newly created variant
     */
    private function reassignTempImages(ProductVariant $variant, array $tempImageIds)
    {
        try {
            // Find images that were uploaded temporarily and reassign them to the variant
            $images = Image::whereIn('id', $tempImageIds)->get();
            
            foreach ($images as $image) {
                // Update the imageable relationship to point to the variant
                $image->imageable_id = $variant->id;
                $image->imageable_type = ProductVariant::class;
                $image->save();}
        } catch (\Exception $e) {}
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
            'price' => 'required|numeric|min:0',
            'sku' => 'nullable|string|max:100|unique:product_variants,sku,' . $variant->id,
            
            'temp_image_ids' => 'nullable|array',
            'temp_image_ids.*' => 'integer|exists:images,id',
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

        // Load the images relationship for the response
        $variant->load('images');
return response()->json([
            'message' => 'Variant updated successfully',
            'variant' => new ProductVariantResource($variant)
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
    
    /**
     * Get product statistics for admin dashboard
     */
    public function getProductStats()
    {
        try {
            $stats = [
                'total_products' => Product::count(),
                'total_variants' => ProductVariant::count(),
                'products_by_category' => Category::withCount('products')->get(),
                'low_stock_products' => Product::whereHas('variants', function ($query) {
                    $query->where('stock', '<', 10);
                })->count(),
                'out_of_stock_products' => Product::whereHas('variants', function ($query) {
                    $query->where('stock', 0);
                })->count(),
            ];
return response()->json($stats);
        } catch (\Exception $e) { return response()->json([
                'message' => 'Failed to get product statistics'
            ], 500);
        }
    }
    
    /**
     * Get products with low stock for admin dashboard
     */
    public function getLowStockProducts(Request $request)
    {
        try {
            $threshold = $request->get('threshold', 10);
            
            $products = Product::whereHas('variants', function ($query) use ($threshold) {
                $query->where('stock', '<', $threshold)->where('stock', '>', 0);
            })->with(['variants' => function ($query) use ($threshold) {
                $query->where('stock', '<', $threshold)->where('stock', '>', 0);
            }])->get();
return response()->json($products);
        } catch (\Exception $e) { return response()->json([
                'message' => 'Failed to get low stock products'
            ], 500);
        }
    }
    
    /**
     * Get out of stock products for admin dashboard
     */
    public function getOutOfStockProducts(Request $request)
    {
        try {
            $products = Product::whereHas('variants', function ($query) {
                $query->where('stock', 0);
            })->with(['variants' => function ($query) {
                $query->where('stock', 0);
            }])->get();
return response()->json($products);
        } catch (\Exception $e) { return response()->json([
                'message' => 'Failed to get out of stock products'
            ], 500);
        }
    }
}