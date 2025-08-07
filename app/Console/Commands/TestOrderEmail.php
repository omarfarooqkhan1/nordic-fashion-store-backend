<?php

namespace App\Console\Commands;

use App\Mail\OrderConfirmation;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestOrderEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:order-email {order_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the order confirmation email with a specific order';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderId = $this->argument('order_id');
        
        $order = Order::with('items')->find($orderId);
        
        if (!$order) {
            $this->error("Order with ID {$orderId} not found.");
            return 1;
        }
        
        try {
            Mail::to($order->shipping_email)->send(new OrderConfirmation($order));
            $this->info("Order confirmation email sent successfully to {$order->shipping_email}");
            $this->info("Check the logs for the email content (MAIL_MAILER=log)");
        } catch (\Exception $e) {
            $this->error("Failed to send email: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
