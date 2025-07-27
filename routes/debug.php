<?php

use Illuminate\Support\Facades\Route;
use App\Models\Cart;
use App\Models\ProductVariant;

// Debug route to check cart and order creation
Route::get('/debug/cart/{sessionId?}', function ($sessionId = null) {
    try {
        if (!$sessionId) {
            $carts = Cart::with(['items', 'items.productVariant', 'items.productVariant.product'])->get();
            return response()->json([
                'total_carts' => $carts->count(),
                'carts' => $carts->map(function ($cart) {
                    return [
                        'id' => $cart->id,
                        'session_id' => $cart->session_id,
                        'user_id' => $cart->user_id,
                        'items_count' => $cart->items->count(),
                        'items' => $cart->items->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'product_variant_id' => $item->product_variant_id,
                                'quantity' => $item->quantity,
                                'variant' => $item->productVariant ? [
                                    'id' => $item->productVariant->id,
                                    'name' => $item->productVariant->name,
                                    'price' => $item->productVariant->price,
                                    'stock' => $item->productVariant->stock,
                                    'product_name' => $item->productVariant->product->name ?? 'Unknown'
                                ] : null
                            ];
                        })
                    ];
                })
            ]);
        } else {
            $cart = Cart::where('session_id', $sessionId)
                ->with(['items', 'items.productVariant', 'items.productVariant.product'])
                ->first();
                
            if (!$cart) {
                return response()->json(['error' => 'Cart not found for session: ' . $sessionId]);
            }
            
            return response()->json([
                'cart_id' => $cart->id,
                'session_id' => $cart->session_id,
                'items_count' => $cart->items->count(),
                'items' => $cart->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_variant_id' => $item->product_variant_id,
                        'quantity' => $item->quantity,
                        'variant' => $item->productVariant ? [
                            'id' => $item->productVariant->id,
                            'name' => $item->productVariant->name,
                            'price' => $item->productVariant->price,
                            'stock' => $item->productVariant->stock,
                            'product_name' => $item->productVariant->product->name ?? 'Unknown'
                        ] : null
                    ];
                })
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
