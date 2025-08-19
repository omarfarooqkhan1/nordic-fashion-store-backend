<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;

class StripeController extends Controller
{
    public function __construct()
    {
        // Constructor left empty - API key will be set in each method
    }

    /**
     * Create a payment intent
     */
    public function createPaymentIntent(Request $request)
    {
        try {
            // Set Stripe API key
            $stripeSecret = config('services.stripe.secret');
            if (!$stripeSecret) {
                throw new \Exception('Stripe secret key not configured');
            }
            Stripe::setApiKey($stripeSecret);
            
            // Log the request for debugging
            Log::info('Creating payment intent', [
                'request_data' => $request->all(),
                'stripe_secret_configured' => !empty($stripeSecret),
                'stripe_secret_length' => strlen($stripeSecret),
                'stripe_secret_start' => substr($stripeSecret, 0, 10) . '...',
                'stripe_secret_end' => '...' . substr($stripeSecret, -10),
            ]);

            $validator = Validator::make($request->all(), [
                'amount' => 'required|integer|min:1',
                'currency' => 'required|string|size:3',
                'order_id' => 'nullable|integer|exists:orders,id',
                'customer_email' => 'required|email',
                'metadata' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $amount = $request->input('amount');
            $currency = strtolower($request->input('currency'));
            $customerEmail = $request->input('customer_email');
            $orderId = $request->input('order_id');
            $metadata = $request->input('metadata', []);

            // Add order ID to metadata if provided
            if ($orderId) {
                $metadata['order_id'] = $orderId;
            }

            // Create payment intent
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount, // Amount in cents
                'currency' => $currency,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'metadata' => $metadata,
                'receipt_email' => $customerEmail,
                'description' => $orderId ? "Order #{$orderId}" : "Payment",
            ]);

            Log::info('Payment intent created successfully', [
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $amount,
                'currency' => $currency,
                'order_id' => $orderId,
                'customer_email' => $customerEmail,
            ]);

            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $amount,
                'currency' => $currency,
            ]);

        } catch (ApiErrorException $e) {
            Log::error('Stripe API error creating payment intent', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Failed to create payment intent',
                'error' => $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            Log::error('Unexpected error creating payment intent', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm a payment
     */
    public function confirmPayment(Request $request)
    {
        try {
            // Set Stripe API key
            $stripeSecret = config('services.stripe.secret');
            if (!$stripeSecret) {
                throw new \Exception('Stripe secret key not configured');
            }
            Stripe::setApiKey($stripeSecret);
            
            $validator = Validator::make($request->all(), [
                'payment_intent_id' => 'required|string',
                'payment_method_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $paymentIntentId = $request->input('payment_intent_id');
            $paymentMethodId = $request->input('payment_method_id');

            // Retrieve the payment intent
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

            // Confirm the payment
            $paymentIntent->confirm([
                'payment_method' => $paymentMethodId,
                'return_url' => config('app.frontend_url') . '/checkout/success',
            ]);

            Log::info('Payment confirmed successfully', [
                'payment_intent_id' => $paymentIntentId,
                'payment_method_id' => $paymentMethodId,
                'status' => $paymentIntent->status,
            ]);

            return response()->json([
                'success' => true,
                'payment_intent' => [
                    'id' => $paymentIntent->id,
                    'status' => $paymentIntent->status,
                    'amount' => $paymentIntent->amount,
                    'currency' => $paymentIntent->currency,
                ],
            ]);

        } catch (ApiErrorException $e) {
            Log::error('Stripe API error confirming payment', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Failed to confirm payment',
                'error' => $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            Log::error('Unexpected error confirming payment', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment intent status
     */
    public function getPaymentIntentStatus($paymentIntentId)
    {
        try {
            // Set Stripe API key
            $stripeSecret = config('services.stripe.secret');
            if (!$stripeSecret) {
                throw new \Exception('Stripe secret key not configured');
            }
            Stripe::setApiKey($stripeSecret);
            
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

            Log::info('Payment intent status retrieved', [
                'payment_intent_id' => $paymentIntentId,
                'status' => $paymentIntent->status,
            ]);

            return response()->json([
                'status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount,
                'currency' => $paymentIntent->currency,
                'created' => $paymentIntent->created,
            ]);

        } catch (ApiErrorException $e) {
            Log::error('Stripe API error retrieving payment intent', [
                'error' => $e->getMessage(),
                'payment_intent_id' => $paymentIntentId,
            ]);

            return response()->json([
                'message' => 'Failed to retrieve payment intent',
                'error' => $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            Log::error('Unexpected error retrieving payment intent', [
                'error' => $e->getMessage(),
                'payment_intent_id' => $paymentIntentId,
            ]);

            return response()->json([
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Webhook handler for Stripe events
     */
    public function handleWebhook(Request $request)
    {
        // Set Stripe API key
        $stripeSecret = config('services.stripe.secret');
        if (!$stripeSecret) {
            throw new \Exception('Stripe secret key not configured');
        }
        Stripe::setApiKey($stripeSecret);
        
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $endpointSecret
            );

            Log::info('Stripe webhook received', [
                'event_type' => $event->type,
                'event_id' => $event->id,
            ]);

            // Handle the event
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentSucceeded($event->data->object);
                    break;
                case 'payment_intent.payment_failed':
                    $this->handlePaymentFailed($event->data->object);
                    break;
                default:
                    Log::info('Unhandled Stripe event type', ['type' => $event->type]);
            }

            return response()->json(['status' => 'success']);

        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid payload in Stripe webhook', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid payload'], 400);

        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Invalid signature in Stripe webhook', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature'], 400);

        } catch (\Exception $e) {
            Log::error('Unexpected error in Stripe webhook', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Unexpected error'], 500);
        }
    }

    /**
     * Handle successful payment
     */
    private function handlePaymentSucceeded($paymentIntent)
    {
        Log::info('Payment succeeded', [
            'payment_intent_id' => $paymentIntent->id,
            'amount' => $paymentIntent->amount,
            'metadata' => $paymentIntent->metadata,
        ]);

        // Update order payment status if order_id is in metadata
        if (isset($paymentIntent->metadata->order_id)) {
            $orderId = $paymentIntent->metadata->order_id;
            
            try {
                $order = \App\Models\Order::find($orderId);
                if ($order) {
                    $order->payment_status = 'completed';
                    $order->payment_transaction_id = $paymentIntent->id;
                    $order->notes = $order->notes . "\n\nStripe Payment Intent: " . $paymentIntent->id;
                    $order->save();
                    
                    Log::info('Order payment status updated to completed via webhook', [
                        'order_id' => $orderId,
                        'payment_intent_id' => $paymentIntent->id,
                    ]);
                    
                    // Clear the cart after successful payment
                    try {
                        $orderController = app(\App\Http\Controllers\Api\OrderController::class);
                        $orderController->clearCartAfterPayment($orderId);
                        Log::info('Cart cleared after successful payment via webhook', ['order_id' => $orderId]);
                    } catch (\Exception $cartError) {
                        Log::error('Failed to clear cart after payment via webhook', [
                            'order_id' => $orderId,
                            'error' => $cartError->getMessage(),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to update order payment status via webhook', [
                    'order_id' => $orderId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle failed payment
     */
    private function handlePaymentFailed($paymentIntent)
    {
        Log::info('Payment failed', [
            'payment_intent_id' => $paymentIntent->id,
            'amount' => $paymentIntent->amount,
            'metadata' => $paymentIntent->metadata,
        ]);

        // Update order payment status if order_id is in metadata
        if (isset($paymentIntent->metadata->order_id)) {
            $orderId = $paymentIntent->metadata->order_id;
            
            try {
                $order = \App\Models\Order::find($orderId);
                if ($order) {
                    $order->payment_status = 'failed';
                    $order->payment_transaction_id = $paymentIntent->id;
                    $order->notes = $order->notes . "\n\nStripe Payment Intent: " . $paymentIntent->id;
                    $order->save();
                    
                    Log::info('Order payment status updated to failed via webhook', [
                        'order_id' => $orderId,
                        'payment_intent_id' => $paymentIntent->id,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to update order payment status via webhook', [
                    'order_id' => $orderId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
