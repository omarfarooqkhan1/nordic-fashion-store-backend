<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReviewGeneratorService
{
    private array $reviewTemplates = [
        5 => [
            "Absolutely love this product! The quality is outstanding and exceeded my expectations.",
            "Perfect! Exactly what I was looking for. Highly recommend to anyone.",
            "Amazing quality and craftsmanship. Worth every penny!",
            "Best purchase I've made in a long time. Five stars all the way!",
            "Exceptional product! The attention to detail is remarkable.",
            "Couldn't be happier with this purchase. Top-notch quality!",
            "This is exactly as described and even better in person. Love it!",
            "Outstanding quality and beautiful design. Highly satisfied!",
        ],
        4 => [
            "Great product overall. Very satisfied with the quality.",
            "Really good quality. Would definitely recommend.",
            "Very happy with this purchase. Good value for money.",
            "Nice product, meets my expectations. Would buy again.",
            "Good quality and well-made. Happy with my purchase.",
            "Solid product. Does exactly what it's supposed to do.",
            "Pretty good! A few minor things but overall very satisfied.",
            "Quality is good and looks great. Pleased with this buy.",
        ],
    ];

    private array $firstNames = [
        'Emma', 'Liam', 'Olivia', 'Noah', 'Ava', 'Ethan', 'Sophia', 'Mason',
        'Isabella', 'William', 'Mia', 'James', 'Charlotte', 'Benjamin', 'Amelia',
        'Lucas', 'Harper', 'Henry', 'Evelyn', 'Alexander', 'Abigail', 'Michael',
        'Emily', 'Daniel', 'Elizabeth', 'Matthew', 'Sofia', 'Jackson', 'Avery',
        'Sebastian', 'Ella', 'Jack', 'Scarlett', 'Aiden', 'Grace', 'Owen', 'Chloe',
        'Samuel', 'Victoria', 'David', 'Riley', 'Joseph', 'Aria', 'Carter', 'Lily',
    ];

    private array $lastNames = [
        'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
        'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson',
        'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Thompson', 'White',
        'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson', 'Walker', 'Young',
    ];

    private array $countries = [
        'SE', // Sweden
        'NO', // Norway
        'DK', // Denmark
        'FI', // Finland
        'IS', // Iceland
        'GB', // United Kingdom
        'DE', // Germany
        'US', // United States
        'CA', // Canada
        'NL', // Netherlands
    ];

    /**
     * Generate random reviews for a product
     *
     * @param Product $product
     * @param int $count Number of reviews to generate (default: random 20-25)
     * @return int Number of reviews created
     */
    public function generateReviews(Product $product, ?int $count = null): int
    {
        // Random count between 20-25 if not specified
        $count = $count ?? rand(20, 25);
        
        // Get all regular users (not admin)
        $users = User::where('role', '!=', 'admin')->pluck('id')->toArray();
        
        if (empty($users)) {
            \Log::warning("No users available to generate reviews for product {$product->id}");
            return 0;
        }
        
        $createdCount = 0;

        DB::transaction(function () use ($product, $count, $users, &$createdCount) {
            for ($i = 0; $i < $count; $i++) {
                // Random rating between 4 and 5
                $rating = rand(4, 5);
                
                // Get random review text
                $reviewText = $this->reviewTemplates[$rating][array_rand($this->reviewTemplates[$rating])];
                
                // Get random user
                $userId = $users[array_rand($users)];
                
                // Random date within last 6 months
                $daysAgo = rand(1, 180);
                $createdAt = now()->subDays($daysAgo);
                
                // Random country
                $country = $this->countries[array_rand($this->countries)];
                
                // Create review
                ProductReview::create([
                    'product_id' => $product->id,
                    'user_id' => $userId,
                    'rating' => $rating,
                    'review_text' => $reviewText,
                    'status' => 'approved', // Auto-approve generated reviews
                    'is_verified_purchase' => false,
                    'country' => $country,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
                
                $createdCount++;
            }
        });

        return $createdCount;
    }
}
