<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin users (password only)
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => 'admin123',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Create password-based customer
        User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Password Customer',
                'password' => 'customer123',
                'role' => 'customer',
                'email_verified_at' => now(),
            ]
        );

        // Create Auth0-based customer
        User::firstOrCreate(
            ['email' => 'auth0customer@example.com'],
            [
                'name' => 'Auth0 Customer',
                'auth0_user_id' => 'auth0|sample123456',
                'role' => 'customer',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Users created successfully!');
        $this->command->info('- Admin: password authentication only');
        $this->command->info('- Customers: both Auth0 and password authentication');
    }
}