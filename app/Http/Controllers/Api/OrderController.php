<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CustomJacketCartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Mail\OrderConfirmation;
use App\Mail\OrderShipped;
use App\Mail\OrderStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class OrderController extends Controller
{
    /**
     * Display a listing of the user's orders.
     */
    public function index(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        
        if (!$user) {
            $sessionId = $request->header('X-Session-Id');
            if (!$sessionId) {
                return response()->json(['message' => 'No user or session ID provided'], 400);
            }
            
            $query = Order::where('session_id', $sessionId)
                ->with(['items.variant.images', 'items.variant.product.allImages']);
        } else {
            $query = $user->orders()
                ->with(['items.variant.images', 'items.variant.product.allImages']);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Search by order number
        if ($request->has('search') && $request->search) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        // Ensure each item has access to its order for currency conversion
        $orders->getCollection()->each(function ($order) {
            $order->items->each(function ($item) use ($order) {
                $item->setRelation('order', $order);
            });
        });
return response()->json([
            'data' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'has_more_pages' => $orders->hasMorePages(),
            ]
        ]);
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
            'shipping_state' => 'nullable|string|max:255',
            'shipping_postal_code' => 'required|string|max:20',
            'shipping_country' => 'required|string|max:255',
            'billing_same_as_shipping' => 'boolean',
            'payment_method' => 'required|string|in:credit_card,paypal,stripe',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
            // Add validation for calculated totals from frontend
            'calculated_subtotal' => 'nullable|numeric|min:0',
            'calculated_tax' => 'nullable|numeric|min:0',
            'calculated_shipping' => 'nullable|numeric|min:0',
            'calculated_total' => 'nullable|numeric|min:0',
        ]);
        
        if ($validator->fails()) {return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // If billing is not same as shipping, validate billing info
        if (!$request->billing_same_as_shipping) {
            $billingValidator = Validator::make($request->all(), [
                'billing_name' => 'required|string|max:255',
                'billing_email' => 'required|email|max:255',
                'billing_phone' => 'nullable|string|max:20',
                'billing_address' => 'required|string|max:255',
                'billing_city' => 'required|string|max:255',
                'billing_state' => 'nullable|string|max:255',
                'billing_postal_code' => 'required|string|max:20',
                'billing_country' => 'required|string|max:255',
            ]);
            
            if ($billingValidator->fails()) {return response()->json(['errors' => $billingValidator->errors()], 422);
            }
        }
        
        // Get cart data
        $user = $this->getAuthenticatedUser($request);
        $sessionId = $request->header('X-Session-Id');
        
        if (!$user && !$sessionId) {
            return response()->json(['message' => 'No user or session ID provided'], 400);
        }
        
        // Get cart
        $cart = null;
        if ($user) {
            $cart = Cart::where('user_id', $user->id)->first();
        } elseif ($sessionId) {
            $cart = Cart::where('session_id', $sessionId)->where('user_id', null)->first();
        }
        
        // Validate cart ownership
        if (!$this->validateCartOwnership($request, $cart)) {
            return response()->json(['message' => 'Unauthorized access to cart'], 403);
        }
        
        // Get custom jacket cart items
        $customJacketItems = collect();
        if ($user) {
            // Authenticated user - get custom jacket items by user_id
            $customJacketItems = CustomJacketCartItem::where('user_id', $user->id)->get();
        } elseif ($sessionId) {
            // Guest user - get custom jacket items by session_id
            $customJacketItems = CustomJacketCartItem::where('session_id', $sessionId)->get();
        }
        
        // Check if either regular cart or custom jacket cart has items
        $hasRegularItems = $cart && $cart->items && !$cart->items->isEmpty();
        $hasCustomItems = $customJacketItems && !$customJacketItems->isEmpty();
        if (!$hasRegularItems && !$hasCustomItems) {
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
            $order->currency = $request->currency ?? 'EUR';
            
            // Set notes from request without adding shipping time
            $order->notes = $request->notes;
            
            // Use calculated totals from frontend if provided (in user's selected currency)
            // Otherwise fall back to calculating from cart items in EUR
            if ($request->has('calculated_subtotal') && $request->has('calculated_total')) {
                $order->subtotal = $request->calculated_subtotal;
                $order->tax = $request->calculated_tax ?? 0;
                $order->shipping = $request->calculated_shipping ?? 0;
                $order->total = $request->calculated_total;} else {
                // Fallback to old calculation method (will be in EUR)
                $order->subtotal = 0;
                $order->tax = 0;
                $order->shipping = 0;
                $order->total = 0;}
            
            $order->save();
            
            // Create order items from cart
            foreach ($cart->items as $cartItem) {
                // Check if the product variant exists and has sufficient stock
                $variant = ProductVariant::find($cartItem->product_variant_id);
                
                if (!$variant) {
                    throw new \Exception('Product variant not found: ' . $cartItem->product_variant_id);
                }
                                
                // Create snapshot of product data
                $productSnapshot = [
                    'product' => $variant->product->toArray(),
                    'variant' => $variant->toArray(),
                    'requested_quantity' => $cartItem->quantity
                ];
                
                // Create order item
                $orderItem = new OrderItem([
                    'order_id' => $order->id,
                    'product_variant_id' => $cartItem->product_variant_id,
                    'product_name' => $variant->product->name,
                    'variant_name' => $variant->name ?? ($variant->size . ' ' . $variant->color),
                    'price' => $variant->price ?? $variant->product->price,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => ($variant->price ?? $variant->product->price) * $cartItem->quantity,
                    'product_snapshot' => $productSnapshot,
                ]);
                
                $orderItem->save();
            }
            
            // Create order items from custom jacket cart
            foreach ($customJacketItems as $customItem) {// Create snapshot of custom jacket data
                $customJacketSnapshot = [
                    'custom_jacket' => [
                        'id' => $customItem->item_id,
                        'name' => $customItem->name,
                        'color' => $customItem->color,
                        'size' => $customItem->size,
                        'front_image_url' => $customItem->front_image_url,
                        'back_image_url' => $customItem->back_image_url,
                        'logos' => $customItem->logos,
                        'custom_description' => $customItem->custom_description,
                    ]
                ];
                
                // Create order item for custom jacket
                $orderItem = new OrderItem([
                    'order_id' => $order->id,
                    'product_variant_id' => null, // Custom items don't have variants
                    'product_name' => $customItem->name,
                    'variant_name' => 'Custom Design - ' . $customItem->size . ' - ' . $customItem->color,
                    'price' => $customItem->price,
                    'quantity' => $customItem->quantity,
                    'subtotal' => $customItem->price * $customItem->quantity,
                    'product_snapshot' => $customJacketSnapshot,
                ]);
                
                $orderItem->save();}
            
            // Calculate order totals only if we didn't use frontend calculated totals
            if (!$request->has('calculated_total')) {
                $order->calculateTotals();
            }
            
            // Save shipping address to user's address book if authenticated and it's a new address
            if ($user) {
                $this->saveShippingAddressToUserBook($request, $user);
            }
            
            DB::commit();
            
            // Process payment based on payment method
            if ($request->payment_method === 'stripe') {
                // For Stripe, we don't process payment here - it's handled separately
                // The order is created with 'pending' payment status
                $order->payment_status = 'pending';
                $order->save();
return response()->json([
                    'message' => 'Order created successfully. Please complete payment.',
                    'order' => $order->load('items'),
                    'payment_required' => true,
                    'payment_method' => 'stripe'
                ], 201);
            } else {
                // For other payment methods (credit_card, paypal) - mock success for now
                $order->payment_status = 'completed';
                $order->payment_transaction_id = 'TRANS_' . uniqid();
                $order->save();
                
                // Send Telegram notification for new order (COMMENTED OUT - use email instead)
                // try {
                //     $telegramService = new \App\Services\TelegramNotificationService();
                //     $telegramService->notifyNewOrder($order);
                // } catch (\Exception $e) {
                //     \Log::error('Telegram notification failed: ' . $e->getMessage());
                // }
                
                // Send email notification to admin
                try {
                    Mail::to('orders@nordflex.store')->send(new \App\Mail\NewOrderNotification($order));
                } catch (\Exception $e) {
                    \Log::error('Admin notification email failed: ' . $e->getMessage());
                }
                
                // Clear cart only after successful payment for non-Stripe methods
                $this->clearCartAfterPayment($order->id);
            }
            
            // Send order confirmation email
            try {
                Mail::to($order->shipping_email)->send(new OrderConfirmation($order));
            } catch (\Exception $e) {
                // Email sending failure shouldn't fail the order
            }
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
     * Clear cart after successful payment
     */
    public function clearCartAfterPayment($orderId)
    {
        $order = Order::find($orderId);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        try {
            DB::beginTransaction();
            
            // Clear the regular cart if it exists
            if ($order->user_id) {
                $cart = Cart::where('user_id', $order->user_id)->first();
                if ($cart) {
                    $cart->items()->delete();}
            } elseif ($order->session_id) {
                $cart = Cart::where('session_id', $order->session_id)->where('user_id', null)->first();
                if ($cart) {
                    $cart->items()->delete();}
            }

            // Clear custom jacket cart items
            $customJacketItems = collect();
            if ($order->user_id) {
                $customJacketItems = CustomJacketCartItem::where('user_id', $order->user_id)->get();
            } elseif ($order->session_id) {
                $customJacketItems = CustomJacketCartItem::where('session_id', $order->session_id)->get();
            }

            if ($customJacketItems->isNotEmpty()) {// Delete images from local storage
                $localImageService = app(\App\Services\LocalImageService::class);
                foreach ($customJacketItems as $customItem) {
                    // Extract local paths and delete
                    $frontPath = $this->extractLocalPathFromUrl($customItem->front_image_url);
                    $backPath = $this->extractLocalPathFromUrl($customItem->back_image_url);
                    
                    if ($frontPath) {
                        $localImageService->deleteImage($frontPath);
                    }
                    if ($backPath) {
                        $localImageService->deleteImage($backPath);
                    }}
                
                // Delete custom jacket items from database
                $customJacketItems->each(function ($item) {
                    $item->delete();
                });}

            DB::commit();
return response()->json([
                'message' => 'Cart cleared successfully after payment',
                'order_id' => $orderId
            ]);

        } catch (\Exception $e) {
            DB::rollback();
return response()->json([
                'message' => 'Failed to clear cart after payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update payment status for Stripe orders
     */
    public function updatePaymentStatus(Request $request, $orderId)
    {
        $validator = Validator::make($request->all(), [
            'payment_status' => 'required|string|in:pending,completed,failed',
            'payment_transaction_id' => 'required|string',
            'stripe_payment_intent_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::find($orderId);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $previousPaymentStatus = $order->payment_status;

        // Update payment status
        $order->payment_status = $request->payment_status;
        $order->payment_transaction_id = $request->payment_transaction_id;
        
        // Store Stripe payment intent ID if provided
        if ($request->stripe_payment_intent_id) {
            // Don't add Stripe payment intent to notes anymore
        }
        
        $order->save();
        // Send order confirmation email if payment status changed from pending to completed
        if ($previousPaymentStatus !== 'completed' && $request->payment_status === 'completed') {
            try {
                Mail::to($order->shipping_email)->send(new OrderConfirmation($order));
            } catch (\Exception $e) {
                // Email sending failure shouldn't fail the order
            }
        }
return response()->json([
            'message' => 'Payment status updated successfully',
            'order' => $order->load('items'),
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $sessionId = $request->header('X-Session-Id');
        
        $query = Order::with(['items.variant.images', 'items.variant.product.allImages']);
        
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
        
        // Ensure each item has access to the order for currency conversion
        $order->items->each(function ($item) use ($order) {
            $item->setRelation('order', $order);
        });
return response()->json($order);
    }

    /**
     * Admin: Get all orders
     */
    public function adminIndex(Request $request)
    {
        $query = Order::with(['items.variant.images', 'items.variant.product.images']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Search by order number or customer email
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', '%' . $search . '%')
                  ->orWhere('customer_email', 'like', '%' . $search . '%');
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['created_at', 'order_number', 'total_amount', 'status'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $orders = $query->paginate($perPage);
return response()->json([
            'data' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'has_more_pages' => $orders->hasMorePages(),
            ]
        ]);
    }

    /**
     * Admin: Update order status and tracking
     */
    public function adminUpdate(Request $request, $id)
    {$validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:pending,processing,shipped,delivered,cancelled',
            'tracking_number' => 'required_if:status,shipped|nullable|string|max:255',
            'shipping_service' => 'required_if:status,shipped|nullable|string|max:255',
            'notes' => 'sometimes|nullable|string|max:1000',
        ]);

        if ($validator->fails()) {return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Additional validation: if status is being set to shipped, ensure both tracking and service are provided
        if ($request->input('status') === 'shipped') {
            if (empty($request->input('tracking_number'))) {return response()->json([
                    'message' => 'Tracking number is required when setting order status to shipped',
                    'errors' => ['tracking_number' => ['Tracking number is required for shipped orders']]
                ], 422);
            }
            
            if (empty($request->input('shipping_service'))) {return response()->json([
                    'message' => 'Shipping service is required when setting order status to shipped',
                    'errors' => ['shipping_service' => ['Shipping service is required for shipped orders']]
                ], 422);
            }
        }

        $order = Order::find($id);
        
        if (!$order) {return response()->json(['message' => 'Order not found'], 404);
        }

        // Store previous status for email notification
        $previousStatus = $order->status;

        // Update only provided fields
        $updateData = array_filter($request->only(['status', 'tracking_number', 'shipping_service', 'notes']), function ($value) {
            return $value !== null;
        });$order->update($updateData);
        $order->refresh();// Send email notification if status changed
        if (isset($updateData['status']) && $updateData['status'] !== $previousStatus) {try {
                // Load the order with items before sending email
                $order->load('items');
                
                // Send email notification using the imported class
                Mail::to($order->shipping_email)->send(new OrderStatusUpdated($order, $previousStatus));
            } catch (\Exception $e) {
                // Email sending failure shouldn't fail the order
            }
        }
        
        // Load the updated order with items for the response
        $order->load('items');
return response()->json([
            'message' => 'Order updated successfully',
            'order' => $order
        ]);
    }
    
    /**
     * Pre-checkout validation to check stock availability
     */
    public function validateCheckout(Request $request)
    {
        try {
            $user = $request->user();
            $sessionId = $request->header('X-Session-Id');
            
            if (!$user && !$sessionId) {
                return response()->json(['message' => 'No user or session ID provided'], 400);
            }
            
            // Get cart data
            $cart = null;
            if ($user) {
                $cart = Cart::where('user_id', $user->id)->first();
            } elseif ($sessionId) {
                $cart = Cart::where('session_id', $sessionId)->first();
            }
            
            // Get custom jacket cart items
            $customJacketItems = collect();
            if ($user) {
                // Authenticated user - get custom jacket items by user_id
                $customJacketItems = CustomJacketCartItem::where('user_id', $user->id)->get();
            } elseif ($sessionId) {
                // Guest user - get custom jacket items by session_id
                $customJacketItems = CustomJacketCartItem::where('session_id', $sessionId)->get();
            }
            
            // Check if either regular cart or custom jacket cart has items
            $hasRegularItems = $cart && $cart->items && !$cart->items->isEmpty();
            $hasCustomItems = $customJacketItems && !$customJacketItems->isEmpty();
            
            if (!$hasRegularItems && !$hasCustomItems) {
                return response()->json(['message' => 'Cart is empty'], 400);
            }
            
            $validationResults = [
                'cart_valid' => true,
                'stock_issues' => [],
                'warnings' => [],
                'total_items' => 0,
                'estimated_total' => 0
            ];
            
            // Validate regular cart items stock
            if ($hasRegularItems) {
                foreach ($cart->items as $cartItem) {
                    $variant = ProductVariant::find($cartItem->product_variant_id);
                    
                    if (!$variant) {
                        $validationResults['stock_issues'][] = [
                            'type' => 'error',
                            'message' => 'Product variant not found',
                            'cart_item_id' => $cartItem->id,
                            'product_variant_id' => $cartItem->product_variant_id
                        ];
                        $validationResults['cart_valid'] = false;
                        continue;
                    }
                    
                    $validationResults['total_items'] += $cartItem->quantity;
                    $estimatedTotal = ($variant->price ?? $variant->product->price) * $cartItem->quantity;
                    $validationResults['estimated_total'] += $estimatedTotal;
                }
            }
            
            // Add custom jacket items to total
            if ($hasCustomItems) {
                foreach ($customJacketItems as $customItem) {
                    $validationResults['total_items'] += $customItem->quantity;
                    $validationResults['estimated_total'] += ($customItem->price * $customItem->quantity);
                }
            }
return response()->json($validationResults);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Checkout validation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Release stock reservation for cart items (called when cart is cleared or items removed)
     */
    public function releaseStockReservation(Request $request)
    {
        try {
            $user = $request->user();
            $sessionId = $request->header('X-Session-Id');
            
            if (!$user && !$sessionId) {
                return response()->json(['message' => 'No user or session ID provided'], 400);
            }
            
            // Get cart data
            $cart = null;
            if ($user) {
                $cart = Cart::where('user_id', $user->id)->first();
            } elseif ($sessionId) {
                $cart = Cart::where('session_id', $sessionId)->first();
            }
            
            if ($cart && $cart->items) {
                foreach ($cart->items as $cartItem) {
                    $variant = ProductVariant::find($cartItem->product_variant_id);
                    if ($variant) {
                        // Release reserved stock back to available stock
                        $variant->stock += $cartItem->quantity;
                        $variant->save();
                    }
                }
            }
return response()->json(['message' => 'Stock reservation released successfully']);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to release stock reservation',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Save shipping address to user's address book if authenticated
     */
    private function saveShippingAddressToUserBook(Request $request, $user)
    {
        try {
            // Check if user already has this address
            $existingAddress = $user->addresses()
                ->where('street', $request->shipping_address)
                ->where('city', $request->shipping_city)
                ->where('state', $request->shipping_state)
                ->where('postal_code', $request->shipping_postal_code)
                ->where('country', $request->shipping_country)
                ->first();
            
            if (!$existingAddress) {
                $address = new Address([
                    'user_id' => $user->id,
                    'type' => 'shipping',
                    'label' => 'My Address',
                    'name' => $request->shipping_name,
                    'street' => $request->shipping_address,
                    'city' => $request->shipping_city,
                    'state' => $request->shipping_state,
                    'postal_code' => $request->shipping_postal_code,
                    'country' => $request->shipping_country,
                    'phone' => $request->shipping_phone,
                    'is_default' => true, // The Address model will handle setting default
                ]);
                
                $address->save();
                return $address;
            }
            
            return $existingAddress;
            
        } catch (\Exception $e) {
            // Don't fail the order if address saving fails
            return null;
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
        } catch (\Exception $e) {}
        
        return null;
    }
    
    /**
     * Get authenticated user from request, handling both Auth0 and Sanctum
     */
    private function getAuthenticatedUser(Request $request)
    {
        // First, try to get user from the request (this works for Auth0 JWT tokens)
        $user = $request->user();// If no user from request, try to authenticate using Sanctum token
        if (!$user) {
            $user = $this->authenticateWithSanctum($request);}
        
        return $user;
    }
    
    /**
     * Validate cart ownership for checkout.
     */
    private function validateCartOwnership(Request $request, $cart)
    {
        $user = $request->user();
        $sessionId = $request->header('X-Session-Id');

        // If user is authenticated, check if the cart belongs to them
        if ($user) {
            return $cart && $cart->user_id === $user->id;
        }

        // If user is not authenticated, check if the cart is a session-based cart
        return $cart && $cart->user_id === null && $cart->session_id === $sessionId;
    }

    /**
     * Extract local path from local storage URL
     *
     * @param string|null $url
     * @return string|null
     */
    private function extractLocalPathFromUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        try {
            $baseUrl = config('app.url');
            
            // Remove the base URL to get the path
            if (strpos($url, $baseUrl) === 0) {
                $path = str_replace($baseUrl, '', $url);
                
                // Handle /storage/ pattern
                if (strpos($path, '/storage/') === 0) {
                    return str_replace('/storage/', '', $path);
                }
            }
            
            return null;
        } catch (\Exception $e) { return null;
        }
    }
}
