<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\CustomJacketCartItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class CartController extends Controller
{
    /**
     * Get cart contents
     */
    public function index(Request $request)
    {
        Log::info('CartController@index method called');
        
        try {
            // Debug: Log what we're receiving
            Log::info('Cart index request received', [
                'user' => $request->user() ? ['id' => $request->user()->id, 'email' => $request->user()->email] : null,
                'session_id_from_header' => $request->header('X-Session-Id'),
                'authorization_header' => $request->header('Authorization'),
                'all_headers' => $request->headers->all()
            ]);

            $cart = $this->getOrCreateCart($request);
            
            // If getOrCreateCart returned an error response, return it
            if ($cart instanceof \Illuminate\Http\JsonResponse) {
                return $cart;
            }
            
            // Validate cart ownership
            if (!$this->validateCartOwnership($request, $cart)) {
                return response()->json(['message' => 'Unauthorized access to cart'], 403);
            }

            // Load cart with items and their relationships (including product images for fallback)
            $cart->load([
                'items.productVariant.product.images',
                'items.productVariant.images'
            ]);
            
            // Debug logging for image loading
            Log::info('Cart loaded with relationships', [
                'cart_id' => $cart->id,
                'items_count' => $cart->items->count(),
                'items_with_images' => $cart->items->map(function($item) {
                    return [
                        'item_id' => $item->id,
                        'variant_id' => $item->productVariant->id,
                        'variant_images_count' => $item->productVariant->images->count(),
                        'product_images_count' => $item->productVariant->product->images->count(),
                        'variant_images' => $item->productVariant->images->pluck('url')->toArray(),
                        'product_images' => $item->productVariant->product->images->pluck('url')->toArray(),
                    ];
                })->toArray()
            ]);

            // Automatically adjust quantities based on current stock
            $adjustedItems = $this->adjustCartQuantitiesForStock($cart);
            
            // Reload cart after adjustments
            $cart->refresh();
            $cart->load([
                'items.productVariant.product.images',
                'items.productVariant.images'
            ]);

            // Transform the data structure to match frontend expectations
            $transformedCart = $cart->toArray();
            
            // Transform each cart item to match frontend structure
            foreach ($transformedCart['items'] as &$item) {
                // Rename 'product_variant' to 'variant' and add variant_id
                $item['variant'] = $item['product_variant'];
                unset($item['product_variant']);
                $item['variant_id'] = $item['variant']['id'];
            }

            $response = ['cart' => $transformedCart];
            
            // Add adjustment notifications if any quantities were adjusted
            if (!empty($adjustedItems)) {
                $response['stock_adjustments'] = $adjustedItems;
                $response['message'] = 'Some cart quantities were adjusted due to low stock';
            }
            
            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Failed to get cart', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
                'session_id' => $request->header('X-Session-Id')
            ]);

            return response()->json([
                'message' => 'Failed to get cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add item to cart
     */
    public function store(Request $request)
    {
        Log::info('CartController@store method called - Adding item to cart', [
            'request_data' => $request->all(),
            'headers' => $request->headers->all()
        ]);
        
        try {
            $request->validate([
                'product_variant_id' => 'required|exists:product_variants,id',
                'quantity' => 'required|integer|min:1'
            ]);

            $cart = $this->getOrCreateCart($request);
            
            // If getOrCreateCart returned an error response, return it
            if ($cart instanceof \Illuminate\Http\JsonResponse) {
                return $cart;
            }
            
            Log::info('Cart retrieved/created for adding item', [
                'cart_id' => $cart->id,
                'user_id' => $cart->user_id,
                'session_id' => $cart->session_id
            ]);
            
            // Validate cart ownership
            if (!$this->validateCartOwnership($request, $cart)) {
                return response()->json(['message' => 'Unauthorized access to cart'], 403);
            }

            $productVariant = ProductVariant::with('product')->find($request->product_variant_id);
            if (!$productVariant) {
                return response()->json(['message' => 'Product variant not found'], 404);
            }

            // Check stock availability
            $requestedQuantity = $request->quantity;
            $availableStock = $productVariant->stock;
            
            if ($requestedQuantity > $availableStock) {
                return response()->json([
                    'message' => 'Insufficient stock',
                    'available' => $availableStock,
                    'requested' => $requestedQuantity
                ], 400);
            }

            // Check if item already exists in cart
            $existingItem = $cart->items()->where('product_variant_id', $request->product_variant_id)->first();
            
            if ($existingItem) {
                $newQuantity = $existingItem->quantity + $requestedQuantity;
                
                // Check if total quantity would exceed stock
                if ($newQuantity > $availableStock) {
                    return response()->json([
                        'message' => 'Adding this quantity would exceed available stock',
                        'available' => $availableStock,
                        'current_in_cart' => $existingItem->quantity,
                        'requested_additional' => $requestedQuantity,
                        'total_would_be' => $newQuantity
                    ], 400);
                }
                
                $existingItem->update(['quantity' => $newQuantity]);
                $message = 'Cart item quantity updated';
            } else {
                $cart->items()->create([
                    'product_variant_id' => $request->product_variant_id,
                    'quantity' => $requestedQuantity
                ]);
                $message = 'Item added to cart';
            }

            // Refresh cart data
            $cart->load([
                'items.productVariant.product.images',
                'items.productVariant.images'
            ]);

            // Automatically adjust quantities based on current stock
            $adjustedItems = $this->adjustCartQuantitiesForStock($cart);
            
            // Reload cart after adjustments
            $cart->refresh();
            $cart->load([
                'items.productVariant.product.images',
                'items.productVariant.images'
            ]);

            // Transform the data structure to match frontend expectations
            $transformedCart = $cart->toArray();
            
            // Transform each cart item to match frontend structure
            foreach ($transformedCart['items'] as &$item) {
                // Rename 'product_variant' to 'variant' and add variant_id
                $item['variant'] = $item['product_variant'];
                unset($item['product_variant']);
                $item['variant_id'] = $item['variant']['id'];
            }

            $response = [
                'message' => $message,
                'cart' => $transformedCart
            ];
            
            // Add adjustment notifications if any quantities were adjusted
            if (!empty($adjustedItems)) {
                $response['stock_adjustments'] = $adjustedItems;
                $response['message'] .= ' (Some quantities adjusted due to low stock)';
            }
            
            return response()->json($response, 201);

        } catch (\Exception $e) {
            Log::error('Failed to add item to cart', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'message' => 'Failed to add item to cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:1'
            ]);

            $cart = $this->getOrCreateCart($request);
            
            // If getOrCreateCart returned an error response, return it
            if ($cart instanceof \Illuminate\Http\JsonResponse) {
                return $cart;
            }
            
            // Validate cart ownership
            if (!$this->validateCartOwnership($request, $cart)) {
                return response()->json(['message' => 'Unauthorized access to cart'], 403);
            }

            $cartItem = $cart->items()->find($id);
            if (!$cartItem) {
                return response()->json(['message' => 'Cart item not found'], 404);
            }

            $requestedQuantity = $request->quantity;
            $productVariant = $cartItem->productVariant;
            
            if (!$productVariant) {
                return response()->json(['message' => 'Product variant not found'], 404);
            }

            // Check stock availability
            $availableStock = $productVariant->stock;
            
            if ($requestedQuantity > $availableStock) {
                return response()->json([
                    'message' => 'Insufficient stock',
                    'available' => $availableStock,
                    'requested' => $requestedQuantity
                ], 400);
            }

            $cartItem->update(['quantity' => $requestedQuantity]);

            // Automatically adjust quantities based on current stock
            $adjustedItems = $this->adjustCartQuantitiesForStock($cart);
            
            // Reload cart after adjustments
            $cart->refresh();
            $cart->load(['items.productVariant.product', 'items.productVariant.images']);

            $response = [
                'message' => 'Cart item updated',
                'cart' => $cart
            ];
            
            // Add adjustment notifications if any quantities were adjusted
            if (!empty($adjustedItems)) {
                $response['stock_adjustments'] = $adjustedItems;
                $response['message'] .= ' (Some quantities adjusted due to low stock)';
            }
            
            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Failed to update cart item', [
                'error' => $e->getMessage(),
                'cart_item_id' => $id,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'message' => 'Failed to update cart item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove item from cart
     */
    public function destroy(Request $request, $id)
    {
        try {
            $cart = $this->getOrCreateCart($request);
            
            // If getOrCreateCart returned an error response, return it
            if ($cart instanceof \Illuminate\Http\JsonResponse) {
                return $cart;
            }
            
            // Validate cart ownership
            if (!$this->validateCartOwnership($request, $cart)) {
                return response()->json(['message' => 'Unauthorized access to cart'], 403);
            }

            $cartItem = $cart->items()->find($id);
            if (!$cartItem) {
                return response()->json(['message' => 'Cart item not found'], 404);
            }

            $cartItem->delete();

            // Refresh cart data
            $cart->load(['items.productVariant.product', 'items.productVariant.images']);

            return response()->json([
                'message' => 'Item removed from cart',
                'cart' => $cart
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to remove cart item', [
                'error' => $e->getMessage(),
                'cart_item_id' => $id
            ]);

            return response()->json([
                'message' => 'Failed to remove cart item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear all items from cart (both regular items and custom jacket items)
     */
    public function clear(Request $request)
    {
        try {
            $cart = $this->getOrCreateCart($request);
            
            // If getOrCreateCart returned an error response, return it
            if ($cart instanceof \Illuminate\Http\JsonResponse) {
                return $cart;
            }
            
            // Validate cart ownership
            if (!$this->validateCartOwnership($request, $cart)) {
                return response()->json(['message' => 'Unauthorized access to cart'], 403);
            }

            // Clear regular cart items
            $cart->items()->delete();
            Log::info('Regular cart items cleared', [
                'cart_id' => $cart->id,
                'user_id' => $request->user()?->id,
                'session_id' => $request->header('X-Session-Id')
            ]);

            // Clear custom jacket cart items
            $user = $request->user();
            $sessionId = $request->header('X-Session-Id');
            
            if ($user || $sessionId) {
                $customJacketQuery = \App\Models\CustomJacketCartItem::query();
                
                if ($user) {
                    $customJacketQuery->where('user_id', $user->id);
                } else {
                    $customJacketQuery->where('session_id', $sessionId);
                }
                
                $customJacketItems = $customJacketQuery->get();
                
                if ($customJacketItems->isNotEmpty()) {
                    Log::info('Clearing custom jacket cart items', [
                        'items_count' => $customJacketItems->count(),
                        'user_id' => $user?->id,
                        'session_id' => $sessionId
                    ]);
                    
                    // Delete custom jacket items from database
                    $customJacketItems->each(function ($item) {
                        $item->delete();
                    });
                    
                    Log::info('Custom jacket cart items cleared successfully');
                }
            }

            return response()->json([
                'message' => 'Cart cleared successfully',
                'cart' => $cart
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to clear cart', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
                'session_id' => $request->header('X-Session-Id')
            ]);

            return response()->json([
                'message' => 'Failed to clear cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Automatically adjust cart quantities based on current stock
     */
    private function adjustCartQuantitiesForStock(Cart $cart)
    {
        $adjustedItems = [];
        
        foreach ($cart->items as $item) {
            $productVariant = $item->productVariant;
            if (!$productVariant) continue;
            
            $currentQuantity = $item->quantity;
            $availableStock = $productVariant->stock;
            
            if ($currentQuantity > $availableStock) {
                // Reduce quantity to available stock
                $item->update(['quantity' => $availableStock]);
                $adjustedItems[] = [
                    'item_id' => $item->id,
                    'product_name' => $productVariant->product->name ?? 'Product',
                    'old_quantity' => $currentQuantity,
                    'new_quantity' => $availableStock,
                    'available_stock' => $availableStock
                ];
            }
        }
        
        return $adjustedItems;
    }

    /**
     * Get or create cart for the current user/session
     */
    private function getOrCreateCart(Request $request)
    {
        Log::info('getOrCreateCart method called');
        
        // First, try to get user from the request (this works for Auth0 JWT tokens)
        $user = $request->user();
        Log::info('User from request (Auth0)', ['user' => $user ? ['id' => $user->id, 'email' => $user->email] : null]);
        
        // If no user from request, try to authenticate using Sanctum token
        if (!$user) {
            $user = $this->authenticateWithSanctum($request);
            Log::info('User from Sanctum authentication', ['user' => $user ? ['id' => $user->id, 'email' => $user->email] : null]);
        }
        
        $sessionId = $request->header('X-Session-Id');
        Log::info('Session ID from header', ['session_id' => $sessionId]);
        
        if ($user) {
            // Authenticated user - get or create user-specific cart
            Log::info('Creating user-specific cart', ['user_id' => $user->id]);
            return Cart::firstOrCreate(['user_id' => $user->id]);
        } else {
            // Guest user - get or create session-specific cart
            if (!$sessionId) {
                Log::warning('No session ID provided for guest user');
                return response()->json(['message' => 'Session ID required for guest users'], 400);
            }
            Log::info('Creating session-specific cart', ['session_id' => $sessionId]);
            return Cart::firstOrCreate(['session_id' => $sessionId, 'user_id' => null]);
        }
    }
    
    /**
     * Authenticate user using Sanctum token from Authorization header
     */
    private function authenticateWithSanctum(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }
        
        $token = substr($authHeader, 7); // Remove 'Bearer ' prefix
        
        try {
            $personalAccessToken = PersonalAccessToken::findToken($token);
            if ($personalAccessToken && (!$personalAccessToken->expires_at || $personalAccessToken->expires_at->isFuture())) {
                $user = $personalAccessToken->tokenable;
                if ($user) {
                    // For Sanctum, we need to set the user on the request
                    $request->setUserResolver(function () use ($user) {
                        return $user;
                    });
                    return $user;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to authenticate user with Sanctum token', ['error' => $e->getMessage()]);
        }
        
        return null;
    }

    /**
     * Validate that the cart belongs to the current user/session
     */
    private function validateCartOwnership(Request $request, $cart)
    {
        if (!$cart) {
            return false;
        }
        
        $user = $request->user();
        $sessionId = $request->header('X-Session-Id');
        
        if ($user) {
            // Authenticated user - cart must belong to them
            return $cart->user_id === $user->id;
        } else {
            // Guest user - cart must match their session ID
            return $cart->session_id === $sessionId && $cart->user_id === null;
        }
    }
    
    /**
     * Clean up expired guest carts (can be called via scheduled command)
     */
    public function cleanupExpiredGuestCarts()
    {
        $expiredCarts = Cart::whereNotNull('session_id')
            ->whereNull('user_id')
            ->where('updated_at', '<', now()->subDays(7)) // Guest carts expire after 7 days
            ->get();
            
        foreach ($expiredCarts as $cart) {
            $cart->items()->delete();
            $cart->delete();
        }
        
        Log::info('Cleaned up expired guest carts', ['count' => $expiredCarts->count()]);
        
        return response()->json(['message' => 'Expired guest carts cleaned up', 'count' => $expiredCarts->count()]);
    }
    
    /**
     * Migrate guest cart to user cart when user logs in
     */
    public function migrateGuestCart(Request $request)
    {
        $user = $request->user();
        $sessionId = $request->header('X-Session-Id');
        
        if (!$user || !$sessionId) {
            return response()->json(['message' => 'User must be authenticated and session ID required'], 400);
        }
        
        try {
            // Find guest cart with session ID
            $guestCart = Cart::where('session_id', $sessionId)
                ->whereNull('user_id')
                ->first();
            
            if (!$guestCart || $guestCart->items->isEmpty()) {
                return response()->json(['message' => 'No guest cart found or cart is empty'], 404);
            }
            
            // Find or create user cart
            $userCart = Cart::firstOrCreate(['user_id' => $user->id]);
            
            // Migrate items from guest cart to user cart
            $migratedItems = 0;
            foreach ($guestCart->items as $guestItem) {
                // Check if item already exists in user cart
                $existingItem = $userCart->items()
                    ->where('product_variant_id', $guestItem->product_variant_id)
                    ->first();
                
                if ($existingItem) {
                    // Merge quantities
                    $existingItem->update([
                        'quantity' => $existingItem->quantity + $guestItem->quantity
                    ]);
                } else {
                    // Create new item
                    $userCart->items()->create([
                        'product_variant_id' => $guestItem->product_variant_id,
                        'quantity' => $guestItem->quantity
                    ]);
                }
                $migratedItems++;
            }
            
            // Delete guest cart
            $guestCart->items()->delete();
            $guestCart->delete();
            
            Log::info('Guest cart migrated to user cart', [
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'migrated_items' => $migratedItems
            ]);
            
            return response()->json([
                'message' => 'Guest cart migrated successfully',
                'migrated_items' => $migratedItems
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to migrate guest cart', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'session_id' => $sessionId
            ]);
            
            return response()->json([
                'message' => 'Failed to migrate guest cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}