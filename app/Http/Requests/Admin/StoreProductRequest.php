<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:products,name',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'variants' => 'nullable|array',
            'variants.*.sku' => 'required_with:variants|string|unique:product_variants,sku',
            'variants.*.color' => 'nullable|string|max:50',
            'variants.*.size' => 'nullable|string|max:50',
            'variants.*.price' => 'nullable|numeric',
            'variants.*.stock' => 'required_with:variants|integer|min:0',
            'images' => 'nullable|array',
            'images.*.url' => 'nullable|url',
            'images.*.alt_text' => 'nullable|string|max:255',
            'images.*.sort_order' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A product with this name already exists.',
            'category_id.exists' => 'The selected category does not exist.',
        ];
    }
}