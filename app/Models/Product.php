<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category; // Make sure this is imported
use App\Models\Image;    // <--- THIS IS THE CRITICAL IMPORT
use App\Models\ProductVariant; // Make sure this is imported

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'size_guide_image',
        'gender',
        'category_id',
        'price',
        'discount',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationship to Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relationship to ProductVariants
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    // Polymorphic relationship to Images
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable')->where('image_type', 'main')->orderBy('sort_order');
    }
    
    
    // Get all images regardless of type
    public function allImages()
    {
        return $this->morphMany(Image::class, 'imageable')->orderBy('image_type')->orderBy('sort_order');
    }

    // Get detailed images
    public function detailedImages()
    {
        return $this->morphMany(Image::class, 'imageable')->where('image_type', 'detailed')->orderBy('sort_order');
    }

    // Get mobile-specific detailed images
    public function mobileDetailedImages()
    {
        return $this->morphMany(Image::class, 'imageable')->where('image_type', 'detailed')->where('is_mobile', true)->orderBy('sort_order');
    }

    // Get non-mobile detailed images
    public function desktopDetailedImages()
    {
        return $this->morphMany(Image::class, 'imageable')->where('image_type', 'detailed')->where('is_mobile', false)->orderBy('sort_order');
    }

    // Relationship to ProductReviews
    public function reviews()
    {
        return $this->hasMany(\App\Models\ProductReview::class);
    }

    // Get average rating for the product
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    // Get total review count for the product
    public function getReviewCountAttribute()
    {
        return $this->reviews()->count();
    }

    // You might also have accessors/mutators or other methods below here
}