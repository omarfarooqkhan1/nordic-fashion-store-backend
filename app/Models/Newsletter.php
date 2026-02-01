<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Newsletter extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'name',
        'is_active',
        'subscribed_at',
        'unsubscribed_at',
        'subscription_source',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    /**
     * Scope to get only active subscribers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Subscribe an email to the newsletter
     */
    public static function subscribe(string $email, string $name = null, string $source = 'website')
    {
        $existing = static::where('email', $email)->first();
        
        if ($existing && $existing->is_active) {
            // Already subscribed and active
            return [
                'subscription' => $existing,
                'status' => 'already_subscribed',
                'message' => 'This email is already subscribed to our newsletter.'
            ];
        }
        
        $subscription = static::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'is_active' => true,
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
                'subscription_source' => $source,
            ]
        );
        
        return [
            'subscription' => $subscription,
            'status' => $existing ? 'resubscribed' : 'new_subscription',
            'message' => $existing 
                ? 'Welcome back! You have been resubscribed to our newsletter.' 
                : 'Successfully subscribed to our newsletter!'
        ];
    }

    /**
     * Unsubscribe an email from the newsletter
     */
    public static function unsubscribe(string $email)
    {
        return static::where('email', $email)->update([
            'is_active' => false,
            'unsubscribed_at' => now(),
        ]);
    }
}