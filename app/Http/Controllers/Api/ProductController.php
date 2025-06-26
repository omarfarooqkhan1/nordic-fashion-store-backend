<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
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
            'file' => 'required|file|mimes:csv,txt|max:10240',
            'update_existing' => 'boolean',
        ]);

        try {
            $file = $request->file('file');
            $updateExisting = $request->boolean('update_existing', false);
            
            $csvData = array_map('str_getcsv', file($file->getRealPath()));
            $header = array_shift($csvData);
            
            // Validate CSV headers
            $requiredHeaders = ['name', 'description', 'price', 'category_name'];
            $missingHeaders = array_diff($requiredHeaders, $header);
            
            if (!empty($missingHeaders)) {
                return response()->json([
                    'message' => 'CSV missing required headers: ' . implode(', ', $missingHeaders),
                    'required_headers' => $requiredHeaders,
                    'found_headers' => $header
                ], 422);
            }

            $results = [
                'total_rows' => count($csvData),
                'successful' => 0,
                'failed' => 0,
                'errors' => []
            ];

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

                    $productData = [
                        'name' => $rowData['name'],
                        'description' => $rowData['description'] ?? null,
                        'price' => (float) $rowData['price'],
                        'category_id' => $category->id,
                    ];

                    $existingProduct = Product::where('name', $productData['name'])->first();

                    if ($existingProduct) {
                        if ($updateExisting) {
                            $existingProduct->update($productData);
                            $results['successful']++;
                        } else {
                            throw new \Exception("Product already exists");
                        }
                    } else {
                        Product::create($productData);
                        $results['successful']++;
                    }

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

            return response()->json([
                'message' => 'Bulk upload completed.',
                'results' => $results
            ]);

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
     * Download CSV template for bulk upload
     */
    public function getBulkUploadTemplate()
    {
        $headers = ['name', 'description', 'price', 'category_name'];
        $sampleData = [
            ['Sample Product 1', 'Sample description', '29.99', 'Electronics'],
            ['Sample Product 2', 'Another description', '49.99', 'Clothing']
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