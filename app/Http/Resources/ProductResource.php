<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Prepare availability and variant prices keyed by "size-color"
        $availability = [];
        $variantPrices = [];

        $variants = $this->whenLoaded('variants');

        if ($variants) {
            foreach ($variants as $variant) {
                $key = $variant->size . '-' . $variant->color;

                $availability[$key] = $variant->stock > 0;

                // Use accessor actual_price for price with price difference included
                $variantPrices[$key] = $variant->actual_price;
            }
        }

        // Get size guide image - prioritize the product's size_guide_image field, fallback to images relationship
        $sizeGuideImage = $this->size_guide_image;
        if (empty($sizeGuideImage)) {
            $sizeGuideImage = null;
            $detailedImages = collect();
            $mobileDetailedImages = collect();
            
            if ($this->relationLoaded('allImages')) {
                $sizeGuide = $this->allImages->firstWhere('image_type', 'size_guide');
                if ($sizeGuide) {
                    $sizeGuideImage = $sizeGuide->url; // Access the model's url property directly
                }
                
                // Get detailed images
                $detailedImages = $this->allImages->where('image_type', 'detailed')->values();
                
                // Get mobile detailed images
                $mobileDetailedImages = $this->allImages->where('image_type', 'detailed')->where('is_mobile', true)->values();
            } elseif ($this->relationLoaded('images')) {
                // Fallback to querying through the images relationship (which might not include size_guide)
                $sizeGuide = $this->allImages()->where('image_type', 'size_guide')->first();
                if ($sizeGuide) {
                    $sizeGuideImage = $sizeGuide->url;
                }
                
                // Get detailed images
                $detailedImages = $this->detailedImages;
                
                // Get mobile detailed images
                $mobileDetailedImages = $this->mobileDetailedImages;
            }
        } else {
            // If size_guide_image field is populated, still get detailed images from relationships
            $detailedImages = collect();
            $mobileDetailedImages = collect();
            
            if ($this->relationLoaded('allImages')) {
                // Get detailed images
                $detailedImages = $this->allImages->where('image_type', 'detailed')->values();
                
                // Get mobile detailed images
                $mobileDetailedImages = $this->allImages->where('image_type', 'detailed')->where('is_mobile', true)->values();
            } elseif ($this->relationLoaded('images')) {
                // Get detailed images
                $detailedImages = $this->detailedImages;
                
                // Get mobile detailed images
                $mobileDetailedImages = $this->mobileDetailedImages;
            }
        }
        
        // Get first variant's main images as product images for initial display
        $productImages = collect();
        if ($variants instanceof \Illuminate\Support\Collection && $variants->count() > 0) {
            $firstVariant = $variants->first();
            if ($firstVariant && method_exists($firstVariant, 'relationLoaded') && $firstVariant->relationLoaded('images')) {
                $productImages = $firstVariant->images->where('image_type', 'main')->values();
            }
        }
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'size_guide_image' => $sizeGuideImage,
            'gender' => $this->gender,
            'discount' => $this->discount ?? null, // Add discount field with fallback to null
            'category' => $this->whenLoaded('category') ? new CategoryResource($this->whenLoaded('category')) : null,
            'variants' => ProductVariantResource::collection($variants), // Nested variants
            'images' => ImageResource::collection($productImages), // First variant's main images for product listing
            'detailed_images' => ImageResource::collection($detailedImages), // Detailed images
            'mobile_detailed_images' => ImageResource::collection($mobileDetailedImages), // Mobile detailed images
            'availability' => $availability,
            'variantPrices' => $variantPrices,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}