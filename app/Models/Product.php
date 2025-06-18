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
        'price',
        'category_id',
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
    // This is the line your error is pointing to (line 35 in my common structure)
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    // You might also have accessors/mutators or other methods below here
}