<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Image;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure the 'Jackets' category exists
        $jacketCategory = \App\Models\Category::firstOrCreate([
            'name' => 'Jackets',
        ], [
            'slug' => 'jackets',
            'description' => 'All types of jackets',
        ]);

        // Create a sample product with the new structure
        $product1 = Product::create([
            'name' => 'Classic Leather Jacket',
            'description' => 'A classic leather jacket made from premium leather. Timeless style and exceptional quality.',
            'gender' => 'unisex',
            'category_id' => $jacketCategory->id,
            'is_active' => true,
            'available_sizes' => ['S', 'M', 'L', 'XL'], // Sizes at product level
        ]);

        // Create variants (one per color, no size field)
        $blackVariant = ProductVariant::create([
            'product_id' => $product1->id,
            'sku' => 'CLJ-BLACK',
            'color' => 'Black',
            'price' => 299.99,
            'video_path' => '/storage/videos/jacket.mp4',
        ]);

        $brownVariant = ProductVariant::create([
            'product_id' => $product1->id,
            'sku' => 'CLJ-BROWN',
            'color' => 'Brown',
            'price' => 309.99,
        ]);

        // Add images to Black variant
        $blackVariant->images()->createMany([
            ['url' => '/storage/images/leather-jacket-black-1-front.jpg', 'alt_text' => 'Black Variant Front', 'sort_order' => 0, 'image_type' => 'main'],
            ['url' => '/storage/images/leather-jacket-black-1-back.jpg', 'alt_text' => 'Black Variant Back', 'sort_order' => 1, 'image_type' => 'main'],
        ]);

        // Add images to Brown variant
        $brownVariant->images()->createMany([
            ['url' => '/storage/images/leather-jacket-brown-1-front.jpg', 'alt_text' => 'Brown Variant Front', 'sort_order' => 0, 'image_type' => 'main'],
            ['url' => '/storage/images/leather-jacket-brown-1-back.jpg', 'alt_text' => 'Brown Variant Back', 'sort_order' => 1, 'image_type' => 'main'],
        ]);

        echo "Products seeded successfully with new structure!\n";
    }
}
