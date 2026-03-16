<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_variant_id',
        'product_name',
        'variant_name',
        'size',
        'price',
        'quantity',
        'subtotal',
        'product_snapshot',
    ];

    protected $casts = [
        'product_snapshot' => 'array',
    ];

    /**
     * Append converted prices to the serialized array
     */
    protected $appends = ['converted_price', 'converted_subtotal', 'formatted_price', 'formatted_subtotal'];

    /**
     * Get the order that owns the item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product variant associated with the item.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Get converted price in order's currency
     */
    public function getConvertedPriceAttribute(): float
    {
        if (!$this->relationLoaded('order')) {
            $this->load('order');
        }
        return $this->order ? $this->order->convertFromEUR($this->price) : $this->price;
    }

    /**
     * Get converted subtotal in order's currency
     */
    public function getConvertedSubtotalAttribute(): float
    {
        if (!$this->relationLoaded('order')) {
            $this->load('order');
        }
        return $this->order ? $this->order->convertFromEUR($this->subtotal) : $this->subtotal;
    }

    /**
     * Get formatted price in order's currency
     */
    public function getFormattedPriceAttribute(): string
    {
        if (!$this->relationLoaded('order')) {
            $this->load('order');
        }
        return $this->order ? $this->order->getFormattedPrice($this->price) : '€' . number_format($this->price, 2);
    }

    /**
     * Get formatted subtotal in order's currency
     */
    public function getFormattedSubtotalAttribute(): string
    {
        if (!$this->relationLoaded('order')) {
            $this->load('order');
        }
        return $this->order ? $this->order->getFormattedPrice($this->subtotal) : '€' . number_format($this->subtotal, 2);
    }
}
