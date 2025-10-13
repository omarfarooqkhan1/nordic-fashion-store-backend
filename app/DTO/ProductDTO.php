<?php

namespace App\DTO;

class ProductDTO extends BaseDTO
{
    public string $name;
    public ?string $description;
    public ?string $size_guide_image;
    public string $gender;
    public int $category_id;
    public ?float $discount;

    /**
     * Define validation rules
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'size_guide_image' => 'nullable|string',
            'gender' => 'required|in:male,female,unisex',
            'category_id' => 'required|exists:categories,id',
            'discount' => 'nullable|numeric|min:0|max:100',
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
            'name',
            'description',
            'size_guide_image',
            'gender',
            'category_id',
            'discount',
        ];
    }
}