<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;
use App\Models\ProductReview;
use App\Models\Order;
use App\Models\OrderItem;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all products and review users
        $products = Product::all();
        $reviewUsers = User::where('role', 'customer')
            ->where('email', 'like', '%@example.com')
            ->get();

        if ($products->isEmpty() || $reviewUsers->isEmpty()) {
            $this->command->warn('No products or review users found. Skipping review creation.');
            return;
        }

        $reviewTitles = [
            'Excellent quality and craftsmanship',
            'Perfect fit and great value',
            'Love the design and comfort',
            'Highly recommend this product',
            'Outstanding customer service',
            'Beautiful piece, exceeded expectations',
            'Great attention to detail',
            'Comfortable and stylish',
            'Worth every penny',
            'Amazing quality materials',
            'Perfect for any occasion',
            'Love the color and fit',
            'Exceptional craftsmanship',
            'Better than expected',
            'Fantastic purchase'
        ];

        $reviewBodies = [
            'This product exceeded my expectations in every way. The quality is outstanding and the craftsmanship is evident in every detail. I\'ve received many compliments and would definitely recommend it to anyone looking for premium quality.',
            'Absolutely love this item! The fit is perfect and the material feels luxurious. It arrived well-packaged and exactly as described. The customer service was also excellent when I had a question about sizing.',
            'I\'m extremely satisfied with this purchase. The product is exactly as shown in the photos and the quality is top-notch. It\'s comfortable, stylish, and well-made. I\'ve already recommended it to several friends.',
            'This is one of the best purchases I\'ve made recently. The attention to detail is incredible and the product feels premium. The delivery was fast and the packaging was excellent. Highly recommend!',
            'Outstanding quality and beautiful design. This product has become a staple in my wardrobe/collection. The materials are high-end and the construction is solid. Very pleased with this purchase.',
            'I\'m impressed with the quality and craftsmanship of this item. It\'s comfortable, well-made, and exactly what I was looking for. The customer service was also very helpful. Would buy again.',
            'This product is even better in person than in the photos. The colors are vibrant, the fit is perfect, and the quality is exceptional. I\'m very happy with my purchase and would recommend it to anyone.',
            'Excellent value for money. The product is high-quality, well-constructed, and exactly as described. The delivery was prompt and the packaging was secure. Very satisfied customer.',
            'I love everything about this product! The design is beautiful, the materials are premium, and it\'s very comfortable. It\'s become one of my favorite items. Highly recommend to others.',
            'This is a fantastic product. The quality is outstanding, the fit is perfect, and it looks even better in person. The customer service was excellent and delivery was fast. Very pleased!',
            'I\'m thrilled with this purchase. The product is exactly what I was looking for - high quality, beautiful design, and excellent craftsmanship. It arrived quickly and was well-packaged.',
            'Outstanding product! The attention to detail is amazing and the quality is top-tier. It\'s comfortable, stylish, and well-made. I\'ve received many compliments and would definitely buy again.',
            'This product is worth every penny. The materials are luxurious, the construction is solid, and the design is beautiful. The customer service was also excellent. Highly recommend!',
            'I\'m very impressed with the quality and design of this item. It\'s exactly as described and the fit is perfect. The delivery was fast and the packaging was excellent. Great purchase!',
            'This is a beautiful, high-quality product that exceeded my expectations. The craftsmanship is excellent and it feels premium. I\'m very happy with my purchase and would recommend it to friends.'
        ];

        $createdReviews = 0;

        foreach ($products as $product) {
            // Track which users have already reviewed this product
            $reviewedUsers = [];
            
            // Create up to 15 reviews for each product
            $maxReviews = min(15, $reviewUsers->count());
            $attemptedReviews = 0;
            
            while (count($reviewedUsers) < $maxReviews && $attemptedReviews < $reviewUsers->count()) {
                $user = $reviewUsers->random();
                $attemptedReviews++;
                
                // Skip if user already reviewed this product
                if (in_array($user->id, $reviewedUsers)) {
                    continue;
                }
                
                // Check if review already exists
                if (ProductReview::where('user_id', $user->id)->where('product_id', $product->id)->exists()) {
                    $reviewedUsers[] = $user->id;
                    continue;
                }
                
                // Create a fake order for this user and product to make the review "verified"
                $order = Order::create([
                    'user_id' => $user->id,
                    'order_number' => 'REV-' . strtoupper(uniqid()),
                    'subtotal' => $product->variants->first()?->price ?? 100.00,
                    'tax' => 0.00,
                    'shipping' => 0.00,
                    'shipping_name' => 'Standard Shipping',
                    'shipping_email' => $user->email,
                    'shipping_city' => 'Review City',
                    'shipping_state' => 'Review State',
                    'shipping_country' => 'Review Country',
                    'shipping_postal_code' => '12345',
                    'total' => $product->variants->first()?->price ?? 100.00,
                    'status' => 'delivered',
                    'shipping_address' => json_encode([
                        'street' => '123 Review Street',
                        'city' => 'Review City',
                        'country' => 'Review Country',
                        'postal_code' => '12345'
                    ]),
                    'billing_address' => json_encode([
                        'street' => '123 Review Street',
                        'city' => 'Review City',
                        'country' => 'Review Country',
                        'postal_code' => '12345'
                    ]),
                    'created_at' => now()->subDays(rand(1, 365)),
                    'updated_at' => now()->subDays(rand(0, 30)),
                ]);

                // Create order item
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_name' => $product->name,
                    'quantity' => 1,
                    'price' => $product->variants->first()?->price ?? 100.00,
                    'subtotal' => $product->variants->first()?->price ?? 100.00,
                ]);

                // Create the review
                ProductReview::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'rating' => rand(4, 5), // Only 4-5 star reviews for positive feedback
                    'title' => $reviewTitles[array_rand($reviewTitles)],
                    'review_text' => $reviewBodies[array_rand($reviewBodies)],
                    'created_at' => $order->created_at->addDays(rand(1, 7)), // Review created 1-7 days after order
                    'updated_at' => $order->created_at->addDays(rand(1, 7)),
                    'status' => 'approved', // Set status to approved so reviews are visible
                ]);

                $reviewedUsers[] = $user->id;
                $createdReviews++;
            }
        }

        $this->command->info("Created {$createdReviews} verified reviews for " . $products->count() . " products.");
    }
}
