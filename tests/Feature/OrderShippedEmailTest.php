<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\OrderShipped;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderShippedEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_shipped_email_is_sent_when_status_updated_to_shipped()
    {
        Mail::fake();
        $order = Order::factory()->create([
            'status' => 'processing',
            'tracking_number' => null,
            'shipping_email' => 'customer@example.com',
        ]);

        $payload = [
            'status' => 'shipped',
            'tracking_number' => 'TRK123456',
        ];

        $this->actingAsAdmin();
        $response = $this->patchJson(route('admin.orders.update', $order->id), $payload);
        $response->assertOk();

        Mail::assertSent(OrderShipped::class, function ($mail) use ($order) {
            return $mail->order->id === $order->id;
        });
    }

    protected function actingAsAdmin()
    {
        // Implement admin authentication logic if needed
    }
}
