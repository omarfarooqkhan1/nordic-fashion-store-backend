<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user with password
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => 'password123', // Will be automatically hashed
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Create regular customer
        User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Customer User',
                'password' => 'password123', // Will be automatically hashed
                'role' => 'customer',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin and customer users created successfully!');
    }
}
