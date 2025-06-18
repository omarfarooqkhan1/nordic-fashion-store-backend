<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'color' => $this->color,
            'size' => $this->size,
            'price_difference' => $this->price_difference,
            'actual_price' => $this->actual_price, // Use the accessor
            'stock' => $this->stock,
            'images' => ImageResource::collection($this->whenLoaded('images')), // Nested images
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}