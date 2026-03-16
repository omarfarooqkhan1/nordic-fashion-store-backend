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
    public bool $is_active;
    public ?array $available_sizes;

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
            'is_active' => 'nullable|boolean',
            'available_sizes' => 'nullable|array',
            'available_sizes.*' => 'string|in:XS,S,M,L,XL,One Size',
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
            'is_active',
            'available_sizes',
        ];
    }
}