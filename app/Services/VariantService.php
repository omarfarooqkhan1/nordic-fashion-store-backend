<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Image;
use App\Exceptions\VariantException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class VariantService
{
    protected $localImageService;

    public function __construct(LocalImageService $localImageService)
    {
        $this->localImageService = $localImageService;
    }

    /**
     * Create a new variant for a product
     *
     * @param Product $product
     * @param array $data
     * @return ProductVariant
     * @throws VariantException
     */
    public function createVariant(Product $product, array $data): ProductVariant
    {
        try {
            // Validate that a variant with the same size and color doesn't already exist
            if ($this->variantExists($product, $data['size'], $data['color'])) {
                throw VariantException::duplicateVariant($data['size'], $data['color']);
            }

            // Generate SKU if not provided
            if (empty($data['sku'])) {
                $data['sku'] = $this->generateSku($product, $data);
            }

            $data['product_id'] = $product->id;

            $variant = ProductVariant::create($data);return $variant;
        } catch (VariantException $e) {throw $e;
        } catch (Exception $e) {throw new VariantException('Failed to create variant: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing variant
     *
     * @param Product $product
     * @param ProductVariant $variant
     * @param array $data
     * @return ProductVariant
     * @throws VariantException
     */
    public function updateVariant(Product $product, ProductVariant $variant, array $data): ProductVariant
    {
        try {
            // Verify the variant belongs to the product
            if ($variant->product_id !== $product->id) {
                throw VariantException::invalidVariant('Variant does not belong to this product');
            }

            // Check if another variant with the same size and color already exists
            if ($this->variantExists($product, $data['size'], $data['color'], $variant->id)) {
                throw VariantException::duplicateVariant($data['size'], $data['color']);
            }

            $variant->update($data);return $variant;
        } catch (VariantException $e) {throw $e;
        } catch (Exception $e) {throw new VariantException('Failed to update variant: ' . $e->getMessage());
        }
    }

    /**
     * Delete a variant
     *
     * @param ProductVariant $variant
     * @return bool
     * @throws VariantException
     */
    public function deleteVariant(ProductVariant $variant): bool
    {
        try {
            DB::beginTransaction();

            // Delete all images associated with the variant
            $variant->images()->delete();

            // Delete the variant itself
            $result = $variant->delete();

            DB::commit();return $result;
        } catch (Exception $e) {
            DB::rollBack();throw new VariantException('Failed to delete variant: ' . $e->getMessage());
        }
    }

    /**
     * Upload images for a variant
     *
     * @param ProductVariant $variant
     * @param array $images
     * @param string $imageType
     * @return array
     * @throws Exception
     */
    public function uploadVariantImages(ProductVariant $variant, array $images, string $imageType = 'main'): array
    {
        try {
            $uploadedImages = [];

            foreach ($images as $image) {
                if ($image instanceof UploadedFile) {
                    $result = $this->localImageService->uploadImage($image, 'variants');
                    
                    if ($result) {
                        $imageModel = $variant->images()->create([
                            'url' => $result['secure_url'],
                            'alt_text' => $variant->product->name . ' - ' . $variant->color . ' - ' . $variant->size . ' - Image',
                            'sort_order' => $variant->images()->count() + 1,
                            'image_type' => $imageType,
                        ]);

                        $uploadedImages[] = $imageModel;
                    }
                }
            }return $uploadedImages;
        } catch (Exception $e) {throw $e;
        }
    }

    /**
     * Upload video for variants of a specific color
     *
     * @param Product $product
     * @param string $color
     * @param UploadedFile $video
     * @return array
     * @throws Exception
     */
    public function uploadVariantVideo(Product $product, string $color, UploadedFile $video): array
    {
        try {
            $filename = uniqid('variant_video_') . '.' . $video->getClientOriginalExtension();
            $path = $video->storeAs('public/variant_videos', $filename);
            $relativePath = str_replace('public/', 'storage/', $path);

            // Update all variants of this color for the product
            $variants = $product->variants()->where('color', $color)->get();
            
            foreach ($variants as $variant) {
                $variant->video_path = $relativePath;
                $variant->save();
            }return [
                'video_path' => $relativePath,
                'variant_ids' => $variants->pluck('id'),
            ];
        } catch (Exception $e) {throw $e;
        }
    }

    /**
     * Generate SKU for a variant
     *
     * @param Product $product
     * @param array $data
     * @return string
     */
    protected function generateSku(Product $product, array $data): string
    {
        $size = $data['size'] ?? 'NOSIZE';
        $color = $data['color'] ?? 'NOCOLOR';
        
        // Create SKU: product name + size + color
        $sku = $product->name . '-' . $size . '-' . $color;
        
        // Sanitize and format SKU
        $sku = preg_replace('/[^A-Za-z0-9\-]/', '', $sku);
        $sku = str_replace(' ', '-', $sku);
        $sku = trim($sku, '-');
        $sku = substr($sku, 0, 100); // Limit to 100 characters
        
        return $sku;
    }

    /**
     * Get all variants for a product with their images
     *
     * @param Product $product
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getProductVariantsWithImages(Product $product)
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