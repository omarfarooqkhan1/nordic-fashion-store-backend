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
        'shipping_name',
        'shipping_email',
        'shipping_phone',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_postal_code',
        'shipping_country',
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
}
