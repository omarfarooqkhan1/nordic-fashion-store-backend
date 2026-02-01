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
        $images = $this->whenLoaded('images');
        $mainImages = (is_a($images, 'Illuminate\\Support\\Collection'))
            ? $images->where('image_type', 'main')->values()
            : collect();
        $detailedImages = (is_a($images, 'Illuminate\\Support\\Collection'))
            ? $images->where('image_type', 'detailed')->where('is_mobile', false)->values()
            : collect();
        $mobileDetailedImages = (is_a($images, 'Illuminate\\Support\\Collection'))
            ? $images->where('image_type', 'detailed')->where('is_mobile', true)->values()
            : collect();
        $stylingImages = (is_a($images, 'Illuminate\\Support\\Collection'))
            ? $images->where('image_type', 'styling')->values()
            : collect();
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'color' => $this->color,
            'size' => $this->size,
            'price' => $this->price,
            'main_images' => ImageResource::collection($mainImages),
            'detailed_images' => ImageResource::collection($detailedImages),
            'mobile_detailed_images' => ImageResource::collection($mobileDetailedImages),
            'styling_images' => ImageResource::collection($stylingImages),
            'video_url' => $this->video_url,
            'label' => "{$this->size} | {$this->color}",
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}