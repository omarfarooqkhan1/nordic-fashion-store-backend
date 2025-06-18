<?php

namespace Database\Seeders;

use App\Models\Category; // Import Category model
use App\Models\Product;    // Import Product model
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch categories - ensure they exist or create them if seeding individually
        // Using firstOrCreate ensures categories are there, useful if running ProductSeeder directly
        $jacketCategory = Category::firstOrCreate(['name' => 'Jackets']);
        $bagCategory = Category::firstOrCreate(['name' => 'Bags']);

        // --- Product 1: Classic Leather Jacket ---
        $product1 = Product::firstOrCreate(
            ['name' => 'Classic Leather Jacket'], // Unique identifier for finding
            [
                'price' => 299.99,
                'description' => 'A timeless classic, crafted from genuine lambskin leather. Perfect for all seasons.',
                'category_id' => $jacketCategory->id,
            ]
        );

        // Product Images (Use firstOrCreate for images if you might re-run ProductSeeder without fresh)
        // For simplicity and clarity during initial setup, createMany is often fine after a migrate:fresh
        // If you were running this seeder repeatedly on an existing db, you'd add more logic to sync images.
        // For now, we assume a fresh start or that images are disposable for re-seeding.
        $product1->images()->delete(); // Clear existing images before re-creating
        $product1->images()->createMany([
            ['url' => 'https://example.com/images/jacket_classic_main.jpg', 'alt_text' => 'Classic Leather Jacket Front', 'sort_order' => 0],
            ['url' => 'https://example.com/images/jacket_classic_back.jpg', 'alt_text' => 'Classic Leather Jacket Back', 'sort_order' => 1],
            ['url' => 'https://example.com/images/jacket_classic_detail.jpg', 'alt_text' => 'Classic Leather Jacket Detail', 'sort_order' => 2],
        ]);

        // Variants for Product 1 (using firstOrCreate for the variant, but delete and recreate images)
        $variant1_1 = $product1->variants()->firstOrCreate(
            ['sku' => 'CLJ-BLK-M-001'],
            [
                'color' => 'Black',
                'size' => 'M',
                'price_difference' => 0.00,
                'stock' => 50,
            ]
        );
        $variant1_1->images()->delete();
        $variant1_1->images()->createMany([
            ['url' => 'https://example.com/images/jacket_black_m.jpg', 'alt_text' => 'Black M Jacket', 'sort_order' => 0],
        ]);

        $variant1_2 = $product1->variants()->firstOrCreate(
            ['sku' => 'CLJ-BLK-L-002'],
            [
                'color' => 'Black',
                'size' => 'L',
                'price_difference' => 10.00,
                'stock' => 30,
            ]
        );
        $variant1_2->images()->delete();
        $variant1_2->images()->createMany([
            ['url' => 'https://example.com/images/jacket_black_l.jpg', 'alt_text' => 'Black L Jacket', 'sort_order' => 0],
        ]);

        $variant1_3 = $product1->variants()->firstOrCreate(
            ['sku' => 'CLJ-BRN-M-003'],
            [
                'color' => 'Brown',
                'size' => 'M',
                'price_difference' => 5.00,
                'stock' => 25,
            ]
        );
        $variant1_3->images()->delete();
        $variant1_3->images()->createMany([
            ['url' => 'https://example.com/images/jacket_brown_m.jpg', 'alt_text' => 'Brown M Jacket', 'sort_order' => 0],
        ]);


        // --- Product 2: Vintage Leather Messenger Bag ---
        $product2 = Product::firstOrCreate(
            ['name' => 'Vintage Leather Messenger Bag'],
            [
                'price' => 149.99,
                'description' => 'Hand-distressed full-grain leather messenger bag with multiple compartments.',
                'category_id' => $bagCategory->id,
            ]
        );

        // Product Images
        $product2->images()->delete();
        $product2->images()->createMany([
            ['url' => 'https://example.com/images/bag_messenger_main.jpg', 'alt_text' => 'Messenger Bag Front', 'sort_order' => 0],
            ['url' => 'https://example.com/images/bag_messenger_inside.jpg', 'alt_text' => 'Messenger Bag Interior', 'sort_order' => 1],
        ]);

        // Variants for Product 2
        $variant2_1 = $product2->variants()->firstOrCreate(
            ['sku' => 'VMB-DRK-001'],
            [
                'color' => 'Dark Brown',
                'size' => 'One Size',
                'price_difference' => 0.00,
                'stock' => 40,
            ]
        );
        $variant2_1->images()->delete();
        $variant2_1->images()->createMany([
            ['url' => 'https://example.com/images/bag_messenger_dark.jpg', 'alt_text' => 'Dark Brown Messenger Bag', 'sort_order' => 0],
        ]);

        $variant2_2 = $product2->variants()->firstOrCreate(
            ['sku' => 'VMB-LGT-002'],
            [
                'color' => 'Light Brown',
                'size' => 'One Size',
                'price_difference' => 5.00,
                'stock' => 20,
            ]
        );
        $variant2_2->images()->delete();
        $variant2_2->images()->createMany([
            ['url' => 'https://example.com/images/bag_messenger_light.jpg', 'alt_text' => 'Light Brown Messenger Bag', 'sort_order' => 0],
        ]);
    }
}