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
        // Ensure the URL is absolute so the frontend can render images from the API host
        $rawUrl = $this->url;
        $absoluteUrl = $rawUrl;
        try {
            if (is_string($rawUrl) && $rawUrl !== '') {
                // If it's a relative path (starts with '/') then prefix app base URL
                if (str_starts_with($rawUrl, '/')) {
                    $absoluteUrl = url($rawUrl);
                }
            }
        } catch (\Throwable $e) {
            // Fallback silently to the raw URL if url() helper fails for any reason
            $absoluteUrl = $rawUrl;
        }

        return [
            'id' => $this->id,
            'url' => $absoluteUrl,
            'alt_text' => $this->alt_text,
            'sort_order' => $this->sort_order,
            'image_type' => $this->image_type,
            'is_mobile' => (bool) $this->is_mobile,
            // 'imageable_type' => $this->imageable_type, // Optionally include if needed for debugging
            // 'imageable_id' => $this->imageable_id,     // Optionally include if needed for debugging
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}