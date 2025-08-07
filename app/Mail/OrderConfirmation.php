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
            from: new Address(env('MAIL_FROM_ADDRESS', 'noreply@nordicskin.com'), env('MAIL_FROM_NAME', 'Nordic Fashion Store')),
            subject: 'Order Confirmation - #' . $this->order->order_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order-confirmation',
            with: [
                'order' => $this->order,
                'customerName' => $this->order->shipping_name,
                'orderNumber' => $this->order->order_number,
                'orderTotal' => $this->order->total,
                'orderItems' => $this->order->items,
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
