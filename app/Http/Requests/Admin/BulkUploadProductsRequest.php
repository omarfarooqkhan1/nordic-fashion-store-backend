<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BulkUploadProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
            'update_existing' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please upload a CSV file.',
            'file.mimes' => 'File must be a CSV format.',
            'file.max' => 'File size cannot exceed 10MB.',
        ];
    }
}
