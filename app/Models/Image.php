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
        'imageable_id',   // Added for clarity, though morphTo handles this
        'imageable_type', // Added for clarity, though morphTo handles this
    ];

    /**
     * Get the parent imageable model (product or product variant).
     */
    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }
}