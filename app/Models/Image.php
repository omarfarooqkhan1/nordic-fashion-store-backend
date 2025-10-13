<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo; // Important for polymorphic relations

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'alt_text',
        'sort_order',
        'image_type', // Add image type field
        'is_mobile', // Add is_mobile field
        'imageable_id',   // Added for clarity, though morphTo handles this
        'imageable_type', // Added for clarity, though morphTo handles this
    ];

    // Cast is_mobile to boolean
    protected $casts = [
        'is_mobile' => 'boolean',
    ];

    /**
     * Get the parent imageable model (product or product variant).
     */
    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }
}