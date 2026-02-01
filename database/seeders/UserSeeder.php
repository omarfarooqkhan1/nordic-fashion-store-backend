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
            ['email' => 'support@nordflex.store'],
            [
                'name' => 'Nordflex Support',
                'password' => '11223344',
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

        // Create 15 random customers for reviews
        $customerNames = [
            'Emma Johnson', 'Liam Smith', 'Olivia Brown', 'Noah Davis', 'Ava Wilson',
            'William Garcia', 'Sophia Martinez', 'James Anderson', 'Isabella Taylor', 'Benjamin Thomas',
            'Mia Hernandez', 'Lucas Moore', 'Charlotte Jackson', 'Henry White', 'Amelia Harris'
        ];

        foreach ($customerNames as $index => $name) {
            $email = strtolower(str_replace(' ', '.', $name)) . '@example.com';
            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => bcrypt('password123'),
                    'role' => 'customer',
                    'email_verified_at' => now(),
                ]
            );
        }

        $this->command->info('Users created successfully!');
        $this->command->info('- Admin: password authentication only');
        $this->command->info('- Customers: both Auth0 and password authentication');
        $this->command->info('- 15 additional customers created for reviews');
    }
}