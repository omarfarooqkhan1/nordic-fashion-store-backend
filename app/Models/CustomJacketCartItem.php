<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomJacketCartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'session_id',
        'user_id',
        'name',
        'color',
        'size',
        'quantity',
        'price',
        'front_image_url',
        'back_image_url',
        'logos',
        'custom_description',
    ];

    protected $casts = [
        'logos' => 'array',
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
