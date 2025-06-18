<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductCollection;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Eager load relationships for efficiency
        $products = Product::with(['category', 'variants.images', 'images'])->get();
        return new ProductCollection($products);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        // Eager load relationships for a single product
        $product->loadMissing(['category', 'variants.images', 'images']);
        return new ProductResource($product);
    }

    // For now, focusing on GET. We'll add store/update/destroy later for products.
}