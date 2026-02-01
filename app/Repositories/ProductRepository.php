<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class ProductRepository
{
    /**
     * Get all products with filters
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAll(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Product::with(['category', 'variants.images', 'allImages']);

        // Search functionality
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Filter by price range
        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Filter by gender
        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        
        if (in_array($sortBy, ['name', 'price', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        return $query->paginate($perPage);
    }

    /**
     * Find a product by ID
     *
     * @param int $id
     * @return Product|null
     */
    public function findById(int $id): ?Product
    {
        return Product::with(['category', 'variants.images', 'allImages'])->find($id);
    }

    /**
     * Create a new product
     *
     * @param array $data
     * @return Product
     */
    public function create(array $data): Product
    {
        $product = Product::create($data);return $product;
    }

    /**
     * Update a product
     *
     * @param Product $product
     * @param array $data
     * @return Product
     */
    public function update(Product $product, array $data): Product
    {
        $product->update($data);return $product;
    }

    /**
     * Delete a product
     *
     * @param Product $product
     * @return bool
     */
    public function delete(Product $product): bool
    {
        $result = $product->delete();return $result;
    }

    /**
     * Get product statistics
     *
     * @return array
     */
    public function getStats(): array
    {
        return [
            'total_products' => Product::count(),
            'total_variants' => \App\Models\ProductVariant::count(),
            'products_by_category' => Category::withCount('products')->get(),
        ];
    }
}