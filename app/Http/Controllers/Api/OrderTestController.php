<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            \Log::info('Order test creation started', ['request_data' => $request->all()]);
            
            // Simple validation
            $validator = Validator::make($request->all(), [
                'shipping_name' => 'required|string|max:255',
                'shipping_email' => 'required|email|max:255',
            ]);
            
            if ($validator->fails()) {
                \Log::info('Validation failed', ['errors' => $validator->errors()]);
                return response()->json(['errors' => $validator->errors()], 422);
            }
            
            // Get cart data
            $user = $request->user();
            $sessionId = $request->header('X-Session-Id');
            
            \Log::info('Looking for cart', ['user_id' => $user?->id, 'session_id' => $sessionId]);
            
            if (!$user && !$sessionId) {
                \Log::info('No user or session ID provided');
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
            $order->shipping_country = $request->shipping_country ?? 'Sweden';
            
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
            
            // Set simple totals as numbers
            $order->subtotal = 100.00;
            $order->tax = 25.00;
            $order->shipping = 9.99;
            $order->total = 134.99;
            
            \Log::info('Saving order');
            $order->save();
            \Log::info('Order saved', ['order_id' => $order->id]);
            
            // Create some test order items
            $testItems = [
                ['product_name' => 'Classic Leather Jacket', 'variant_name' => 'M Black', 'price' => 299.99, 'quantity' => 1],
                ['product_name' => 'Vintage Messenger Bag', 'variant_name' => 'One Size Brown', 'price' => 149.99, 'quantity' => 1],
            ];
            
            foreach ($testItems as $itemData) {
                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => null, // Test data doesn't need real variant
                    'product_name' => $itemData['product_name'],
                    'variant_name' => $itemData['variant_name'],
                    'price' => $itemData['price'],
                    'quantity' => $itemData['quantity'],
                    'subtotal' => $itemData['price'] * $itemData['quantity'],
                    'product_snapshot' => json_encode(['test' => true]),
                ]);
            }
            \Log::info('Test order items created');
            
            return response()->json([
                'message' => 'Test order created successfully',
                'order' => $order->load('items'),
            ], 201);
            
        } catch (\Exception $e) {
            \Log::error('Order test creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
