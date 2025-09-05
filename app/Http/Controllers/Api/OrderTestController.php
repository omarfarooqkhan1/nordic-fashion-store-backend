<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderTestController extends Controller
{
    /**
     * Test order creation with minimal processing
     */
    public function testStore(Request $request)
    {
        try {
            
            // Simple validation
            $validator = Validator::make($request->all(), [
                'shipping_name' => 'required|string|max:255',
                'shipping_email' => 'required|email|max:255',
            ]);
            
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            
            // Get cart data
            $user = $request->user();
            $sessionId = $request->header('X-Session-Id');
            
            
            if (!$user && !$sessionId) {
                return response()->json(['message' => 'No user or session ID provided'], 400);
            }
            
            // Get cart
            $cart = null;
            if ($user) {
                $cart = Cart::where('user_id', $user->id)->with('items')->first();
            } elseif ($sessionId) {
                $cart = Cart::where('session_id', $sessionId)->with('items')->first();
            }
            
            \Log::info('Cart found', ['cart_id' => $cart?->id, 'items_count' => $cart?->items->count()]);
            
            if (!$cart || $cart->items->isEmpty()) {
                \Log::info('Cart is empty or not found');
                return response()->json(['message' => 'Cart is empty'], 400);
            }
            
            // Create minimal order without complex processing
            $order = new Order();
            $order->user_id = $user ? $user->id : null;
            $order->session_id = $user ? null : $sessionId;
            $order->order_number = 'TEST_' . time();
            $order->status = 'pending';
            
            // Set required shipping info
            $order->shipping_name = $request->shipping_name;
            $order->shipping_email = $request->shipping_email;
            $order->shipping_phone = $request->shipping_phone ?? '';
            $order->shipping_address = $request->shipping_address ?? 'Test Address';
            $order->shipping_city = $request->shipping_city ?? 'Test City';
            $order->shipping_state = $request->shipping_state ?? 'Test State';
            $order->shipping_postal_code = $request->shipping_postal_code ?? '12345';
            $order->shipping_country = $request->shipping_country ?? 'Finland';
            
            // Set billing same as shipping
            $order->billing_same_as_shipping = true;
            $order->billing_name = $order->shipping_name;
            $order->billing_email = $order->shipping_email;
            $order->billing_phone = $order->shipping_phone;
            $order->billing_address = $order->shipping_address;
            $order->billing_city = $order->shipping_city;
            $order->billing_state = $order->shipping_state;
            $order->billing_postal_code = $order->shipping_postal_code;
            $order->billing_country = $order->shipping_country;
            
            // Set payment info
            $order->payment_method = $request->payment_method ?? 'credit_card';
            $order->payment_status = 'pending';
            
            // Initialize totals (will be calculated after adding items)
            $order->subtotal = 0;
            $order->tax = 0;
            $order->shipping = 0;
            $order->total = 0;
            
            \Log::info('Saving order');
            $order->save();
            \Log::info('Order saved', ['order_id' => $order->id]);
            
            // Create order items from actual cart items
            foreach ($cart->items as $cartItem) {
                // Check if the product variant exists
                $variant = ProductVariant::find($cartItem->product_variant_id);
                
                if (!$variant) {
                    \Log::warning('Product variant not found', ['variant_id' => $cartItem->product_variant_id]);
                    continue; // Skip this item if variant doesn't exist
                }
                
                // Create snapshot of product data
                $productSnapshot = [
                    'product' => $variant->product->toArray(),
                    'variant' => $variant->toArray(),
                ];
                
                // Create order item from cart item
                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $cartItem->product_variant_id,
                    'product_name' => $variant->product->name,
                    'variant_name' => $variant->name ?? ($variant->size . ' ' . $variant->color),
                    'price' => $variant->actual_price ?? $variant->product->price,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => ($variant->actual_price ?? $variant->product->price) * $cartItem->quantity,
                    'product_snapshot' => json_encode($productSnapshot),
                ]);
                
                \Log::info('Order item created from cart', [
                    'product_name' => $variant->product->name,
                    'variant_name' => $variant->name ?? ($variant->size . ' ' . $variant->color),
                    'quantity' => $cartItem->quantity
                ]);
            }
            
            // Calculate proper totals based on actual items
            $order->calculateTotals();
            
            // Save shipping address to user's address book if authenticated
            if ($user) {
                $this->saveShippingAddressToUser($user, $request);
            }
            
            // Clear the cart after successful order creation
            $cart->items()->delete();

            // Send order confirmation email (same as production)
            try {
                \Mail::to($order->shipping_email)->send(new \App\Mail\OrderConfirmation($order));
            } catch (\Exception $e) {
            }

            return response()->json([
                'message' => 'Test order created successfully',
                'order' => $order->load('items'),
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Save shipping address to user's address book if it's a new address
     */
    private function saveShippingAddressToUser($user, Request $request)
    {
        try {
            // Check if user already has this exact address
            $existingAddress = $user->addresses()
                ->where('street', $request->shipping_address)
                ->where('city', $request->shipping_city)
                ->where('postal_code', $request->shipping_postal_code)
                ->where('country', $request->shipping_country)
                ->first();
            
            // If address doesn't exist, create it
            if (!$existingAddress) {
                $addressData = [
                    'type' => 'home', // Default type
                    'label' => $request->shipping_name . "'s Address",
                    'street' => $request->shipping_address ?? 'Test Address',
                    'city' => $request->shipping_city ?? 'Test City',
                    'state' => $request->shipping_state ?? '',
                    'postal_code' => $request->shipping_postal_code ?? '12345',
                    'country' => $request->shipping_country ?? 'Finland',
                ];
                
                $address = $user->addresses()->create($addressData);
                
                // If this is the user's first address, make it default
                $userAddressCount = $user->addresses()->count();
                if ($userAddressCount === 1) {
                    $address->update(['is_default' => true]);
                }
                
                \Log::info('Shipping address saved to user address book', [
                    'user_id' => $user->id,
                    'address_id' => $address->id,
                    'address_label' => $address->label
                ]);
            }
        } catch (\Exception $e) {
            // Log the error but don't fail the order creation
            \Log::error('Failed to save shipping address to user address book: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'shipping_address' => $request->shipping_address
            ]);
        }
    }
}
