<?php

namespace App\DTO;

class VariantDTO extends BaseDTO
{
    public int $product_id;
    public string $color;
    public ?string $sku;
    public float $price;
    public ?string $video_url;
    public ?string $video_path;

    /**
     * Define validation rules
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'color' => 'required|string|max:50',
            'sku' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'video_url' => 'nullable|string',
            'video_path' => 'nullable|string',
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
            'product_id',
            'color',
            'sku',
            'price',
            'video_url',
            'video_path',
        ];
    }
}