<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CloudinaryService;

class CloudinaryStorageManagement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cloudinary:manage 
                            {action : The action to perform (usage|cleanup|optimize)}
                            {--days=30 : For cleanup: days old to delete unused images}
                            {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Cloudinary storage: check usage, cleanup old images, optimize storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cloudinaryService = app(CloudinaryService::class);
        $action = $this->argument('action');

        switch ($action) {
            case 'usage':
                $this->showUsage($cloudinaryService);
                break;
                
            case 'cleanup':
                $this->cleanupImages($cloudinaryService);
                break;
                
            case 'optimize':
                $this->optimizeStorage($cloudinaryService);
                break;
                
            default:
                $this->error("Unknown action: {$action}");
                $this->info("Available actions: usage, cleanup, optimize");
                return 1;
        }

        return 0;
    }

    private function showUsage(CloudinaryService $cloudinaryService)
    {
        $this->info('📊 Checking Cloudinary storage usage...');
        
        $usage = $cloudinaryService->getStorageUsage();
        
        if (!$usage) {
            $this->error('Failed to retrieve storage usage information.');
            return;
        }

        $this->table(
            ['Metric', 'Value'],
            [
                ['Storage Used', "{$usage['used_mb']} MB / {$usage['limit_mb']} MB ({$usage['percentage_used']}%)"],
                ['Transformations Used', number_format($usage['transformations_used']) . ' / 25,000'],
                ['Bandwidth Used', "{$usage['bandwidth_used_mb']} MB"],
            ]
        );

        if ($usage['percentage_used'] > 80) {
            $this->warn('⚠️  Storage usage is high! Consider running cleanup.');
        } elseif ($usage['percentage_used'] > 95) {
            $this->error('🚨 Storage nearly full! Run cleanup immediately.');
        } else {
            $this->info('✅ Storage usage is within normal limits.');
        }
    }

    private function cleanupImages(CloudinaryService $cloudinaryService)
    {
        $days = $this->option('days');
        $force = $this->option('force');

        $this->info("🧹 Cleaning up unused images older than {$days} days...");

        if (!$force) {
            if (!$this->confirm('This will permanently delete unused images. Continue?')) {
                $this->info('Cleanup cancelled.');
                return;
            }
        }

        $result = $cloudinaryService->cleanupOldImages($days);

        $this->table(
            ['Metric', 'Value'],
            [
                ['Images Deleted', $result['deleted']],
                ['Failed Deletions', $result['failed']],
                ['Storage Freed', "{$result['freed_mb']} MB"],
            ]
        );

        if ($result['deleted'] > 0) {
            $this->info("✅ Cleanup completed! Freed {$result['freed_mb']} MB of storage.");
        } else {
            $this->info('No unused images found to delete.');
        }
    }

    private function optimizeStorage(CloudinaryService $cloudinaryService)
    {
        $this->info('🔧 Running storage optimization...');

        // Show current usage
        $this->showUsage($cloudinaryService);
        
        // Auto-cleanup if storage is getting full
        $usage = $cloudinaryService->getStorageUsage();
        
        if ($usage && $usage['percentage_used'] > 80) {
            $this->warn('Storage usage high, running automatic cleanup...');
            $result = $cloudinaryService->cleanupOldImages(60);
            
            if ($result['deleted'] > 0) {
                $this->info("Auto-cleanup freed {$result['freed_mb']} MB");
            }
        }

        $this->info('💡 Optimization tips:');
        $this->line('• Images are automatically compressed on upload');
        $this->line('• Use ZIP uploads for better compression');
        $this->line('• Run cleanup regularly: php artisan cloudinary:manage cleanup');
        $this->line('• Monitor usage: php artisan cloudinary:manage usage');
    }
}
