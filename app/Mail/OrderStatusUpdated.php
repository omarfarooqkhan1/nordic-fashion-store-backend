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

class OrderStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $previousStatus;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, string $previousStatus = null)
    {
        $this->order = $order;
        $this->previousStatus = $previousStatus;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $statusMessages = [
            'pending' => 'Order Received',
            'processing' => 'Order Processing',
            'shipped' => 'Order Shipped',
            'delivered' => 'Order Delivered',
            'cancelled' => 'Order Cancelled',
        ];

        $subject = $statusMessages[$this->order->status] ?? 'Order Status Update';
        
        return new Envelope(
            from: new Address(env('MAIL_FROM_ADDRESS', 'noreply@nordflex.store'), env('MAIL_FROM_NAME', 'Nord Flex')),
            subject: $subject . ' - #' . $this->order->order_number,
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
            view: 'emails.order-status-updated',
            with: [
                'order' => $this->order,
                'orderItems' => $orderItems,
                'previousStatus' => $this->previousStatus,
                'customerName' => $this->order->shipping_name,
                'orderNumber' => $this->order->order_number,
                'currentStatus' => $this->order->status,
                'trackingNumber' => $this->order->tracking_number,
                'shippingService' => $this->order->shipping_service,
                'currencySymbol' => $this->getCurrencySymbol($this->order->currency ?? 'EUR'),
            ],
        );
    }

    /**
     * Get currency symbol for the given currency code.
     */
    private function getCurrencySymbol(string $currencyCode): string
    {
        $symbols = [
            'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥', 'CNY' => '¥', 'AUD' => 'A$', 'CAD' => 'C$', 'CHF' => 'CHF',
            'SEK' => 'kr', 'NOK' => 'kr', 'DKK' => 'kr', 'PLN' => 'zł', 'CZK' => 'Kč', 'HUF' => 'Ft', 'ISK' => 'kr',
            'HKD' => 'HK$', 'SGD' => 'S$', 'NZD' => 'NZ$', 'KRW' => '₩', 'TWD' => 'NT$', 'THB' => '฿', 'MYR' => 'RM',
            'PHP' => '₱', 'IDR' => 'Rp', 'VND' => '₫', 'INR' => '₹', 'PKR' => '₨', 'BDT' => '৳', 'LKR' => '₨',
            'MXN' => '$', 'BRL' => 'R$', 'ARS' => '$', 'CLP' => '$', 'COP' => '$', 'PEN' => 'S/', 'UYU' => '$U',
            'AED' => 'د.إ', 'SAR' => '﷼', 'QAR' => '﷼', 'KWD' => 'د.ك', 'BHD' => '.د.ب', 'OMR' => '﷼', 'JOD' => 'د.ا',
            'ILS' => '₪', 'EGP' => 'E£', 'ZAR' => 'R', 'NGN' => '₦', 'KES' => 'KSh', 'GHS' => '₵', 'TRY' => '₺', 'RUB' => '₽'
        ];
        
        return $symbols[$currencyCode] ?? '€';
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