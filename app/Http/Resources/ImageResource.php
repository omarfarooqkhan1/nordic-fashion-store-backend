<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
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
            'url' => $this->url,
            'alt_text' => $this->alt_text,
            'sort_order' => $this->sort_order,
            // 'imageable_type' => $this->imageable_type, // Optionally include if needed for debugging
            // 'imageable_id' => $this->imageable_id,     // Optionally include if needed for debugging
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}