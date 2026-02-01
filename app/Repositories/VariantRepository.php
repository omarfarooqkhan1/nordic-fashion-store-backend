<?php

namespace App\Repositories;

use App\Models\ProductVariant;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class VariantRepository
{
    /**
     * Find a variant by ID
     *
     * @param int $id
     * @return ProductVariant|null
     */
    public function findById(int $id): ?ProductVariant
    {
        return ProductVariant::with('images')->find($id);
    }

    /**
     * Create a new variant
     *
     * @param array $data
     * @return ProductVariant
     */
    public function create(array $data): ProductVariant
    {
        $variant = ProductVariant::create($data);return $variant;
    }

    /**
     * Update a variant
     *
     * @param ProductVariant $variant
     * @param array $data
     * @return ProductVariant
     */
    public function update(ProductVariant $variant, array $data): ProductVariant
    {
        $variant->update($data);return $variant;
    }

    /**
     * Delete a variant
     *
     * @param ProductVariant $variant
     * @return bool
     */
    public function delete(ProductVariant $variant): bool
    {
        $result = $variant->delete();return $result;
    }

    /**
     * Get all variants for a product
     *
     * @param Product $product
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVariantsByProduct(Product $product)
    {
        return $product->variants()->with('images')->get();
    }

    /**
     * Check if a variant with specific attributes already exists
     *
     * @param Product $product
     * @param string $size
     * @param string $color
     * @param int|null $excludeId
     * @return bool
     */
    public function variantExists(Product $product, string $size, string $color, ?int $excludeId = null): bool
    {
        $query = ProductVariant::where([
            'product_id' => $product->id,
            'size' => $size,
            'color' => $color
        ]);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}