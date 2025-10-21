<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\HeroImage;
use Illuminate\Support\Facades\Storage;

class HeroImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing hero images
        HeroImage::truncate();

        // Get all image files from the public directory
        $imageFiles = $this->getImageFiles();

        // Create hero images from the found files
        $sortOrder = 0;
        foreach ($imageFiles as $imagePath) {
            // Convert absolute path to relative URL
            if (strpos($imagePath, storage_path('app/public')) === 0) {
                // For storage images, use storage link
                $relativePath = str_replace(storage_path('app/public'), '', $imagePath);
                $imageUrl = '/storage' . $relativePath;
            } else {
                // For public images
                $relativePath = str_replace(public_path(), '', $imagePath);
                $imageUrl = '/' . ltrim($relativePath, '/');
            }

            // Generate alt text based on filename
            $filename = basename($imagePath, '.' . pathinfo($imagePath, PATHINFO_EXTENSION));
            $altText = $this->generateAltText($filename);

            HeroImage::create([
                'image_url' => $imageUrl,
                'alt_text' => $altText,
                'sort_order' => $sortOrder,
                'is_active' => true,
            ]);

            $sortOrder++;
        }

        $this->command->info("Seeded {$sortOrder} hero images from public directory");
    }

    /**
     * Get all image files from the hero-images directory
     */
    private function getImageFiles(): array
    {
        $imageFiles = [];

        // Get images from Laravel storage (images/hero-images directory)
        $storagePath = storage_path('app/public/images/hero-images');
        if (is_dir($storagePath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($storagePath, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $extension = strtolower($file->getExtension());
                    if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $imageFiles[] = $file->getPathname();
                    }
                }
            }
        }

        return $imageFiles;
    }

    /**
     * Check if directory should be skipped
     */
    private function shouldSkipDirectory(string $path): bool
    {
        $skipPatterns = [
            '/favicon/',
            '/models/',
            '/textures/',
            '/assets/images/blogs/', // Skip blogs directory as requested
            '/images/hero-images/', // Skip images/hero-images directory since we're seeding from it
        ];

        foreach ($skipPatterns as $pattern) {
            if (strpos($path, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate alt text based on filename
     */
    private function generateAltText(string $filename): string
    {
        // Clean up filename for alt text
        $altText = str_replace(['_', '-'], ' ', $filename);
        $altText = ucwords($altText);

        // Add some context for hero images
        return "Nordic fashion - {$altText}";
    }
}
