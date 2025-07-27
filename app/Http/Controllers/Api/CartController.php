<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CartController extends Controller
{
    // Get current user's or guest's cart
    public function index(Request $request)
    {
        $cart = $this->getOrCreateCart($request);
        $cart->load('items.variant.product');

        return response()->json($cart);
    }

    // Add or update item in the cart
    public function store(Request $request)
    {
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $this->getOrCreateCart($request);

        $item = $cart->items()->updateOrCreate(
            ['product_variant_id' => $request->product_variant_id],
            ['quantity' => $request->quantity]
        );

        return response()->json($item->load('variant.product'), 201);
    }

    // Update quantity
    public function update(Request $request, $itemId)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        
        $cart = $this->getOrCreateCart($request);
        $item = $cart->items()->findOrFail($itemId);
        $item->update(['quantity' => $request->quantity]);

        return response()->json($item->load('variant.product'));
    }

    // Remove item
    public function destroy(Request $request, $itemId)
    {
        $cart = $this->getOrCreateCart($request);
        $item = $cart->items()->findOrFail($itemId);
        $item->delete();

        return response()->json(['message' => 'Item removed']);
    }

    // Clear entire cart
    public function clear(Request $request)
    {
        $cart = $this->getOrCreateCart($request);
        $cart->items()->delete();

        return response()->json(['message' => 'Cart cleared']);
    }

    private function getOrCreateCart(Request $request): Cart
    {
        if ($request->user()) {
            return Cart::firstOrCreate(['user_id' => $request->user()->id]);
        } else {
            $sessionId = $request->header('X-Session-Id');
            if (!$sessionId) {
                abort(400, 'Session ID is required for guest carts');
            }
            return Cart::firstOrCreate(['session_id' => $sessionId]);
        }
    }
}