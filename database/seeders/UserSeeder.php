<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
// No need for Hash facade, as we're not storing local passwords.
// If you still have `use Illuminate\Support\Facades\Hash;` remove it.

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create an admin user for testing with Auth0
        User::firstOrCreate(
            ['email' => 'admin@example.com'], // Condition to find existing record
            [
                'name' => 'Admin User',
                'auth0_user_id' => 'auth0|test_admin_sub', // Placeholder Auth0 user ID (e.g., from an actual Auth0 user)
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Create a regular customer user
        User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Test Customer',
                'auth0_user_id' => 'auth0|test_customer_sub', // Another placeholder
                'role' => 'customer',
                'email_verified_at' => now(),
            ]
        );
    }
}