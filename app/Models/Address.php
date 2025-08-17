<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'label',
        'street',
        'city',
        'state',
        'postal_code',
        'country',
        'is_default',
    ];

    protected $attributes = [
        'type' => 'home',
        'label' => 'Default Address',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the user that owns the address.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Boot the model to handle default address logic.
     */
    protected static function boot()
    {
        parent::boot();

        // When creating a new address
        static::creating(function ($address) {
            // If this is the first address for the user, make it default
            $userAddressCount = static::where('user_id', $address->user_id)->count();
            if ($userAddressCount === 0) {
                $address->is_default = true;
            }

            // If setting as default, unset other defaults for this user
            if ($address->is_default) {
                static::where('user_id', $address->user_id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });

        // When updating an address
        static::updating(function ($address) {
            // If setting as default, unset other defaults for this user
            if ($address->is_default && $address->isDirty('is_default')) {
                static::where('user_id', $address->user_id)
                    ->where('id', '!=', $address->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }
}
