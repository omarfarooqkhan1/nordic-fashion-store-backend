<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Make sure this trait is imported if you're using Sanctum

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'auth0_user_id', // THIS IS THE CORRECTED FIELD NAME
        'role',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * These should typically NOT include 'password' or 'remember_token'.
     *
     * @var list<string>
     */
    protected $hidden = [
        // No 'password' or 'remember_token' needed here for Auth0 setup
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            // No 'password' => 'hashed' casting needed here
        ];
    }

    /**
     * Get the orders for the user. (Assuming you have an Order model and relationship)
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}