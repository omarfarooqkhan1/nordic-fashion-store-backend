<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReview extends Model 
{
    public static function hasUserPurchasedProduct(int $userId, int $productId): bool
    {
        return \DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.user_id', $userId)
            ->where('product_variants.product_id', $productId)
            ->whereIn('orders.status', ['pending', 'shipped', 'delivered'])
            ->where('order_items.quantity', '>', 0)
            ->exists();
    }

    public static function hasUserReceivedProduct(int $userId, int $productId): bool
    {
        return \DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.user_id', $userId)
            ->where('product_variants.product_id', $productId)
            ->whereIn('orders.status', ['delivered'])
            ->where('order_items.quantity', '>', 0)
            ->exists();
    }

    /**
     * Check if a user has already reviewed a product
     */
    public static function hasUserReviewedProduct(int $userId, int $productId): bool
    {
        return static::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Get detailed purchase verification info
     */
    public static function getPurchaseVerificationInfo(int $userId, int $productId): array
    {
        $allOrders = \DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select([
                'orders.id as order_id',
                'orders.status as order_status',
                'orders.user_id',
                'order_items.product_variant_id',
                'order_items.quantity',
                'product_variants.product_id',
                'orders.created_at as order_date'
            ])
            ->where('orders.user_id', $userId)
            ->where('product_variants.product_id', $productId)
            ->get();
        $purchaseInfo = \DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select([
                'orders.id as order_id',
                'orders.status as order_status',
                'orders.created_at as purchase_date',
                'order_items.quantity'
            ])
            ->where('orders.user_id', $userId)
            ->where('product_variants.product_id', $productId)
            ->whereIn('orders.status', ['delivered'])
            ->where('order_items.quantity', '>', 0)
            ->first();

        // Check if user has any pending or shipped orders for this product
        $pendingOrder = \DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select([
                'orders.id as order_id',
                'orders.status as order_status',
                'orders.created_at as order_date'
            ])
            ->where('orders.user_id', $userId)
            ->where('product_variants.product_id', $productId)
            ->whereIn('orders.status', ['pending', 'shipped'])
            ->where('order_items.quantity', '>', 0)
            ->first();

        $hasDelivered = !is_null($purchaseInfo);
        $hasPending = !is_null($pendingOrder);
        $hasReviewed = static::hasUserReviewedProduct($userId, $productId);

        return [
            'has_purchased' => $hasDelivered || $hasPending,
            'has_delivered' => $hasDelivered,
            'has_pending' => $hasPending,
            'purchase_details' => $purchaseInfo,
            'pending_order_details' => $pendingOrder,
            'can_review' => $hasDelivered && !$hasReviewed,  // Only if delivered and not reviewed
            'has_reviewed' => $hasReviewed,
            'delivery_status' => $hasDelivered ? 'delivered' : ($hasPending ? 'pending' : 'none')
        ];
    }

    use HasFactory;


    protected $fillable = [
        'user_id',
        'product_id',
        'rating',
        'review_text',
        'title',
        'is_verified_purchase',
        'media',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_verified_purchase' => 'boolean',
        'media' => 'array',
    ];

    /**
     * Get the user that wrote the review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product being reviewed.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

}
