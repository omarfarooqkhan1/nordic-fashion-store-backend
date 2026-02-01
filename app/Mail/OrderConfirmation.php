<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(env('MAIL_FROM_ADDRESS', 'noreply@nordflex.store'), env('MAIL_FROM_NAME', 'Nord Flex')),
            subject: 'Order Confirmation - #' . $this->order->order_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Load order items with their variant and product relationships
        $orderItems = $this->order->items()->with([
            'variant.product.allImages',
            'variant.images'
        ])->get();

        // Add product image to each order item
        $orderItems = $orderItems->map(function ($item) {
            $productImage = null;
            
            // Check if this is a custom jacket item
            if (isset($item->product_snapshot['custom_jacket'])) {
                // For custom jackets, use the front image from the snapshot
                $customJacket = $item->product_snapshot['custom_jacket'];
                $productImage = $customJacket['front_image_url'] ?? null;
                
                // Convert relative URLs to absolute URLs for email display
                if ($productImage) {
                    if (!str_starts_with($productImage, 'http')) {
                        $productImage = config('app.url') . $productImage;
                    }
                }
            } else {
                // For regular products, try to get variant's image first, then product's image
                if ($item->variant) {
                    // Try to get variant's first image
                    if ($item->variant->images && $item->variant->images->count() > 0) {
                        $image = $item->variant->images->first();
                        $productImage = $image->url;
                        
                        // Convert relative URLs to absolute URLs for email display
                        if (!str_starts_with($productImage, 'http')) {
                            $productImage = config('app.url') . $productImage;
                        }
                    }
                    // Fallback to product's first image
                    elseif ($item->variant->product && $item->variant->product->allImages && $item->variant->product->allImages->count() > 0) {
                        $image = $item->variant->product->allImages->first();
                        $productImage = $image->url;
                        
                        // Convert relative URLs to absolute URLs for email display
                        if (!str_starts_with($productImage, 'http')) {
                            $productImage = config('app.url') . $productImage;
                        }
                    }
                }
            }
            
            $item->product_image = $productImage;
            return $item;
        });

        return new Content(
            view: 'emails.order-confirmation',
            with: [
                'order' => $this->order,
                'customerName' => $this->order->shipping_name,
                'orderNumber' => $this->order->order_number,
                'orderTotal' => $this->order->total,
                'orderItems' => $orderItems,
                'shippingAddress' => [
                    'name' => $this->order->shipping_name,
                    'address' => $this->order->shipping_address,
                    'city' => $this->order->shipping_city,
                    'state' => $this->order->shipping_state,
                    'postal_code' => $this->order->shipping_postal_code,
                    'country' => $this->order->shipping_country,
                ],
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
