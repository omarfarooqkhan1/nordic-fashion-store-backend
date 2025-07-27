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

class OrderController extends Controller
{
    /**
     * Display a listing of the user's orders.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            $sessionId = $request->header('X-Session-Id');
            if (!$sessionId) {
                return response()->json(['message' => 'No user or session ID provided'], 400);
            }
            
            $orders = Order::where('session_id', $sessionId)
                ->with('items')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $orders = $user->orders()
                ->with('items')
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        return response()->json($orders);
    }
    
    /**
     * Store a new order (checkout).
     */
    public function store(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'shipping_name' => 'required|string|max:255',
            'shipping_email' => 'required|email|max:255',
            'shipping_phone' => 'nullable|string|max:20',
            'shipping_address' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:255',
            'shipping_state' => 'required|string|max:255',
            'shipping_postal_code' => 'required|string|max:20',
            'shipping_country' => 'required|string|max:255',
            'billing_same_as_shipping' => 'boolean',
            'payment_method' => 'required|string|in:credit_card,paypal,stripe',
            'notes' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // If billing is not same as shipping, validate billing info
        if (!$request->billing_same_as_shipping) {
            $billingValidator = Validator::make($request->all(), [
                'billing_name' => 'required|string|max:255',
                'billing_email' => 'required|email|max:255',
                'billing_phone' => 'nullable|string|max:20',
                'billing_address' => 'required|string|max:255',
                'billing_city' => 'required|string|max:255',
                'billing_state' => 'required|string|max:255',
                'billing_postal_code' => 'required|string|max:20',
                'billing_country' => 'required|string|max:255',
            ]);
            
            if ($billingValidator->fails()) {
                return response()->json(['errors' => $billingValidator->errors()], 422);
            }
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
            $cart = Cart::where('user_id', $user->id)->first();
        } elseif ($sessionId) {
            $cart = Cart::where('session_id', $sessionId)->first();
        }
        
        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }
        
        try {
            DB::beginTransaction();
            
            // Create order
            $order = new Order();
            $order->user_id = $user ? $user->id : null;
            $order->session_id = $user ? null : $sessionId;
            $order->order_number = Order::generateOrderNumber();
            $order->status = 'pending';
            
            // Set shipping info
            $order->shipping_name = $request->shipping_name;
            $order->shipping_email = $request->shipping_email;
            $order->shipping_phone = $request->shipping_phone;
            $order->shipping_address = $request->shipping_address;
            $order->shipping_city = $request->shipping_city;
            $order->shipping_state = $request->shipping_state;
            $order->shipping_postal_code = $request->shipping_postal_code;
            $order->shipping_country = $request->shipping_country;
            
            // Set billing info
            $order->billing_same_as_shipping = $request->billing_same_as_shipping ?? true;
            
            if (!$order->billing_same_as_shipping) {
                $order->billing_name = $request->billing_name;
                $order->billing_email = $request->billing_email;
                $order->billing_phone = $request->billing_phone;
                $order->billing_address = $request->billing_address;
                $order->billing_city = $request->billing_city;
                $order->billing_state = $request->billing_state;
                $order->billing_postal_code = $request->billing_postal_code;
                $order->billing_country = $request->billing_country;
            } else {
                $order->billing_name = $request->shipping_name;
                $order->billing_email = $request->shipping_email;
                $order->billing_phone = $request->shipping_phone;
                $order->billing_address = $request->shipping_address;
                $order->billing_city = $request->shipping_city;
                $order->billing_state = $request->shipping_state;
                $order->billing_postal_code = $request->shipping_postal_code;
                $order->billing_country = $request->shipping_country;
            }
            
            // Set payment info
            $order->payment_method = $request->payment_method;
            $order->payment_status = 'pending';
            $order->notes = $request->notes;
            
            // Initialize totals
            $order->subtotal = 0;
            $order->tax = 0;
            $order->shipping = 0;
            $order->total = 0;
            
            $order->save();
            
            // Create order items from cart
            foreach ($cart->items as $cartItem) {
                // Check if the product variant exists and has stock
                $variant = ProductVariant::find($cartItem->product_variant_id);
                
                if (!$variant || $variant->stock < $cartItem->quantity) {
                    throw new \Exception('Product is out of stock: ' . ($variant ? $variant->product->name : 'Unknown product'));
                }
                
                // Create snapshot of product data
                $productSnapshot = [
                    'product' => $variant->product->toArray(),
                    'variant' => $variant->toArray(),
                ];
                
                // Create order item
                $orderItem = new OrderItem([
                    'order_id' => $order->id,
                    'product_variant_id' => $cartItem->product_variant_id,
                    'product_name' => $variant->product->name,
                    'variant_name' => $variant->name ?? ($variant->size . ' ' . $variant->color),
                    'price' => $variant->actual_price ?? $variant->product->price,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => ($variant->actual_price ?? $variant->product->price) * $cartItem->quantity,
                    'product_snapshot' => $productSnapshot,
                ]);
                
                $orderItem->save();
                
                // Update stock
                $variant->stock -= $cartItem->quantity;
                $variant->save();
            }
            
            // Calculate order totals
            $order->calculateTotals();
            
            // Clear the cart
            $cart->items()->delete();
            
            DB::commit();
            
            // Process payment (mock success for now)
            $order->payment_status = 'completed';
            $order->payment_transaction_id = 'TRANS_' . uniqid();
            $order->save();
            
            return response()->json([
                'message' => 'Order placed successfully',
                'order' => $order->load('items'),
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Display the specified order.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $sessionId = $request->header('X-Session-Id');
        
        $query = Order::with('items');
        
        if ($user) {
            $query->where('user_id', $user->id);
        } elseif ($sessionId) {
            $query->where('session_id', $sessionId);
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        $order = $query->find($id);
        
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        
        return response()->json($order);
    }
}
