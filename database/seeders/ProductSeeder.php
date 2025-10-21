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
        // Ensure the 'Jackets' category exists for all jacket products
        $jacketCategory = \App\Models\Category::firstOrCreate([
            'name' => 'Jackets',
        ], [
            'slug' => 'jackets',
            'description' => 'All types of jackets',
        ]);

        // Blog images for desktop view
        $blogImages = [
            '/storage/images/blogs/1.jpeg',
            '/storage/images/blogs/2.jpeg',
            '/storage/images/blogs/3.jpeg',
            '/storage/images/blogs/4.jpeg',
            '/storage/images/blogs/5.jpeg',
            '/storage/images/blogs/6.jpeg',
            '/storage/images/blogs/7.jpeg',
            '/storage/images/blogs/8.jpeg',
            '/storage/images/blogs/9.jpeg',
            '/storage/images/blogs/10.jpeg'
        ];

        // Product images for mobile view
        $mobileImages = [
            '/storage/images/leather-jacket-black-1-front.jpg',
            '/storage/images/leather-jacket-black-2-front.jpg',
            '/storage/images/leather-jacket-black-3-front.jpg',
            '/storage/images/leather-jacket-brown-1-front.jpg',
            '/storage/images/leather-jacket-blue-1-front.jpg',
            '/storage/images/leather-jacket-burgundy-2-front.jpg',
            '/storage/images/leather-jacket-dark-brown-1-front.jpg',
            '/storage/images/leather-jacket-olive-1-front.jpg',
            '/storage/images/leather-jacket-white-1-front.jpg'
        ];

        // Ensure the 'Classic Black Leather Jacket' product exists for $product1
        $product1 = Product::firstOrCreate(
            ['name' => 'Classic Black Leather Jacket'],
            [
                'description' => 'A classic black leather jacket made from premium leather. Timeless style and exceptional quality.',
                'gender' => 'unisex',
                'category_id' => $jacketCategory->id,
            ]
        );

        // Add main product images
        $product1->images()->delete();
        $product1->images()->createMany([
            ['url' => '/storage/images/leather-jacket-black-1-front.jpg', 'alt_text' => 'Classic Black Leather Jacket Front View', 'sort_order' => 0, 'image_type' => 'main'],
            ['url' => '/storage/images/leather-jacket-black-1-back.jpg', 'alt_text' => 'Classic Black Leather Jacket Back View', 'sort_order' => 1, 'image_type' => 'main'],
        ]);
        // --- VARIANTS FOR PRODUCT 1 ---
        $product1->variants()->delete();
        $variant1a = ProductVariant::create([
            'product_id' => $product1->id,
            'sku' => 'CBLJ-BLK-S',
            'color' => 'Black',
            'size' => 'S',
            'price' => 299.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        $variant1b = ProductVariant::create([
            'product_id' => $product1->id,
            'sku' => 'CBLJ-BLK-M',
            'color' => 'Black',
            'size' => 'M',
            'price' => 309.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        // Shuffle and select 2 random blog images for desktop view
        $shuffledBlogImages = $blogImages;
        shuffle($shuffledBlogImages);
        $selectedBlogImages = array_slice($shuffledBlogImages, 0, 2);
        // Shuffle and select 2 random mobile images for mobile view
        $shuffledMobileImages = $mobileImages;
        shuffle($shuffledMobileImages);
        $selectedMobileImages = array_slice($shuffledMobileImages, 0, 2);
        // Shared images for all Black variants (attach to only one variant per color)
        $blackVariantImages = [
            // 2 main images
            ['url' => '/storage/images/leather-jacket-black-1-front.jpg', 'alt_text' => 'Black Variant Front', 'sort_order' => 0, 'image_type' => 'main'],
            ['url' => '/storage/images/leather-jacket-black-1-back.jpg', 'alt_text' => 'Black Variant Back', 'sort_order' => 1, 'image_type' => 'main'],
            // 2 detailed images (desktop)
            ['url' => '/storage/images/leather-jacket-black-2-front.jpg', 'alt_text' => 'Detail View 1', 'sort_order' => 2, 'image_type' => 'detailed', 'is_mobile' => false],
            ['url' => '/storage/images/leather-jacket-black-3-front.jpg', 'alt_text' => 'Detail View 2', 'sort_order' => 3, 'image_type' => 'detailed', 'is_mobile' => false],
            // 2 detailed images (mobile)
            ['url' => $selectedMobileImages[0], 'alt_text' => 'Mobile Detail 1', 'sort_order' => 4, 'image_type' => 'detailed', 'is_mobile' => true],
            ['url' => $selectedMobileImages[1], 'alt_text' => 'Mobile Detail 2', 'sort_order' => 5, 'image_type' => 'detailed', 'is_mobile' => true],
            // 2 styling images
            ['url' => '/storage/images/leather-jacket-woman-black-1-1.jpeg', 'alt_text' => 'Styling Inspiration 1', 'sort_order' => 6, 'image_type' => 'styling'],
            ['url' => '/storage/images/leather-jacket-woman-black-2-1.jpeg', 'alt_text' => 'Styling Inspiration 2', 'sort_order' => 7, 'image_type' => 'styling'],
        ];
        $variant1a->images()->createMany($blackVariantImages);
        // Do not attach images to $variant1b (same color)
        
        // TODO: Implement createMultiColorVariants or manually seed variants for $product1
        // $this->createMultiColorVariants($product1, [...]);

        // Product 2: Modern Black Leather Jacket (Style 2)
        $product2 = Product::firstOrCreate(
            ['name' => 'Modern Black Leather Jacket'],
            [
                'description' => 'A contemporary black leather jacket with modern design elements. Features asymmetrical zippers and a more fitted silhouette. Perfect for those who want a sleek, urban look with premium leather quality.',
                'gender' => 'unisex',
                'category_id' => $jacketCategory->id,
            ]
        );

        $product2->images()->delete();
        $product2->images()->createMany([
            ['url' => '/storage/images/leather-jacket-black-2-front.jpg', 'alt_text' => 'Modern Black Leather Jacket Front View', 'sort_order' => 0, 'image_type' => 'main'],
            ['url' => '/storage/images/leather-jacket-black-2-back.jpg', 'alt_text' => 'Modern Black Leather Jacket Back View', 'sort_order' => 1, 'image_type' => 'main'],
        ]);

        // --- VARIANTS FOR PRODUCT 2 ---
        $product2->variants()->delete();
        $variant2a = ProductVariant::create([
            'product_id' => $product2->id,
            'sku' => 'MBLJ-BLK-S',
            'color' => 'Black',
            'size' => 'S',
            'price' => 319.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        $variant2b = ProductVariant::create([
            'product_id' => $product2->id,
            'sku' => 'MBLJ-BLK-M',
            'color' => 'Black',
            'size' => 'M',
            'price' => 334.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        
        // Shuffle and select 2 random blog images for desktop view
        $shuffledBlogImages2 = $blogImages;
        shuffle($shuffledBlogImages2);
        $selectedBlogImages2 = array_slice($shuffledBlogImages2, 0, 2);
        
        // Shuffle and select 2 random mobile images for mobile view
        $shuffledMobileImages2 = $mobileImages;
        shuffle($shuffledMobileImages2);
        $selectedMobileImages2 = array_slice($shuffledMobileImages2, 0, 2);
        
        // Shared images for all Black variants of Product 2
        $blackVariantImages2 = [
            // 2 main images
            ['url' => '/storage/images/leather-jacket-black-2-front.jpg', 'alt_text' => 'Black Variant Front', 'sort_order' => 0, 'image_type' => 'main'],
            ['url' => '/storage/images/leather-jacket-black-2-back.jpg', 'alt_text' => 'Black Variant Back', 'sort_order' => 1, 'image_type' => 'main'],
            // 2 detailed images (desktop)
            ['url' => '/storage/images/leather-jacket-black-3-front.jpg', 'alt_text' => 'Detail View 1', 'sort_order' => 2, 'image_type' => 'detailed', 'is_mobile' => false],
            ['url' => $selectedBlogImages2[0], 'alt_text' => 'Detail View 2', 'sort_order' => 3, 'image_type' => 'detailed', 'is_mobile' => false],
            // 2 detailed images (mobile)
            ['url' => $selectedMobileImages2[0], 'alt_text' => 'Mobile Detail 1', 'sort_order' => 4, 'image_type' => 'detailed', 'is_mobile' => true],
            ['url' => $selectedMobileImages2[1], 'alt_text' => 'Mobile Detail 2', 'sort_order' => 5, 'image_type' => 'detailed', 'is_mobile' => true],
            // 2 styling images
            ['url' => '/storage/images/leather-jacket-woman-black-1-2.jpeg', 'alt_text' => 'Styling 1', 'sort_order' => 6, 'image_type' => 'styling'],
            ['url' => '/storage/images/leather-jacket-woman-black-3-1.jpeg', 'alt_text' => 'Styling 2', 'sort_order' => 7, 'image_type' => 'styling'],
        ];
        
        $variant2a->images()->createMany($blackVariantImages2);
        $variant2b->images()->createMany($blackVariantImages2);

    // TODO: Implement createMultiColorVariants or manually seed variants for $product2
    // $this->createMultiColorVariants($product2, [...]);

        // Product 3: Vintage Black Leather Jacket (Style 3)
        $product3 = Product::firstOrCreate(
            ['name' => 'Vintage Black Leather Jacket'],
            [
                'description' => 'A vintage-inspired black leather jacket with distressed details and classic biker styling. Features multiple pockets and a relaxed fit. Perfect for those who appreciate retro aesthetics with modern comfort.',
                'gender' => 'unisex',
                'category_id' => $jacketCategory->id,
            ]
        );

        $product3->images()->delete();
        $product3->images()->createMany([
            ['url' => '/storage/images/leather-jacket-black-3-front.jpg', 'alt_text' => 'Vintage Black Leather Jacket Front View', 'sort_order' => 0, 'image_type' => 'main'],
            ['url' => '/storage/images/leather-jacket-black-3-back.jpg', 'alt_text' => 'Vintage Black Leather Jacket Back View', 'sort_order' => 1, 'image_type' => 'main'],
        ]);

        // --- VARIANTS FOR PRODUCT 3 ---
        $product3->variants()->delete();
        $variant3a = ProductVariant::create([
            'product_id' => $product3->id,
            'sku' => 'VBLJ-BLK-S',
            'color' => 'Black',
            'size' => 'S',
            'price' => 289.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        $variant3b = ProductVariant::create([
            'product_id' => $product3->id,
            'sku' => 'VBLJ-BLK-M',
            'color' => 'Black',
            'size' => 'M',
            'price' => 301.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        
        // Shuffle and select 2 random blog images for desktop view
        $shuffledBlogImages3 = $blogImages;
        shuffle($shuffledBlogImages3);
        $selectedBlogImages3 = array_slice($shuffledBlogImages3, 0, 2);
        
        // Shuffle and select 2 random mobile images for mobile view
        $shuffledMobileImages3 = $mobileImages;
        shuffle($shuffledMobileImages3);
        $selectedMobileImages3 = array_slice($shuffledMobileImages3, 0, 2);
        
        // Shared images for all Black variants of Product 3
        $blackVariantImages3 = [
            // 2 main images
            ['url' => '/storage/images/leather-jacket-black-3-front.jpg', 'alt_text' => 'Black Variant Front', 'sort_order' => 0, 'image_type' => 'main'],
            ['url' => '/storage/images/leather-jacket-black-3-back.jpg', 'alt_text' => 'Black Variant Back', 'sort_order' => 1, 'image_type' => 'main'],
            // 2 detailed images (desktop)
            ['url' => '/storage/images/leather-jacket-black-4-front.jpg', 'alt_text' => 'Detail View 1', 'sort_order' => 2, 'image_type' => 'detailed', 'is_mobile' => false],
            ['url' => $selectedBlogImages3[0], 'alt_text' => 'Detail View 2', 'sort_order' => 3, 'image_type' => 'detailed', 'is_mobile' => false],
            // 2 detailed images (mobile)
            ['url' => $selectedMobileImages3[0], 'alt_text' => 'Mobile Detail 1', 'sort_order' => 4, 'image_type' => 'detailed', 'is_mobile' => true],
            ['url' => $selectedMobileImages3[1], 'alt_text' => 'Mobile Detail 2', 'sort_order' => 5, 'image_type' => 'detailed', 'is_mobile' => true],
            // 2 styling images
            ['url' => '/storage/images/leather-jacket-woman-black-2-2.jpeg', 'alt_text' => 'Styling 1', 'sort_order' => 6, 'image_type' => 'styling'],
            ['url' => '/storage/images/leather-jacket-woman-black-3-1.jpeg', 'alt_text' => 'Styling 2', 'sort_order' => 7, 'image_type' => 'styling'],
        ];
        
        $variant3a->images()->createMany($blackVariantImages3);
        $variant3b->images()->createMany($blackVariantImages3);

    // TODO: Implement createVariants or manually seed variants for $product3
    // $this->createVariants($product3, 'Black', 'VBLJ-BLK', [...], 'leather-jacket-black-3');

        // --- BROWN LEATHER JACKETS ---

        // Product 4: Premium Brown Leather Jacket (Style 1)
        $product4 = Product::firstOrCreate(
            ['name' => 'Premium Brown Leather Jacket'],
            [
                'description' => 'Handcrafted from rich brown full-grain leather with a vintage-inspired design. Features intricate stitching details and a comfortable fit that molds to your body. Perfect for those who appreciate classic American style with a modern twist.',
                'gender' => 'unisex',
                'category_id' => $jacketCategory->id,
            ]
        );

        $product4->images()->delete();
        $product4->images()->createMany([
            ['url' => '/storage/images/leather-jacket-brown-1-front.jpg', 'alt_text' => 'Premium Brown Leather Jacket Front View', 'sort_order' => 0],
            ['url' => '/storage/images/leather-jacket-brown-1-back.jpg', 'alt_text' => 'Premium Brown Leather Jacket Back View', 'sort_order' => 1],
        ]);

        // --- VARIANTS FOR PRODUCT 4 ---
        $product4->variants()->delete();
        $variant4a = ProductVariant::create([
            'product_id' => $product4->id,
            'sku' => 'PBLJ-BRN-S',
            'color' => 'Brown',
            'size' => 'S',
            'price' => 329.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        $variant4b = ProductVariant::create([
            'product_id' => $product4->id,
            'sku' => 'PBLJ-BRN-M',
            'color' => 'Brown',
            'size' => 'M',
            'price' => 339.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        // Shuffle and select 2 random blog images for desktop view
        $shuffledBlogImages2 = $blogImages;
        shuffle($shuffledBlogImages2);
        $selectedBlogImages2 = array_slice($shuffledBlogImages2, 0, 2);
        // Shuffle and select 2 random mobile images for mobile view
        $shuffledMobileImages2 = $mobileImages;
        shuffle($shuffledMobileImages2);
        $selectedMobileImages2 = array_slice($shuffledMobileImages2, 0, 2);
        // Shared images for all Black variants of Product 2 (attach to only one variant per color)
        $blackVariantImages2 = [
            // 2 main images
            ['url' => '/storage/images/leather-jacket-black-2-front.jpg', 'alt_text' => 'Black Variant Front', 'sort_order' => 0, 'image_type' => 'main'],
            ['url' => '/storage/images/leather-jacket-black-2-back.jpg', 'alt_text' => 'Black Variant Back', 'sort_order' => 1, 'image_type' => 'main'],
            // 2 detailed images (desktop)
            ['url' => '/storage/images/leather-jacket-black-3-front.jpg', 'alt_text' => 'Detail View 1', 'sort_order' => 2, 'image_type' => 'detailed', 'is_mobile' => false],
            ['url' => $selectedBlogImages2[0], 'alt_text' => 'Detail View 2', 'sort_order' => 3, 'image_type' => 'detailed', 'is_mobile' => false],
            // 2 detailed images (mobile)
            ['url' => $selectedMobileImages2[0], 'alt_text' => 'Mobile Detail 1', 'sort_order' => 4, 'image_type' => 'detailed', 'is_mobile' => true],
            ['url' => $selectedMobileImages2[1], 'alt_text' => 'Mobile Detail 2', 'sort_order' => 5, 'image_type' => 'detailed', 'is_mobile' => true],
            // 2 styling images
            ['url' => '/storage/images/leather-jacket-woman-black-1-2.jpeg', 'alt_text' => 'Styling 1', 'sort_order' => 6, 'image_type' => 'styling'],
            ['url' => '/storage/images/leather-jacket-woman-black-3-1.jpeg', 'alt_text' => 'Styling 2', 'sort_order' => 7, 'image_type' => 'styling'],
        ];
        $variant2a->images()->createMany($blackVariantImages2);
    // Do not attach images to $variant2b (same color)
    // Do not attach images to $variant4b (same color)

    // TODO: Implement createVariants or manually seed variants for $product4
    // $this->createVariants($product4, 'Brown', 'PBLJ-BRN', [...], 'leather-jacket-brown-1');

        // Product 5: Classic Brown Leather Jacket (Style 2)
        $product5 = Product::firstOrCreate(
            ['name' => 'Classic Brown Leather Jacket'],
            [
                'description' => 'A traditional brown leather jacket with timeless appeal. Features clean lines and a versatile design that works for both casual and semi-formal occasions. Made from high-quality brown leather that ages beautifully.',
                'gender' => 'unisex',
                'category_id' => $jacketCategory->id,
            ]
        );

        $product5->images()->delete();
        $product5->images()->createMany([
            ['url' => '/storage/images/leather-jacket-brown-2-front.jpg', 'alt_text' => 'Classic Brown Leather Jacket Front View', 'sort_order' => 0],
            ['url' => '/storage/images/leather-jacket-brown-2-back.jpg', 'alt_text' => 'Classic Brown Leather Jacket Back View', 'sort_order' => 1],
        ]);

        // --- VARIANTS FOR PRODUCT 5 ---
        $product5->variants()->delete();
        $variant5a = ProductVariant::create([
            'product_id' => $product5->id,
            'sku' => 'CBLJ2-BRN-S',
            'color' => 'Brown',
            'size' => 'S',
            'price' => 299.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        $variant5b = ProductVariant::create([
            'product_id' => $product5->id,
            'sku' => 'CBLJ2-BRN-M',
            'color' => 'Brown',
            'size' => 'M',
            'price' => 309.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        // Shuffle and select 2 random blog images for desktop view
        $shuffledBlogImages4 = $blogImages;
        shuffle($shuffledBlogImages4);
        $selectedBlogImages4 = array_slice($shuffledBlogImages4, 0, 2);
        // Shuffle and select 2 random mobile images for mobile view
        $shuffledMobileImages4 = $mobileImages;
        shuffle($shuffledMobileImages4);
        $selectedMobileImages4 = array_slice($shuffledMobileImages4, 0, 2);
        // Shared images for all Brown variants (attach to only one variant per color)
        $brownVariantImages = [
            // 2 main images
            ['url' => '/storage/images/leather-jacket-brown-1-front.jpg', 'alt_text' => 'Brown Variant Front', 'sort_order' => 0, 'image_type' => 'main'],
            ['url' => '/storage/images/leather-jacket-brown-1-back.jpg', 'alt_text' => 'Brown Variant Back', 'sort_order' => 1, 'image_type' => 'main'],
            // 2 detailed images (desktop)
            ['url' => $selectedBlogImages4[0], 'alt_text' => 'Detail View 1', 'sort_order' => 2, 'image_type' => 'detailed', 'is_mobile' => false],
            ['url' => $selectedBlogImages4[1], 'alt_text' => 'Detail View 2', 'sort_order' => 3, 'image_type' => 'detailed', 'is_mobile' => false],
            // 2 detailed images (mobile)
            ['url' => $selectedMobileImages4[0], 'alt_text' => 'Mobile Detail 1', 'sort_order' => 4, 'image_type' => 'detailed', 'is_mobile' => true],
            ['url' => $selectedMobileImages4[1], 'alt_text' => 'Mobile Detail 2', 'sort_order' => 5, 'image_type' => 'detailed', 'is_mobile' => true],
            // 2 styling images
            ['url' => '/storage/images/leather-jacket-brown-1-front.jpg', 'alt_text' => 'Styling 1', 'sort_order' => 6, 'image_type' => 'styling'],
            ['url' => '/storage/images/leather-jacket-brown-1-back.jpg', 'alt_text' => 'Styling 2', 'sort_order' => 7, 'image_type' => 'styling'],
        ];
        $variant4a->images()->createMany($brownVariantImages);
    // Do not attach images to $variant4b or $variant5b (same color)

    // TODO: Implement createVariants or manually seed variants for $product5
    // $this->createVariants($product5, 'Brown', 'CBLJ2-BRN', [...], 'leather-jacket-brown-2');

        // --- DARK BROWN LEATHER JACKET ---

        // Product 6: Dark Brown Leather Jacket
        $product6 = Product::firstOrCreate(
            ['name' => 'Dark Brown Leather Jacket'],
            [
                'description' => 'A sophisticated dark brown leather jacket with a rich, deep color. Features premium leather construction and a refined design. Perfect for those who want a more formal leather jacket option.',
                'gender' => 'unisex',
                'category_id' => $jacketCategory->id,
            ]
        );

        $product6->images()->delete();
        $product6->images()->createMany([
            ['url' => '/storage/images/leather-jacket-dark-brown-1-front.jpg', 'alt_text' => 'Dark Brown Leather Jacket Front View', 'sort_order' => 0],
            ['url' => '/storage/images/leather-jacket-dark-brown-1-back.jpg', 'alt_text' => 'Dark Brown Leather Jacket Back View', 'sort_order' => 1],
        ]);

        // --- VARIANTS FOR PRODUCT 6 ---
        $product6->variants()->delete();
        $variant6a = ProductVariant::create([
            'product_id' => $product6->id,
            'sku' => 'DBLJ-DBRN-S',
            'color' => 'Dark Brown',
            'size' => 'S',
            'price' => 309.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        $variant6b = ProductVariant::create([
            'product_id' => $product6->id,
            'sku' => 'DBLJ-DBRN-M',
            'color' => 'Dark Brown',
            'size' => 'M',
            'price' => 319.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        
        // Shuffle and select 2 random blog images for desktop view
        $shuffledBlogImages6 = $blogImages;
        shuffle($shuffledBlogImages6);
        $selectedBlogImages6 = array_slice($shuffledBlogImages6, 0, 2);
        
        // Shuffle and select 2 random mobile images for mobile view
        $shuffledMobileImages6 = $mobileImages;
        shuffle($shuffledMobileImages6);
        // Shuffle and select 2 random blog images for desktop view
        $shuffledBlogImages5 = $blogImages;
        shuffle($shuffledBlogImages5);
        $selectedBlogImages5 = array_slice($shuffledBlogImages5, 0, 2);
        // Shuffle and select 2 random mobile images for mobile view
        $shuffledMobileImages5 = $mobileImages;
        shuffle($shuffledMobileImages5);
        $selectedMobileImages5 = array_slice($shuffledMobileImages5, 0, 2);
        // Shared images for all Brown variants of Product 5 (attach to only one variant per color)
        $brownVariantImages2 = [
            // 2 main images
            ['url' => '/storage/images/leather-jacket-brown-2-front.jpg', 'alt_text' => 'Brown Variant Front', 'sort_order' => 0, 'image_type' => 'main'],
            ['url' => '/storage/images/leather-jacket-brown-2-back.jpg', 'alt_text' => 'Brown Variant Back', 'sort_order' => 1, 'image_type' => 'main'],
            // 2 detailed images (desktop)
            ['url' => $selectedBlogImages5[0], 'alt_text' => 'Detail View 1', 'sort_order' => 2, 'image_type' => 'detailed', 'is_mobile' => false],
            ['url' => $selectedBlogImages5[1], 'alt_text' => 'Detail View 2', 'sort_order' => 3, 'image_type' => 'detailed', 'is_mobile' => false],
            // 2 detailed images (mobile)
            ['url' => $selectedMobileImages5[0], 'alt_text' => 'Mobile Detail 1', 'sort_order' => 4, 'image_type' => 'detailed', 'is_mobile' => true],
            ['url' => $selectedMobileImages5[1], 'alt_text' => 'Mobile Detail 2', 'sort_order' => 5, 'image_type' => 'detailed', 'is_mobile' => true],
            // 2 styling images
            ['url' => '/storage/images/leather-jacket-brown-2-front.jpg', 'alt_text' => 'Styling 1', 'sort_order' => 6, 'image_type' => 'styling'],
            ['url' => '/storage/images/leather-jacket-brown-2-back.jpg', 'alt_text' => 'Styling 2', 'sort_order' => 7, 'image_type' => 'styling'],
        ];
        $variant5a->images()->createMany($brownVariantImages2);
    // Do not attach images to $variant5b (same color)
    // Do not attach images to $variant4b or $variant5b (same color)
        // Product 7: Burgundy Leather Jacket (Style 1)
        $product7 = Product::firstOrCreate(
            ['name' => 'Burgundy Leather Jacket'],
            [
                'description' => 'A bold and distinctive burgundy leather jacket that makes a statement. Crafted from premium leather with a rich, deep color that stands out from the crowd. Features modern design elements and exceptional craftsmanship.',
                'gender' => 'unisex',
                'category_id' => $jacketCategory->id,
            ]
        );

        $product7->images()->delete();
        $product7->images()->createMany([
            ['url' => '/storage/images/leather-jacket-burgundy-2-front.jpg', 'alt_text' => 'Burgundy Leather Jacket Front View', 'sort_order' => 0],
            ['url' => '/storage/images/leather-jacket-burgundy-2-back.jpg', 'alt_text' => 'Burgundy Leather Jacket Back View', 'sort_order' => 1],
        ]);

    // TODO: Implement createVariants or manually seed variants for $product7
    // $this->createVariants($product7, 'Burgundy', 'BGLJ-BUR', [...], 'leather-jacket-burgundy-1');

        // Product 8: Premium Burgundy Leather Jacket (Style 2)
        $product8 = Product::firstOrCreate(
            ['name' => 'Premium Burgundy Leather Jacket'],
            [
                'description' => 'An elegant burgundy leather jacket with sophisticated styling. Features premium leather construction and refined details. Perfect for those who want a unique color with exceptional quality and style.',
                'gender' => 'unisex',
                'category_id' => $jacketCategory->id,
            ]
        );

        $product8->images()->delete();
        $product8->images()->createMany([
            ['url' => '/storage/images/leather-jacket-burugndy-1-front.jpg', 'alt_text' => 'Premium Burgundy Leather Jacket Front View', 'sort_order' => 0],
            ['url' => '/storage/images/leather-jacket-burugndy-1-back.jpg', 'alt_text' => 'Premium Burgundy Leather Jacket Back View', 'sort_order' => 1],
        ]);

    // TODO: Implement createVariants or manually seed variants for $product8
    // $this->createVariants($product8, 'Burgundy', 'PBLJ-BUR', [...], 'leather-jacket-burgundy-2');

        // --- BLUE LEATHER JACKET ---

        // Product 9: Navy Blue Leather Jacket
        $product9 = Product::firstOrCreate(
            ['name' => 'Navy Blue Leather Jacket'],
            [
                'description' => 'A sophisticated navy blue leather jacket that combines elegance with durability. Made from high-quality leather with a smooth finish and contemporary styling. Perfect for both casual and semi-formal occasions.',
                'gender' => 'unisex',
                'category_id' => $jacketCategory->id,
            ]
        );

        $product9->images()->delete();
        $product9->images()->createMany([
            ['url' => '/storage/images/leather-jacket-blue-1-front.jpg', 'alt_text' => 'Navy Blue Leather Jacket Front View', 'sort_order' => 0],
            ['url' => '/storage/images/leather-jacket-blue-1-back.jpg', 'alt_text' => 'Navy Blue Leather Jacket Back View', 'sort_order' => 1],
        ]);

        // --- VARIANTS FOR PRODUCT 9 ---
        $product9->variants()->delete();
        $variant9a = ProductVariant::create([
            'product_id' => $product9->id,
            'sku' => 'NBLJ-BLU-S',
            'color' => 'Navy Blue',
            'size' => 'S',
            'price' => 279.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        $variant9b = ProductVariant::create([
            'product_id' => $product9->id,
            'sku' => 'NBLJ-BLU-M',
            'color' => 'Navy Blue',
            'size' => 'M',
            'price' => 289.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        // Shuffle and select 2 random blog images for desktop view
        $shuffledBlogImages6 = $blogImages;
        shuffle($shuffledBlogImages6);
        $selectedBlogImages6 = array_slice($shuffledBlogImages6, 0, 2);
        // Shuffle and select 2 random mobile images for mobile view
        $shuffledMobileImages6 = $mobileImages;
        shuffle($shuffledMobileImages6);
        $selectedMobileImages6 = array_slice($shuffledMobileImages6, 0, 2);
        // Shared images for all Dark Brown variants (attach to only one variant per color)
        $darkBrownVariantImages = [
            // 2 main images
            ['url' => '/storage/images/leather-jacket-dark-brown-1-front.jpg', 'alt_text' => 'Dark Brown Variant Front', 'sort_order' => 0, 'image_type' => 'main'],
            ['url' => '/storage/images/leather-jacket-dark-brown-1-back.jpg', 'alt_text' => 'Dark Brown Variant Back', 'sort_order' => 1, 'image_type' => 'main'],
            // 2 detailed images (desktop)
            ['url' => $selectedBlogImages6[0], 'alt_text' => 'Detail View 1', 'sort_order' => 2, 'image_type' => 'detailed', 'is_mobile' => false],
            ['url' => $selectedBlogImages6[1], 'alt_text' => 'Detail View 2', 'sort_order' => 3, 'image_type' => 'detailed', 'is_mobile' => false],
            // 2 detailed images (mobile)
            ['url' => $selectedMobileImages6[0], 'alt_text' => 'Mobile Detail 1', 'sort_order' => 4, 'image_type' => 'detailed', 'is_mobile' => true],
            ['url' => $selectedMobileImages6[1], 'alt_text' => 'Mobile Detail 2', 'sort_order' => 5, 'image_type' => 'detailed', 'is_mobile' => true],
            // 2 styling images
            ['url' => '/storage/images/leather-jacket-dark-brown-1-front.jpg', 'alt_text' => 'Styling 1', 'sort_order' => 6, 'image_type' => 'styling'],
            ['url' => '/storage/images/leather-jacket-dark-brown-1-back.jpg', 'alt_text' => 'Styling 2', 'sort_order' => 7, 'image_type' => 'styling'],
        ];
        $variant6a->images()->createMany($darkBrownVariantImages);
        // Do not attach images to $variant6b (same color)
    // Do not attach images to $variant9b (same color)

        // $this->createVariants($product9, 'Navy Blue', 'NBLJ-BLU', [...], 'leather-jacket-blue-1');

        // --- OLIVE GREEN LEATHER JACKET ---

        // Product 10: Olive Green Leather Jacket
        $product10 = Product::firstOrCreate(
            ['name' => 'Olive Green Leather Jacket'],
            [
                'description' => 'A rugged olive green leather jacket inspired by military and aviation heritage. Built to last with reinforced stitching and durable hardware. The earthy green color pairs perfectly with casual and outdoor wear.',
                'gender' => 'unisex',
                'category_id' => $jacketCategory->id,
            ]
        );

        $product10->images()->delete();
        $product10->images()->createMany([
            ['url' => '/storage/images/leather-jacket-olive-1-front.jpg', 'alt_text' => 'Olive Green Leather Jacket Front View', 'sort_order' => 0],
            ['url' => '/storage/images/leather-jacket-olive-1-back.jpg', 'alt_text' => 'Olive Green Leather Jacket Back View', 'sort_order' => 1],
        ]);

        // --- VARIANTS FOR PRODUCT 10 ---
        $product10->variants()->delete();
        $variant10a = ProductVariant::create([
            'product_id' => $product10->id,
            'sku' => 'OGLJ-OLV-S',
            'color' => 'Olive Green',
            'size' => 'S',
            'price' => 289.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        $variant10b = ProductVariant::create([
            'product_id' => $product10->id,
            'sku' => 'OGLJ-OLV-M',
            'color' => 'Olive Green',
            'size' => 'M',
            'price' => 299.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        
        // Shuffle and select 2 random blog images for desktop view
        $shuffledBlogImages10 = $blogImages;
        shuffle($shuffledBlogImages10);
        $selectedBlogImages10 = array_slice($shuffledBlogImages10, 0, 2);
        
        // Shuffle and select 2 random mobile images for mobile view
        $shuffledMobileImages10 = $mobileImages;
        shuffle($shuffledMobileImages10);
        $selectedMobileImages10 = array_slice($shuffledMobileImages10, 0, 2);
        
        // Shared images for all Olive Green variants
        $oliveVariantImages = [
            // 2 main images
            ['url' => '/storage/images/leather-jacket-olive-1-front.jpg', 'alt_text' => 'Olive Green Variant Front', 'sort_order' => 0, 'image_type' => 'main'],
            ['url' => '/storage/images/leather-jacket-olive-1-back.jpg', 'alt_text' => 'Olive Green Variant Back', 'sort_order' => 1, 'image_type' => 'main'],
            // 2 detailed images (desktop)
            ['url' => $selectedBlogImages10[0], 'alt_text' => 'Detail View 1', 'sort_order' => 2, 'image_type' => 'detailed', 'is_mobile' => false],
            ['url' => $selectedBlogImages10[1], 'alt_text' => 'Detail View 2', 'sort_order' => 3, 'image_type' => 'detailed', 'is_mobile' => false],
            // 2 detailed images (mobile)
            ['url' => $selectedMobileImages10[0], 'alt_text' => 'Mobile Detail 1', 'sort_order' => 4, 'image_type' => 'detailed', 'is_mobile' => true],
            ['url' => $selectedMobileImages10[1], 'alt_text' => 'Mobile Detail 2', 'sort_order' => 5, 'image_type' => 'detailed', 'is_mobile' => true],
            // 2 styling images
            ['url' => '/storage/images/leather-jacket-olive-1-front.jpg', 'alt_text' => 'Styling 1', 'sort_order' => 6, 'image_type' => 'styling'],
            ['url' => '/storage/images/leather-jacket-olive-1-back.jpg', 'alt_text' => 'Styling 2', 'sort_order' => 7, 'image_type' => 'styling'],
        ];
        
        $variant10a->images()->createMany($oliveVariantImages);
    // Do not attach images to $variant10b (same color)

        // $this->createVariants($product10, 'Olive Green', 'OGLJ-OLV', [...], 'leather-jacket-olive-1');

        // --- WHITE LEATHER JACKET ---

        // Product 11: White Leather Jacket
        $product11 = Product::firstOrCreate(
            ['name' => 'White Leather Jacket'],
            [
                'description' => 'A striking white leather jacket that makes a bold fashion statement. Crafted from premium white leather with a clean, minimalist design. Perfect for those who want to stand out with a unique and elegant look.',
                'gender' => 'unisex',
                'category_id' => $jacketCategory->id,
            ]
        );

        $product11->images()->delete();
        $product11->images()->createMany([
            ['url' => '/storage/images/leather-jacket-white-1-front.jpg', 'alt_text' => 'White Leather Jacket Front View', 'sort_order' => 0],
            ['url' => '/storage/images/leather-jacket-white-1-back.jpg', 'alt_text' => 'White Leather Jacket Back View', 'sort_order' => 1],
        ]);

        // --- VARIANTS FOR PRODUCT 11 ---
        $product11->variants()->delete();
        $variant11a = ProductVariant::create([
            'product_id' => $product11->id,
            'sku' => 'WLJ-WHT-S',
            'color' => 'White',
            'size' => 'S',
            'price' => 299.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        $variant11b = ProductVariant::create([
            'product_id' => $product11->id,
            'sku' => 'WLJ-WHT-M',
            'color' => 'White',
            'size' => 'M',
            'price' => 309.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        
        // Shuffle and select 2 random blog images for desktop view
        $shuffledBlogImages11 = $blogImages;
        shuffle($shuffledBlogImages11);
        $selectedBlogImages11 = array_slice($shuffledBlogImages11, 0, 2);
        
        // Shuffle and select 2 random mobile images for mobile view
        $shuffledMobileImages11 = $mobileImages;
        shuffle($shuffledMobileImages11);
        $selectedMobileImages11 = array_slice($shuffledMobileImages11, 0, 2);
        
        // Shared images for all White variants
        $whiteVariantImages = [
            // 2 main images
            ['url' => '/storage/images/leather-jacket-white-1-front.jpg', 'alt_text' => 'White Variant Front', 'sort_order' => 0, 'image_type' => 'main'],
            ['url' => '/storage/images/leather-jacket-white-1-back.jpg', 'alt_text' => 'White Variant Back', 'sort_order' => 1, 'image_type' => 'main'],
            // 2 detailed images (desktop)
            ['url' => $selectedBlogImages11[0], 'alt_text' => 'Detail View 1', 'sort_order' => 2, 'image_type' => 'detailed', 'is_mobile' => false],
            ['url' => $selectedBlogImages11[1], 'alt_text' => 'Detail View 2', 'sort_order' => 3, 'image_type' => 'detailed', 'is_mobile' => false],
            // 2 detailed images (mobile)
            ['url' => $selectedMobileImages11[0], 'alt_text' => 'Mobile Detail 1', 'sort_order' => 4, 'image_type' => 'detailed', 'is_mobile' => true],
            ['url' => $selectedMobileImages11[1], 'alt_text' => 'Mobile Detail 2', 'sort_order' => 5, 'image_type' => 'detailed', 'is_mobile' => true],
            // 2 styling images
            ['url' => '/storage/images/leather-jacket-white-1-front.jpg', 'alt_text' => 'Styling 1', 'sort_order' => 6, 'image_type' => 'styling'],
            ['url' => '/storage/images/leather-jacket-white-1-back.jpg', 'alt_text' => 'Styling 2', 'sort_order' => 7, 'image_type' => 'styling'],
        ];
        
        $variant11a->images()->createMany($whiteVariantImages);
    // Do not attach images to $variant11b (same color)

        // $this->createVariants($product11, 'White', 'WLJ-WHT', [...], 'leather-jacket-white-1');

        // --- WOMEN'S LEATHER JACKETS ---

        // Product 12: Women's Classic Black Leather Jacket (Style 1)
        $product12 = Product::firstOrCreate(
            ['name' => 'Women\'s Classic Black Leather Jacket'],
            [
                'description' => 'A sophisticated black leather jacket designed specifically for women. Features a tailored fit with feminine details and premium black leather construction. Perfect for the modern woman who values both style and quality.',
                'gender' => 'female',
                'category_id' => $jacketCategory->id,
            ]
        );

        $product12->images()->delete();
        $product12->images()->createMany([
            ['url' => '/storage/images/leather-jacket-woman-black-1-1.jpeg', 'alt_text' => 'Women\'s Classic Black Leather Jacket View 1', 'sort_order' => 0],
            ['url' => '/storage/images/leather-jacket-woman-black-1-2.jpeg', 'alt_text' => 'Women\'s Classic Black Leather Jacket View 2', 'sort_order' => 1],
            ['url' => '/storage/images/leather-jacket-woman-black-1-3.jpeg', 'alt_text' => 'Women\'s Classic Black Leather Jacket View 3', 'sort_order' => 2],
        ]);

        // --- VARIANTS FOR PRODUCT 12 ---
        $product12->variants()->delete();
        $variant12a = ProductVariant::create([
            'product_id' => $product12->id,
            'sku' => 'WCBLJ-BLK-S',
            'color' => 'Black',
            'size' => 'S',
            'price' => 279.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        $variant12b = ProductVariant::create([
            'product_id' => $product12->id,
            'sku' => 'WCBLJ-BLK-M',
            'color' => 'Black',
            'size' => 'M',
            'price' => 289.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        
        // Shuffle and select 2 random blog images for desktop view
        $shuffledBlogImages12 = $blogImages;
        shuffle($shuffledBlogImages12);
        $selectedBlogImages12 = array_slice($shuffledBlogImages12, 0, 2);
        
        // Shuffle and select 2 random mobile images for mobile view
        $shuffledMobileImages12 = $mobileImages;
        shuffle($shuffledMobileImages12);
        $selectedMobileImages12 = array_slice($shuffledMobileImages12, 0, 2);
        
        // Shared images for all Black variants of Product 12
        $blackVariantImages12 = [
            // 2 main images
            ['url' => '/storage/images/leather-jacket-woman-black-1-1.jpeg', 'alt_text' => 'Black Variant View 1', 'sort_order' => 0, 'image_type' => 'main'],
            ['url' => '/storage/images/leather-jacket-woman-black-1-2.jpeg', 'alt_text' => 'Black Variant View 2', 'sort_order' => 1, 'image_type' => 'main'],
            // 2 detailed images (desktop)
            ['url' => $selectedBlogImages12[0], 'alt_text' => 'Detail View 1', 'sort_order' => 2, 'image_type' => 'detailed', 'is_mobile' => false],
            ['url' => $selectedBlogImages12[1], 'alt_text' => 'Detail View 2', 'sort_order' => 3, 'image_type' => 'detailed', 'is_mobile' => false],
            // 2 detailed images (mobile)
            ['url' => $selectedMobileImages12[0], 'alt_text' => 'Mobile Detail 1', 'sort_order' => 4, 'image_type' => 'detailed', 'is_mobile' => true],
            ['url' => $selectedMobileImages12[1], 'alt_text' => 'Mobile Detail 2', 'sort_order' => 5, 'image_type' => 'detailed', 'is_mobile' => true],
            // 2 styling images
            ['url' => '/storage/images/leather-jacket-woman-black-1-1.jpeg', 'alt_text' => 'Styling 1', 'sort_order' => 6, 'image_type' => 'styling'],
            ['url' => '/storage/images/leather-jacket-woman-black-1-2.jpeg', 'alt_text' => 'Styling 2', 'sort_order' => 7, 'image_type' => 'styling'],
        ];
        
        $variant12a->images()->createMany($blackVariantImages12);
        $variant12b->images()->createMany($blackVariantImages12);

        // $this->createVariants($product12, 'Black', 'WCBLJ-BLK', [...], 'leather-jacket-woman-black-1');

        // Product 13: Women's Modern Black Leather Jacket (Style 2)
        $product13 = Product::firstOrCreate(
            ['name' => 'Women\'s Modern Black Leather Jacket'],
            [
                'description' => 'A contemporary black leather jacket with sleek design elements tailored for women. Features modern cuts and premium leather quality. Ideal for the fashion-forward woman seeking a statement piece.',
                'gender' => 'female',
                'category_id' => $jacketCategory->id,
            ]
        );

        $product13->images()->delete();
        $product13->images()->createMany([
            ['url' => '/storage/images/leather-jacket-woman-black-2-1.jpeg', 'alt_text' => 'Women\'s Modern Black Leather Jacket View 1', 'sort_order' => 0],
            ['url' => '/storage/images/leather-jacket-woman-black-2-2.jpeg', 'alt_text' => 'Women\'s Modern Black Leather Jacket View 2', 'sort_order' => 1],
            ['url' => '/storage/images/leather-jacket-woman-black-2-3.jpeg', 'alt_text' => 'Women\'s Modern Black Leather Jacket View 3', 'sort_order' => 2],
        ]);

        // --- VARIANTS FOR PRODUCT 13 ---
        $product13->variants()->delete();
        $variant13a = ProductVariant::create([
            'product_id' => $product13->id,
            'sku' => 'WMBLJ-BLK-S',
            'color' => 'Black',
            'size' => 'S',
            'price' => 299.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        $variant13b = ProductVariant::create([
            'product_id' => $product13->id,
            'sku' => 'WMBLJ-BLK-M',
            'color' => 'Black',
            'size' => 'M',
            'price' => 309.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        
        // Shuffle and select 2 random blog images for desktop view
        $shuffledBlogImages13 = $blogImages;
        shuffle($shuffledBlogImages13);
        $selectedBlogImages13 = array_slice($shuffledBlogImages13, 0, 2);
        
        // Shuffle and select 2 random mobile images for mobile view
        $shuffledMobileImages13 = $mobileImages;
        shuffle($shuffledMobileImages13);
        $selectedMobileImages13 = array_slice($shuffledMobileImages13, 0, 2);
        
        // Shared images for all Black variants of Product 13
        $blackVariantImages13 = [
            // 2 main images
            ['url' => '/storage/images/leather-jacket-woman-black-2-1.jpeg', 'alt_text' => 'Black Variant View 1', 'sort_order' => 0, 'image_type' => 'main'],
            ['url' => '/storage/images/leather-jacket-woman-black-2-2.jpeg', 'alt_text' => 'Black Variant View 2', 'sort_order' => 1, 'image_type' => 'main'],
            // 2 detailed images (desktop)
            ['url' => $selectedBlogImages13[0], 'alt_text' => 'Detail View 1', 'sort_order' => 2, 'image_type' => 'detailed', 'is_mobile' => false],
            ['url' => $selectedBlogImages13[1], 'alt_text' => 'Detail View 2', 'sort_order' => 3, 'image_type' => 'detailed', 'is_mobile' => false],
            // 2 detailed images (mobile)
            ['url' => $selectedMobileImages13[0], 'alt_text' => 'Mobile Detail 1', 'sort_order' => 4, 'image_type' => 'detailed', 'is_mobile' => true],
            ['url' => $selectedMobileImages13[1], 'alt_text' => 'Mobile Detail 2', 'sort_order' => 5, 'image_type' => 'detailed', 'is_mobile' => true],
            // 2 styling images
            ['url' => '/storage/images/leather-jacket-woman-black-2-1.jpeg', 'alt_text' => 'Styling 1', 'sort_order' => 6, 'image_type' => 'styling'],
            ['url' => '/storage/images/leather-jacket-woman-black-2-2.jpeg', 'alt_text' => 'Styling 2', 'sort_order' => 7, 'image_type' => 'styling'],
        ];
        
        $variant13a->images()->createMany($blackVariantImages13);
        $variant13b->images()->createMany($blackVariantImages13);

    // TODO: Implement createVariants or manually seed variants for $product13
    // $this->createVariants($product13, 'Black', 'WMBLJ-BLK', [...], 'leather-jacket-woman-black-2');

        // Product 14: Women's Elegant Black Leather Jacket (Style 3)
        $product14 = Product::firstOrCreate(
            ['name' => 'Women\'s Elegant Black Leather Jacket'],
            [
                'description' => 'An elegant black leather jacket with refined styling for women. Features sophisticated design elements and premium leather construction. Perfect for the woman who appreciates timeless elegance and quality craftsmanship.',
                'gender' => 'female',
                'category_id' => $jacketCategory->id,
            ]
        );

        $product14->images()->delete();
        $product14->images()->createMany([
            ['url' => '/storage/images/leather-jacket-woman-black-3-1.jpeg', 'alt_text' => 'Women\'s Elegant Black Leather Jacket View 1', 'sort_order' => 0],
            ['url' => '/storage/images/leather-jacket-woman-black-3-2.jpeg', 'alt_text' => 'Women\'s Elegant Black Leather Jacket View 2', 'sort_order' => 1],
            ['url' => '/storage/images/leather-jacket-woman-black-3-3.jpeg', 'alt_text' => 'Women\'s Elegant Black Leather Jacket View 3', 'sort_order' => 2],
        ]);

        // --- VARIANTS FOR PRODUCT 14 ---
        $product14->variants()->delete();
        $variant14a = ProductVariant::create([
            'product_id' => $product14->id,
            'sku' => 'WEBLJ-BLK-S',
            'color' => 'Black',
            'size' => 'S',
            'price' => 319.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        $variant14b = ProductVariant::create([
            'product_id' => $product14->id,
            'sku' => 'WEBLJ-BLK-M',
            'color' => 'Black',
            'size' => 'M',
            'price' => 329.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        
        // Shuffle and select 2 random blog images for desktop view
        $shuffledBlogImages14 = $blogImages;
        shuffle($shuffledBlogImages14);
        $selectedBlogImages14 = array_slice($shuffledBlogImages14, 0, 2);
        
        // Shuffle and select 2 random mobile images for mobile view
        $shuffledMobileImages14 = $mobileImages;
        shuffle($shuffledMobileImages14);
        $selectedMobileImages14 = array_slice($shuffledMobileImages14, 0, 2);
        
        // Shared images for all Black variants of Product 14
        $blackVariantImages14 = [
            // 2 main images
            ['url' => '/storage/images/leather-jacket-woman-black-3-1.jpeg', 'alt_text' => 'Black Variant View 1', 'sort_order' => 0, 'image_type' => 'main'],
            ['url' => '/storage/images/leather-jacket-woman-black-3-2.jpeg', 'alt_text' => 'Black Variant View 2', 'sort_order' => 1, 'image_type' => 'main'],
            // 2 detailed images (desktop)
            ['url' => $selectedBlogImages14[0], 'alt_text' => 'Detail View 1', 'sort_order' => 2, 'image_type' => 'detailed', 'is_mobile' => false],
            ['url' => $selectedBlogImages14[1], 'alt_text' => 'Detail View 2', 'sort_order' => 3, 'image_type' => 'detailed', 'is_mobile' => false],
            // 2 detailed images (mobile)
            ['url' => $selectedMobileImages14[0], 'alt_text' => 'Mobile Detail 1', 'sort_order' => 4, 'image_type' => 'detailed', 'is_mobile' => true],
            ['url' => $selectedMobileImages14[1], 'alt_text' => 'Mobile Detail 2', 'sort_order' => 5, 'image_type' => 'detailed', 'is_mobile' => true],
            // 2 styling images
            ['url' => '/storage/images/leather-jacket-woman-black-3-1.jpeg', 'alt_text' => 'Styling 1', 'sort_order' => 6, 'image_type' => 'styling'],
            ['url' => '/storage/images/leather-jacket-woman-black-3-2.jpeg', 'alt_text' => 'Styling 2', 'sort_order' => 7, 'image_type' => 'styling'],
        ];
        
        $variant14a->images()->createMany($blackVariantImages14);
        $variant14b->images()->createMany($blackVariantImages14);

    // TODO: Implement createVariants or manually seed variants for $product14
    // $this->createVariants($product14, 'Black', 'WEBLJ-BLK', [...], 'leather-jacket-woman-black-3');

        // Product 15: Women's Premium Black Leather Jacket (Style 4)
        $product15 = Product::firstOrCreate(
            ['name' => 'Women\'s Premium Black Leather Jacket'],
            [
                'description' => 'A premium black leather jacket with luxurious details designed for women. Features exceptional craftsmanship and the finest leather quality. The ultimate statement piece for the discerning woman who demands the best.',
                'gender' => 'female',
                'category_id' => $jacketCategory->id,
            ]
        );

        $product15->images()->delete();
        $product15->images()->createMany([
            ['url' => '/storage/images/leather-jacket-woman-black-4-1.jpeg', 'alt_text' => 'Women\'s Premium Black Leather Jacket View 1', 'sort_order' => 0],
            ['url' => '/storage/images/leather-jacket-woman-black-4-2.jpeg', 'alt_text' => 'Women\'s Premium Black Leather Jacket View 2', 'sort_order' => 1],
            ['url' => '/storage/images/leather-jacket-woman-black-4-3.jpeg', 'alt_text' => 'Women\'s Premium Black Leather Jacket View 3', 'sort_order' => 2],
            ['url' => '/storage/images/leather-jacket-woman-black-4-4jpeg.jpeg', 'alt_text' => 'Women\'s Premium Black Leather Jacket View 4', 'sort_order' => 3],
            ['url' => '/storage/images/leather-jacket-woman-black-4-5.jpeg', 'alt_text' => 'Women\'s Premium Black Leather Jacket View 5', 'sort_order' => 4],
            ['url' => '/storage/images/leather-jacket-woman-black-4-6.jpeg', 'alt_text' => 'Women\'s Premium Black Leather Jacket View 6', 'sort_order' => 5],
        ]);

        // --- VARIANTS FOR PRODUCT 15 ---
        $product15->variants()->delete();
        $variant15a = ProductVariant::create([
            'product_id' => $product15->id,
            'sku' => 'WPBLJ-BLK-S',
            'color' => 'Black',
            'size' => 'S',
            'price' => 339.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        $variant15b = ProductVariant::create([
            'product_id' => $product15->id,
            'sku' => 'WPBLJ-BLK-M',
            'color' => 'Black',
            'size' => 'M',
            'price' => 349.99,
            
            'video_url' => '/storage/videos/jacket.mp4',
        ]);
        
        // Shuffle and select 2 random blog images for desktop view
        $shuffledBlogImages15 = $blogImages;
        shuffle($shuffledBlogImages15);
        $selectedBlogImages15 = array_slice($shuffledBlogImages15, 0, 2);
        
        // Shuffle and select 2 random mobile images for mobile view
        $shuffledMobileImages15 = $mobileImages;
        shuffle($shuffledMobileImages15);
        $selectedMobileImages15 = array_slice($shuffledMobileImages15, 0, 2);
        
        // Shared images for all Black variants of Product 15
        $blackVariantImages15 = [
            // 2 main images
            ['url' => '/storage/images/leather-jacket-woman-black-4-1.jpeg', 'alt_text' => 'Black Variant View 1', 'sort_order' => 0, 'image_type' => 'main'],
            ['url' => '/storage/images/leather-jacket-woman-black-4-2.jpeg', 'alt_text' => 'Black Variant View 2', 'sort_order' => 1, 'image_type' => 'main'],
            // 2 detailed images (desktop)
            ['url' => $selectedBlogImages15[0], 'alt_text' => 'Detail View 1', 'sort_order' => 2, 'image_type' => 'detailed', 'is_mobile' => false],
            ['url' => $selectedBlogImages15[1], 'alt_text' => 'Detail View 2', 'sort_order' => 3, 'image_type' => 'detailed', 'is_mobile' => false],
            // 2 detailed images (mobile)
            ['url' => $selectedMobileImages15[0], 'alt_text' => 'Mobile Detail 1', 'sort_order' => 4, 'image_type' => 'detailed', 'is_mobile' => true],
            ['url' => $selectedMobileImages15[1], 'alt_text' => 'Mobile Detail 2', 'sort_order' => 5, 'image_type' => 'detailed', 'is_mobile' => true],
            // 2 styling images
            ['url' => '/storage/images/leather-jacket-woman-black-4-1.jpeg', 'alt_text' => 'Styling 1', 'sort_order' => 6, 'image_type' => 'styling'],
            ['url' => '/storage/images/leather-jacket-woman-black-4-2.jpeg', 'alt_text' => 'Styling 2', 'sort_order' => 7, 'image_type' => 'styling'],
        ];
        
        $variant15a->images()->createMany($blackVariantImages15);
        $variant15b->images()->createMany($blackVariantImages15);

    // TODO: Implement createVariants or manually seed variants for $product15
    // $this->createVariants($product15, 'Black', 'WPBLJ-BLK', [...], 'leather-jacket-woman-black-4');
    
        // Add size guide images to all products that don't have one
        $allProducts = Product::all();
        foreach ($allProducts as $product) {
            // Check if product already has a size guide image
            $hasSizeGuide = $product->images()->where('image_type', 'size_guide')->exists();
            if (!$hasSizeGuide) {
                $product->images()->create([
                    'url' => '/storage/images/size_guide.jpg',
                    'alt_text' => 'Size Guide',
                    'sort_order' => 999,
                    'image_type' => 'size_guide',
                ]);
            }
        }
    }

    // All legacy helpers removed. Only variant-centric seeding remains.
}