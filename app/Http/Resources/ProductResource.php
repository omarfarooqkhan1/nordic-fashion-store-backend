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

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'category' => new CategoryResource($this->whenLoaded('category')), // Nested category
            'variants' => ProductVariantResource::collection($variants), // Nested variants
            'images' => ImageResource::collection($this->whenLoaded('images')), // Product images
            'availability' => $availability,
            'variantPrices' => $variantPrices,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}