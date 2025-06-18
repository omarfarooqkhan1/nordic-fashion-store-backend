<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Image;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'color',
        'size',
        'price_difference',
        'stock',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getActualPriceAttribute(): float
    {
        return $this->product->price + $this->price_difference;
    }

    /**
     * Get all of the images for the product variant.
     */
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable')->orderBy('sort_order');
    }
}