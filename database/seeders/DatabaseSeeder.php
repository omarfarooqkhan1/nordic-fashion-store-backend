<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Image;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,      // Now correct for Auth0
            CategorySeeder::class,
            ProductSeeder::class,
            // OrderSeeder::class, // Uncomment if you want to seed orders too
        ]);
    }
}
