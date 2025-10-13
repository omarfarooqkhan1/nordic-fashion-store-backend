<?php

namespace App\DTO;

class ImageDTO extends BaseDTO
{
    public string $url;
    public ?string $alt_text;
    public int $sort_order;
    public string $image_type;
    public bool $is_mobile;
    public int $imageable_id;
    public string $imageable_type;

    /**
     * Define validation rules
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'url' => 'required|string',
            'alt_text' => 'nullable|string|max:255',
            'sort_order' => 'required|integer|min:0',
            'image_type' => 'required|in:main,detailed,styling,size_guide',
            'is_mobile' => 'required|boolean',
            'imageable_id' => 'required|integer',
            'imageable_type' => 'required|string',
        ];
    }

    /**
     * Get fillable attributes
     *
     * @return array
     */
    public function fillable(): array
    {
        return [
            'url',
            'alt_text',
            'sort_order',
            'image_type',
            'is_mobile',
            'imageable_id',
            'imageable_type',
        ];
    }
}