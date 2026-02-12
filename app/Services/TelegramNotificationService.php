<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    private $botToken;
    private $chatId;

    public function __construct()
    {
        $this->botToken = env('TELEGRAM_BOT_TOKEN');
        $this->chatId = env('TELEGRAM_CHAT_ID');
    }

    /**
     * Send a notification message to Telegram
     */
    public function sendMessage(string $message): bool
    {
        // Skip if Telegram is not configured
        if (empty($this->botToken) || empty($this->chatId)) {
            Log::info('Telegram notification skipped - not configured');
            return false;
        }

        try {
            $response = Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            if ($response->successful()) {
                Log::info('Telegram notification sent successfully');
                return true;
            } else {
                Log::error('Telegram notification failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Telegram notification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send new order notification
     */
    public function notifyNewOrder($order): bool
    {
        $message = "🛍️ <b>New Order Received!</b>\n\n";
        $message .= "📦 Order #: {$order->id}\n";
        $message .= "👤 Customer: {$order->customer_name}\n";
        $message .= "📧 Email: {$order->customer_email}\n";
        $message .= "💰 Total: €" . number_format($order->total_amount, 2) . "\n";
        $message .= "📍 Status: {$order->status}\n";
        $message .= "🕐 Time: " . $order->created_at->format('Y-m-d H:i:s');

        return $this->sendMessage($message);
    }
}
