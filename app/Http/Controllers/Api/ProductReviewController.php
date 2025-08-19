<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\CloudinaryService;

class ProductReviewController extends Controller
{
    /**
     * Admin: Get all pending reviews
     */
    public function pendingReviews(): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        \Log::info('pendingReviews - Auth user', [
            'user_id' => $user?->id,
            'is_admin' => $user?->is_admin,
            'user' => $user,
        ]);
        if (!$user || !$user->isAdmin()) {
            \Log::warning('pendingReviews - Unauthorized access', [
                'user_id' => $user?->id,
                'role' => $user?->role,
            ]);
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $pending = ProductReview::where('status', 'pending')->with('user:id,name')->orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $pending
        ]);
    }
    /**
     * Get reviews for a specific product
     */
    public function index(Request $request, int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        $user = Auth::guard('sanctum')->user();
        \Log::info('ProductReviewController@index - Auth user', ['user' => $user]);
        $reviews = $product->reviews()
            ->where(function ($query) use ($user) {
                $query->where('status', 'approved');
                if ($user) {
                    $query->orWhere(function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
                }
            })
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        \Log::info('ProductReviewController@index - Reviews count', ['count' => $reviews->count(), 'review_ids' => $reviews->pluck('id')]);

        // Ensure media is always an array for each review
        $reviews->getCollection()->transform(function ($review) {
            $arr = $review->toArray();
            $arr['media'] = $arr['media'] ?? [];
            return $arr;
        });

        return response()->json([
            'success' => true,
            'data' => $reviews,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'average_rating' => $product->average_rating,
                'review_count' => $product->review_count,
            ]
        ]);
    }

    /**
     * Store a new review
     */
    public function store(Request $request, int $productId): JsonResponse
    {

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Authentication required'], 401);
        }

        // Check if user has purchased the product
        if (!ProductReview::hasUserPurchasedProduct($user->id, $productId)) {
            return response()->json([
                'message' => 'You can only review products you have purchased'
            ], 403);
        }

        // Check if user has already reviewed this product
        if (ProductReview::hasUserReviewedProduct($user->id, $productId)) {
            return response()->json([
                'message' => 'You have already reviewed this product'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'review_text' => 'nullable|string|max:1000',
            'media' => 'nullable',
            'media.*' => 'file|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi|max:20480', // 20MB max per file
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Normalize media files to array for single/multiple upload
        $mediaFiles = $request->file('media');
        $fileCount = 0;
        if ($mediaFiles instanceof \Illuminate\Http\UploadedFile) {
            $mediaFiles = [$mediaFiles];
            $fileCount = 1;
        } elseif (is_array($mediaFiles)) {
            $fileCount = count($mediaFiles);
        }
        \Log::info('Review media files received (store)', [
            'has_file' => $request->hasFile('media'),
            'file_count' => $fileCount,
            'all_files' => $request->allFiles()
        ]);

        $mediaUrls = [];
        if ($request->hasFile('media') && is_array($mediaFiles)) {
            $cloudinaryService = app(CloudinaryService::class);
            foreach ($mediaFiles as $file) {
                $publicId = 'reviews/' . uniqid() . '_' . $file->getClientOriginalName();
                $resourceType = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';
                $result = $resourceType === 'image'
                    ? $cloudinaryService->uploadImage($file->getRealPath(), $publicId, 'nordic-skin-reviews')
                    : $cloudinaryService->uploadVideo($file->getRealPath(), $publicId, 'nordic-skin-reviews');
                if ($result && isset($result['secure_url'])) {
                    $mediaUrls[] = [
                        'url' => $result['secure_url'],
                        'type' => $resourceType
                    ];
                }
            }
        }

        $review = ProductReview::create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'rating' => $request->rating,
            'title' => $request->title,
            'review_text' => $request->review_text,
            'is_verified_purchase' => true,
            'media' => $mediaUrls,
            'status' => 'pending',
        ]);

        $review->load('user:id,name');

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully',
            'data' => $review
        ], 201);
    }

    /**
     * Update an existing review
     */
    public function update(Request $request, int $productId, int $reviewId): JsonResponse
    {
        // Debug: Log all files received in the request
        \Log::info('ProductReviewController@update - allFiles', [
            'all_files' => $request->allFiles(),
            'has_file' => $request->hasFile('media'),
            'media_files' => $request->file('media'),
        ]);

        // Robust: Enforce max 5 media (existing + new), prevent duplicates
        $existingMedia = $request->has('existing_media') ? json_decode($request->input('existing_media'), true) : [];
        if (!is_array($existingMedia)) $existingMedia = [];
        $existingMedia = array_values(array_filter($existingMedia, function($item) {
            return isset($item['url']) && isset($item['type']);
        }));

        $mediaFiles = $request->file('media');
        $newFiles = [];
        if ($mediaFiles instanceof \Illuminate\Http\UploadedFile) {
            $newFiles = [$mediaFiles];
        } elseif (is_array($mediaFiles)) {
            $newFiles = $mediaFiles;
        }
        $totalMediaCount = count($existingMedia) + count($newFiles);
        if ($totalMediaCount > 5) {
            return response()->json([
                'message' => 'You can only attach up to 5 images/videos per review.',
                'errors' => ['media' => ['Maximum 5 media allowed.']],
            ], 422);
        }
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Authentication required'], 401);
        }


        $review = ProductReview::where('id', $reviewId)
            ->where('product_id', $productId)
            ->where('user_id', $user->id)
            ->first();
        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }
        if ($review->status === 'approved') {
            return response()->json(['message' => 'You cannot edit an approved review.'], 403);
        }

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'sometimes|integer|min:1|max:5',
            'title' => 'sometimes|nullable|string|max:255',
            'review_text' => 'sometimes|nullable|string|max:1000',
            'media' => 'nullable',
            'media.*' => 'file|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi|max:20480',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $cloudinaryService = app(CloudinaryService::class);
        $oldMedia = $review->media ?? [];

        // Delete removed media from Cloudinary
        $existingUrls = array_column($existingMedia, 'url');
        foreach ($oldMedia as $mediaItem) {
            if (!in_array($mediaItem['url'], $existingUrls)) {
                $cloudinaryService->deleteImage($mediaItem['url']);
            }
        }

        // Start with existing media
        $mediaUrls = $existingMedia;
        // Add new uploads (prevent duplicate URLs)
        if (!empty($newFiles)) {
            foreach ($newFiles as $file) {
                // Robust: skip invalid or unreadable files
                if (!$file instanceof \Illuminate\Http\UploadedFile || !$file->isValid() || !$file->getRealPath()) {
                    \Log::warning('Skipping invalid file in review update', ['file' => $file]);
                    continue;
                }
                $publicId = 'reviews/' . uniqid() . '_' . $file->getClientOriginalName();
                $resourceType = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';
                $result = $resourceType === 'image'
                    ? $cloudinaryService->uploadImage($file->getRealPath(), $publicId, 'nordic-skin-reviews')
                    : $cloudinaryService->uploadVideo($file->getRealPath(), $publicId, 'nordic-skin-reviews');
                if ($result && isset($result['secure_url'])) {
                    // Only add if not already present
                    if (!in_array($result['secure_url'], array_column($mediaUrls, 'url'))) {
                        $mediaUrls[] = [
                            'url' => $result['secure_url'],
                            'type' => $resourceType
                        ];
                    }
                }
            }
        }
        // Final safety: trim to 5 max
        $mediaUrls = array_slice($mediaUrls, 0, 5);

        $review->update(array_merge(
            $request->only(['rating', 'title', 'review_text']),
            [
                'media' => $mediaUrls,
                'status' => 'pending', // Set to pending on update
            ]
        ));

        $review->load('user:id,name');

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully',
            'data' => $review
        ]);
    }
    /**
     * Admin: Approve a review
     */
    public function approve(int $reviewId): JsonResponse
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $review = ProductReview::find($reviewId);
        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }
        $review->status = 'approved';
        $review->save();

        // Send email notification to the reviewer
        if ($review->user && $review->user->email) {
            try {
                \Mail::to($review->user->email)->send(new \App\Mail\ReviewApproved($review));
            } catch (\Exception $e) {
                \Log::error('Failed to send review approval email', [
                    'review_id' => $review->id,
                    'user_id' => $review->user->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Return updated pending reviews
        $pending = ProductReview::where('status', 'pending')->with('user:id,name')->orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'message' => 'Review approved',
            'pending_reviews' => $pending
        ]);
    }

    /**
     * Admin: Reject a review
     */
    public function reject(int $reviewId): JsonResponse
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $review = ProductReview::find($reviewId);
        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }
        $review->status = 'rejected';
        $review->save();

        // Return updated pending reviews
        $pending = ProductReview::where('status', 'pending')->with('user:id,name')->orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'message' => 'Review rejected',
            'pending_reviews' => $pending
        ]);
    }

    /**
     * Delete a review
     */
    public function destroy(int $productId, int $reviewId): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Authentication required'], 401);
        }


        $review = ProductReview::where('id', $reviewId)
            ->where('product_id', $productId)
            ->where('user_id', $user->id)
            ->first();
        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }
        if ($review->status === 'approved') {
            return response()->json(['message' => 'You cannot delete an approved review.'], 403);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully'
        ]);
    }

    /**
     * Check if user can review a product
     */
    public function canReview(int $productId): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Authentication required'], 401);
        }

        $verificationInfo = ProductReview::getPurchaseVerificationInfo($user->id, $productId);

        $existingReview = null;
        if ($verificationInfo['has_reviewed']) {
            $review = ProductReview::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->first();
            if ($review) {
                $reviewArr = $review->toArray();
                // Ensure media is always an array
                $reviewArr['media'] = $reviewArr['media'] ?? [];
                $existingReview = $reviewArr;
            }
        }

        $response = [
            'success' => true,
            'data' => [
                'can_review' => $verificationInfo['can_review'],
                'has_purchased' => $verificationInfo['has_purchased'],
                'has_delivered' => $verificationInfo['has_delivered'],
                'has_pending' => $verificationInfo['has_pending'],
                'has_reviewed' => $verificationInfo['has_reviewed'],
                'purchase_details' => $verificationInfo['purchase_details'],
                'pending_order_details' => $verificationInfo['pending_order_details'],
                'delivery_status' => $verificationInfo['delivery_status'],
                'existing_review' => $existingReview
            ]
        ];

        // Add additional context for debugging
        if (!$verificationInfo['has_purchased']) {
            $response['data']['message'] = 'You need to purchase this product before reviewing it.';
        } elseif (!$verificationInfo['has_delivered']) {
            $response['data']['message'] = 'Your order is still being processed. You can review this product once it has been delivered.';
        } elseif ($verificationInfo['has_reviewed']) {
            $response['data']['message'] = 'You have already reviewed this product.';
        }

        return response()->json($response);
    }

}
