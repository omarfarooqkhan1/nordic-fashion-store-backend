<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'order_number',
        'status',
        'subtotal',
        'tax',
        'shipping',
        'total',
        'notes',
        'tracking_number',
        'shipping_name',
        'shipping_email',
        'shipping_phone',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_postal_code',
        'billing_same_as_shipping',
        'billing_name',
        'billing_email',
        'billing_phone',
        'billing_address',
        'billing_city',
        'billing_state',
        'billing_postal_code',
        'billing_country',
        'payment_method',
        'payment_status',
        'payment_transaction_id',
        'currency',
        'notes',
        'tracking_number',
        'shipping_service',
    ];

    /**
     * Generate a unique order number.
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $timestamp = now()->format('YmdHis');
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
        return $prefix . $timestamp . $random;
    }

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items for the order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    
    /**
     * Calculate order totals.
     */
    public function calculateTotals(): void
    {
        // Calculate subtotal from items
        $subtotal = $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
        
        // Set values
        $this->subtotal = $subtotal;
        $this->tax = $subtotal * 0.25; // 25% VAT for EU countries
        $this->shipping = $subtotal > 100 ? 0 : 9.99; // Free shipping over €100
        $this->total = $this->subtotal + $this->tax + $this->shipping;
        
        $this->save();
    }

    /**
     * Get currency symbol for the order
     */
    public function getCurrencySymbol(): string
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
        
        return $symbols[$this->currency ?? 'EUR'] ?? '€';
    }

    /**
     * Convert EUR amount to order's currency
     */
    public function convertFromEUR(float $eurAmount): float
    {
        // If order currency is EUR, no conversion needed
        if ($this->currency === 'EUR') {
            return $eurAmount;
        }

        // Exchange rates from EUR (same as frontend)
        $exchangeRates = [
            'USD' => 1.10, 'GBP' => 0.85, 'JPY' => 130.0, 'CNY' => 7.8, 'AUD' => 1.55, 'CAD' => 1.45, 'CHF' => 0.98,
            'SEK' => 11.2, 'NOK' => 11.8, 'DKK' => 7.45, 'PLN' => 4.35, 'CZK' => 24.5, 'HUF' => 385.0, 'ISK' => 145.0,
            'HKD' => 8.6, 'SGD' => 1.48, 'NZD' => 1.68, 'KRW' => 1420.0, 'TWD' => 34.5, 'THB' => 38.5, 'MYR' => 4.95,
            'PHP' => 61.0, 'IDR' => 16800.0, 'VND' => 26500.0, 'INR' => 91.5, 'PKR' => 305.0, 'BDT' => 118.0, 'LKR' => 325.0,
            'MXN' => 19.8, 'BRL' => 5.45, 'ARS' => 365.0, 'CLP' => 920.0, 'COP' => 4350.0, 'PEN' => 4.15, 'UYU' => 42.5,
            'AED' => 4.05, 'SAR' => 4.12, 'QAR' => 4.0, 'KWD' => 0.33, 'BHD' => 0.415, 'OMR' => 0.42, 'JOD' => 0.78,
            'ILS' => 4.1, 'EGP' => 54.0, 'ZAR' => 20.5, 'NGN' => 1650.0, 'KES' => 142.0, 'GHS' => 15.8, 'TRY' => 32.5, 'RUB' => 92.0
        ];

        $rate = $exchangeRates[$this->currency] ?? 1.0;
        return $eurAmount * $rate;
    }

    /**
     * Get formatted price in order's currency (converted from EUR)
     */
    public function getFormattedPrice(float $eurAmount): string
    {
        $convertedAmount = $this->convertFromEUR($eurAmount);
        return $this->getCurrencySymbol() . number_format($convertedAmount, 2);
    }
}
