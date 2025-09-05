<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\BlogSeeder;

class SeedBlogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:blogs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the blogs table with sample Nordic fashion blog posts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Seeding blogs table...');
        
        $seeder = new BlogSeeder();
        $seeder->run();
        
        $this->info('Blogs seeded successfully!');
    }
}
