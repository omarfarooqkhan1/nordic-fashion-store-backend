<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\Image;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductCollection;
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
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            'update_existing' => 'sometimes|in:true,false,1,0',
        ]);

        try {
            $file = $request->file('csv_file');
            $updateExisting = filter_var($request->input('update_existing', false), FILTER_VALIDATE_BOOLEAN);
            
            // Debug logging
            Log::info('Bulk upload started', [
                'filename' => $file->getClientOriginalName(),
                'update_existing' => $updateExisting,
                'file_size' => $file->getSize()
            ]);
            
            $csvData = array_map('str_getcsv', file($file->getRealPath()));
            $header = array_shift($csvData);
            
            // Validate CSV headers
            $requiredHeaders = ['name', 'description', 'price', 'category_name'];
            $optionalHeaders = ['sku', 'color', 'size', 'price_difference', 'stock', 'image_url_1', 'image_url_2', 'image_url_3', 'image_url_4', 'image_url_5'];
            $allValidHeaders = array_merge($requiredHeaders, $optionalHeaders);
            
            $missingHeaders = array_diff($requiredHeaders, $header);
            $invalidHeaders = array_diff($header, $allValidHeaders);
            
            if (!empty($missingHeaders)) {
                return response()->json([
                    'message' => 'CSV missing required headers: ' . implode(', ', $missingHeaders),
                    'required_headers' => $requiredHeaders,
                    'optional_headers' => $optionalHeaders,
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
                        $this->processProductImages($product, $rowData, $updateExisting);
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

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk upload failed: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Bulk upload failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process product images from CSV data
     */
    private function processProductImages(Product $product, array $rowData, bool $updateExisting): void
    {
        $imageColumns = ['image_url_1', 'image_url_2', 'image_url_3', 'image_url_4', 'image_url_5'];
        $imageUrls = [];

        // Collect valid image URLs
        foreach ($imageColumns as $column) {
            if (!empty($rowData[$column]) && filter_var($rowData[$column], FILTER_VALIDATE_URL)) {
                $imageUrls[] = $rowData[$column];
            }
        }

        if (empty($imageUrls)) {
            return; // No valid image URLs provided
        }

        // If updating existing product, remove old images
        if ($updateExisting) {
            $product->images()->delete();
        }

        // Create new images
        foreach ($imageUrls as $index => $imageUrl) {
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
     * Download CSV template for bulk upload
     */
    public function getBulkUploadTemplate()
    {
        $headers = ['name', 'description', 'price', 'category_name', 'sku', 'color', 'size', 'price_difference', 'stock', 'image_url_1', 'image_url_2', 'image_url_3', 'image_url_4', 'image_url_5'];
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
                'https://example.com/images/nordic-sweater-red-front.jpg',
                'https://example.com/images/nordic-sweater-red-back.jpg',
                'https://example.com/images/nordic-sweater-red-detail.jpg',
                '',
                ''
            ],
            [
                'Nordic Wool Sweater', 
                'Warm and cozy wool sweater perfect for Nordic winters', 
                '89.99', 
                'Clothing', 
                'NWS-RED-L', 
                'Red', 
                'L', 
                '5.00', 
                '30',
                'https://example.com/images/nordic-sweater-red-front.jpg',
                'https://example.com/images/nordic-sweater-red-back.jpg',
                '',
                '',
                ''
            ],
            [
                'Leather Boots', 
                'Premium leather boots for harsh weather', 
                '149.99', 
                'Footwear', 
                'LB-BROWN-42', 
                'Brown', 
                '42', 
                '0.00', 
                '25',
                'https://example.com/images/leather-boots-brown-side.jpg',
                'https://example.com/images/leather-boots-brown-sole.jpg',
                'https://example.com/images/leather-boots-brown-detail.jpg',
                'https://example.com/images/leather-boots-brown-top.jpg',
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
}