<?php

namespace Database\Seeders;

use App\Models\Category; // Make sure to import the Category model
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::firstOrCreate(['name' => 'Jackets']);
        Category::firstOrCreate(['name' => 'Bags']);
        Category::firstOrCreate(['name' => 'Wallets']);
        Category::firstOrCreate(['name' => 'Accessories']);
    }
}