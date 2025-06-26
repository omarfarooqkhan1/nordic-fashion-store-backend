<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'auth0_user_id',
        'role',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user is using Auth0 authentication
     */
    public function isAuth0User(): bool
    {
        return !empty($this->auth0_user_id);
    }

    /**
     * Check if user is using traditional password authentication
     */
    public function isPasswordUser(): bool
    {
        return !empty($this->password);
    }

    /**
     * Check if user has admin role
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user has customer role
     */
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Check if admin user (must use password auth only)
     */
    public function isValidAdmin(): bool
    {
        return $this->isAdmin() && $this->isPasswordUser() && !$this->isAuth0User();
    }

    /**
     * Check if customer user (can use both auth methods)
     */
    public function isValidCustomer(): bool
    {
        return $this->isCustomer() && ($this->isAuth0User() || $this->isPasswordUser());
    }

    /**
     * Get authentication method
     */
    public function getAuthMethod(): string
    {
        if ($this->isAuth0User()) {
            return 'auth0';
        } elseif ($this->isPasswordUser()) {
            return 'password';
        }
        return 'unknown';
    }

    /**
     * Get the orders for the user.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Scope to get only admin users (password only)
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin')->whereNotNull('password');
    }

    /**
     * Scope to get customer users (both auth methods)
     */
    public function scopeCustomers($query)
    {
        return $query->where('role', 'customer');
    }

    /**
     * Scope to get Auth0 customers
     */
    public function scopeAuth0Customers($query)
    {
        return $query->where('role', 'customer')->whereNotNull('auth0_user_id');
    }

    /**
     * Scope to get password-based customers
     */
    public function scopePasswordCustomers($query)
    {
        return $query->where('role', 'customer')->whereNotNull('password');
    }
}