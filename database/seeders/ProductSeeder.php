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
        $jacketCategory = Category::firstOrCreate(['name' => 'Jackets']);

        // --- BLACK LEATHER JACKETS ---

        // Product 1: Classic Black Leather Jacket (Style 1)
        $product1 = Product::firstOrCreate(
            ['name' => 'Classic Black Leather Jacket'],
            [
                'price' => 299.99,
                'description' => 'A timeless classic crafted from premium black lambskin leather. Features a sleek design with clean lines and a perfect fit. The supple leather develops a beautiful patina over time, making each jacket unique to its owner.',
                'category_id' => $jacketCategory->id,
            ]
        );

        $product1->images()->delete();
        $product1->images()->createMany([
            ['url' => '/storage/images/leather-jacket-black-1-front.jpg', 'alt_text' => 'Classic Black Leather Jacket Front View', 'sort_order' => 0],
            ['url' => '/storage/images/leather-jacket-black-1-back.jpg', 'alt_text' => 'Classic Black Leather Jacket Back View', 'sort_order' => 1],
        ]);

        // Variants for Product 1
        $this->createVariants($product1, 'Black', 'CBLJ-BLK', [
            ['size' => 'S', 'stock' => 25],
            ['size' => 'M', 'stock' => 30],
            ['size' => 'L', 'stock' => 20],
            ['size' => 'XL', 'stock' => 15, 'price_diff' => 15.00],
        ], 'leather-jacket-black-1');

        // Product 2: Modern Black Leather Jacket (Style 2)
        $product2 = Product::firstOrCreate(
            ['name' => 'Modern Black Leather Jacket'],
            [
                'price' => 319.99,
                'description' => 'A contemporary black leather jacket with modern design elements. Features asymmetrical zippers and a more fitted silhouette. Perfect for those who want a sleek, urban look with premium leather quality.',
                'category_id' => $jacketCategory->id,
            ]
        );

        $product2->images()->delete();
        $product2->images()->createMany([
            ['url' => '/storage/images/leather-jacket-black-2-front.jpg', 'alt_text' => 'Modern Black Leather Jacket Front View', 'sort_order' => 0],
            ['url' => '/storage/images/leather-jacket-black-2-back.jpg', 'alt_text' => 'Modern Black Leather Jacket Back View', 'sort_order' => 1],
        ]);

        $this->createVariants($product2, 'Black', 'MBLJ-BLK', [
            ['size' => 'S', 'stock' => 22],
            ['size' => 'M', 'stock' => 28],
            ['size' => 'L', 'stock' => 24],
        ], 'leather-jacket-black-2');

        // Product 3: Vintage Black Leather Jacket (Style 3)
        $product3 = Product::firstOrCreate(
            ['name' => 'Vintage Black Leather Jacket'],
            [
                'price' => 289.99,
                'description' => 'A vintage-inspired black leather jacket with distressed details and classic biker styling. Features multiple pockets and a relaxed fit. Perfect for those who appreciate retro aesthetics with modern comfort.',
                'category_id' => $jacketCategory->id,
            ]
        );

        $product3->images()->delete();
        $product3->images()->createMany([
            ['url' => '/storage/images/leather-jacket-black-3-front.jpg', 'alt_text' => 'Vintage Black Leather Jacket Front View', 'sort_order' => 0],
            ['url' => '/storage/images/leather-jacket-black-3-back.jpg', 'alt_text' => 'Vintage Black Leather Jacket Back View', 'sort_order' => 1],
        ]);

        $this->createVariants($product3, 'Black', 'VBLJ-BLK', [
            ['size' => 'S', 'stock' => 18],
            ['size' => 'M', 'stock' => 25],
            ['size' => 'L', 'stock' => 22],
            ['size' => 'XL', 'stock' => 12, 'price_diff' => 10.00],
        ], 'leather-jacket-black-3');

        // --- BROWN LEATHER JACKETS ---

        // Product 4: Premium Brown Leather Jacket (Style 1)
        $product4 = Product::firstOrCreate(
            ['name' => 'Premium Brown Leather Jacket'],
            [
                'price' => 329.99,
                'description' => 'Handcrafted from rich brown full-grain leather with a vintage-inspired design. Features intricate stitching details and a comfortable fit that molds to your body. Perfect for those who appreciate classic American style with a modern twist.',
                'category_id' => $jacketCategory->id,
            ]
        );

        $product4->images()->delete();
        $product4->images()->createMany([
            ['url' => '/storage/images/leather-jacket-brown-1-front.jpg', 'alt_text' => 'Premium Brown Leather Jacket Front View', 'sort_order' => 0],
            ['url' => '/storage/images/leather-jacket-brown-1-back.jpg', 'alt_text' => 'Premium Brown Leather Jacket Back View', 'sort_order' => 1],
        ]);

        $this->createVariants($product4, 'Brown', 'PBLJ-BRN', [
            ['size' => 'S', 'stock' => 20],
            ['size' => 'M', 'stock' => 25],
            ['size' => 'L', 'stock' => 18],
        ], 'leather-jacket-brown-1');

        // Product 5: Classic Brown Leather Jacket (Style 2)
        $product5 = Product::firstOrCreate(
            ['name' => 'Classic Brown Leather Jacket'],
            [
                'price' => 299.99,
                'description' => 'A traditional brown leather jacket with timeless appeal. Features clean lines and a versatile design that works for both casual and semi-formal occasions. Made from high-quality brown leather that ages beautifully.',
                'category_id' => $jacketCategory->id,
            ]
        );

        $product5->images()->delete();
        $product5->images()->createMany([
            ['url' => '/storage/images/leather-jacket-brown-2-front.jpg', 'alt_text' => 'Classic Brown Leather Jacket Front View', 'sort_order' => 0],
            ['url' => '/storage/images/leather-jacket-brown-2-back.jpg', 'alt_text' => 'Classic Brown Leather Jacket Back View', 'sort_order' => 1],
        ]);

        $this->createVariants($product5, 'Brown', 'CBLJ-BRN', [
            ['size' => 'S', 'stock' => 22],
            ['size' => 'M', 'stock' => 28],
            ['size' => 'L', 'stock' => 24],
            ['size' => 'XL', 'stock' => 16, 'price_diff' => 12.00],
        ], 'leather-jacket-brown-2');

        // --- DARK BROWN LEATHER JACKET ---

        // Product 6: Dark Brown Leather Jacket
        $product6 = Product::firstOrCreate(
            ['name' => 'Dark Brown Leather Jacket'],
            [
                'price' => 309.99,
                'description' => 'A sophisticated dark brown leather jacket with a rich, deep color. Features premium leather construction and a refined design. Perfect for those who want a more formal leather jacket option.',
                'category_id' => $jacketCategory->id,
            ]
        );

        $product6->images()->delete();
        $product6->images()->createMany([
            ['url' => '/storage/images/leather-jacket-dark-brown-1-front.jpg', 'alt_text' => 'Dark Brown Leather Jacket Front View', 'sort_order' => 0],
            ['url' => '/storage/images/leather-jacket-dark-brown-1-back.jpg', 'alt_text' => 'Dark Brown Leather Jacket Back View', 'sort_order' => 1],
        ]);

        $this->createVariants($product6, 'Dark Brown', 'DBLJ-DBRN', [
            ['size' => 'S', 'stock' => 16],
            ['size' => 'M', 'stock' => 22],
            ['size' => 'L', 'stock' => 20],
        ], 'leather-jacket-dark-brown-1');

        // --- BURGUNDY LEATHER JACKETS ---

        // Product 7: Burgundy Leather Jacket (Style 1)
        $product7 = Product::firstOrCreate(
            ['name' => 'Burgundy Leather Jacket'],
            [
                'price' => 319.99,
                'description' => 'A bold and distinctive burgundy leather jacket that makes a statement. Crafted from premium leather with a rich, deep color that stands out from the crowd. Features modern design elements and exceptional craftsmanship.',
                'category_id' => $jacketCategory->id,
            ]
        );

        $product7->images()->delete();
        $product7->images()->createMany([
            ['url' => '/storage/images/leather-jacket-burgundy-2-front.jpg', 'alt_text' => 'Burgundy Leather Jacket Front View', 'sort_order' => 0],
            ['url' => '/storage/images/leather-jacket-burgundy-2-back.jpg', 'alt_text' => 'Burgundy Leather Jacket Back View', 'sort_order' => 1],
        ]);

        $this->createVariants($product7, 'Burgundy', 'BGLJ-BUR', [
            ['size' => 'S', 'stock' => 15],
            ['size' => 'M', 'stock' => 20],
            ['size' => 'L', 'stock' => 18],
        ], 'leather-jacket-burgundy-2');

        // Product 8: Premium Burgundy Leather Jacket (Style 2)
        $product8 = Product::firstOrCreate(
            ['name' => 'Premium Burgundy Leather Jacket'],
            [
                'price' => 339.99,
                'description' => 'An elegant burgundy leather jacket with sophisticated styling. Features premium leather construction and refined details. Perfect for those who want a unique color with exceptional quality and style.',
                'category_id' => $jacketCategory->id,
            ]
        );

        $product8->images()->delete();
        $product8->images()->createMany([
            ['url' => '/storage/images/leather-jacket-burugndy-1-front.jpg', 'alt_text' => 'Premium Burgundy Leather Jacket Front View', 'sort_order' => 0],
            ['url' => '/storage/images/leather-jacket-burugndy-1-back.jpg', 'alt_text' => 'Premium Burgundy Leather Jacket Back View', 'sort_order' => 1],
        ]);

        $this->createVariants($product8, 'Burgundy', 'PBLJ-BUR', [
            ['size' => 'S', 'stock' => 12],
            ['size' => 'M', 'stock' => 18],
            ['size' => 'L', 'stock' => 16],
        ], 'leather-jacket-burugndy-1');

        // --- BLUE LEATHER JACKET ---

        // Product 9: Navy Blue Leather Jacket
        $product9 = Product::firstOrCreate(
            ['name' => 'Navy Blue Leather Jacket'],
            [
                'price' => 279.99,
                'description' => 'A sophisticated navy blue leather jacket that combines elegance with durability. Made from high-quality leather with a smooth finish and contemporary styling. Perfect for both casual and semi-formal occasions.',
                'category_id' => $jacketCategory->id,
            ]
        );

        $product9->images()->delete();
        $product9->images()->createMany([
            ['url' => '/storage/images/leather-jacket-blue-1-front.jpg', 'alt_text' => 'Navy Blue Leather Jacket Front View', 'sort_order' => 0],
            ['url' => '/storage/images/leather-jacket-blue-1-back.jpg', 'alt_text' => 'Navy Blue Leather Jacket Back View', 'sort_order' => 1],
        ]);

        $this->createVariants($product9, 'Navy Blue', 'NBLJ-BLU', [
            ['size' => 'S', 'stock' => 22],
            ['size' => 'M', 'stock' => 28],
            ['size' => 'L', 'stock' => 24],
        ], 'leather-jacket-blue-1');

        // --- OLIVE GREEN LEATHER JACKET ---

        // Product 10: Olive Green Leather Jacket
        $product10 = Product::firstOrCreate(
            ['name' => 'Olive Green Leather Jacket'],
            [
                'price' => 289.99,
                'description' => 'A rugged olive green leather jacket inspired by military and aviation heritage. Built to last with reinforced stitching and durable hardware. The earthy green color pairs perfectly with casual and outdoor wear.',
                'category_id' => $jacketCategory->id,
            ]
        );

        $product10->images()->delete();
        $product10->images()->createMany([
            ['url' => '/storage/images/leather-jacket-olive-1-front.jpg', 'alt_text' => 'Olive Green Leather Jacket Front View', 'sort_order' => 0],
            ['url' => '/storage/images/leather-jacket-olive-1-back.jpg', 'alt_text' => 'Olive Green Leather Jacket Back View', 'sort_order' => 1],
        ]);

        $this->createVariants($product10, 'Olive Green', 'OGLJ-OLV', [
            ['size' => 'S', 'stock' => 16],
            ['size' => 'M', 'stock' => 22],
            ['size' => 'L', 'stock' => 19],
        ], 'leather-jacket-olive-1');

        // --- WHITE LEATHER JACKET ---

        // Product 11: White Leather Jacket
        $product11 = Product::firstOrCreate(
            ['name' => 'White Leather Jacket'],
            [
                'price' => 299.99,
                'description' => 'A striking white leather jacket that makes a bold fashion statement. Crafted from premium white leather with a clean, minimalist design. Perfect for those who want to stand out with a unique and elegant look.',
                'category_id' => $jacketCategory->id,
            ]
        );

        $product11->images()->delete();
        $product11->images()->createMany([
            ['url' => '/storage/images/leather-jacket-white-1-front.jpg', 'alt_text' => 'White Leather Jacket Front View', 'sort_order' => 0],
            ['url' => '/storage/images/leather-jacket-white-1-back.jpg', 'alt_text' => 'White Leather Jacket Back View', 'sort_order' => 1],
        ]);

        $this->createVariants($product11, 'White', 'WLJ-WHT', [
            ['size' => 'S', 'stock' => 14],
            ['size' => 'M', 'stock' => 20],
            ['size' => 'L', 'stock' => 18],
        ], 'leather-jacket-white-1');
    }

    /**
     * Helper method to create variants for a product
     */
    private function createVariants($product, $color, $skuPrefix, $variants, $imagePrefix)
    {
        foreach ($variants as $index => $variant) {
            $sku = $skuPrefix . '-' . $variant['size'] . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            
            $variantModel = $product->variants()->firstOrCreate(
                ['sku' => $sku],
                [
                    'color' => $color,
                    'size' => $variant['size'],
                    'price_difference' => $variant['price_diff'] ?? 0.00,
                    'stock' => $variant['stock'],
                ]
            );
            
            $variantModel->images()->delete();
            $variantModel->images()->createMany([
                ['url' => '/storage/images/' . $imagePrefix . '-front.jpg', 'alt_text' => $color . ' ' . $variant['size'] . ' Jacket Front', 'sort_order' => 0],
            ]);
        }
    }
}