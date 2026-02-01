<?php

namespace App\Repositories;

use App\Models\Image;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ImageRepository
{
    /**
     * Find an image by ID
     *
     * @param int $id
     * @return Image|null
     */
    public function findById(int $id): ?Image
    {
        return Image::find($id);
    }

    /**
     * Create a new image
     *
     * @param array $data
     * @return Image
     */
    public function create(array $data): Image
    {
        $image = Image::create($data);return $image;
    }

    /**
     * Update an image
     *
     * @param Image $image
     * @param array $data
     * @return Image
     */
    public function update(Image $image, array $data): Image
    {
        $image->update($data);return $image;
    }

    /**
     * Delete an image
     *
     * @param Image $image
     * @return bool
     */
    public function delete(Image $image): bool
    {
        $result = $image->delete();return $result;
    }

    /**
     * Get categorized images for a product
     *
     * @param Product $product
     * @return array
     */
    public function getCategorizedImages(Product $product): array
    {
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

        return [
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
        ];
    }
}