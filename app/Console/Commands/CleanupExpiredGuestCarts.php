<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cart;
use Illuminate\Support\Facades\Log;

class CleanupExpiredGuestCarts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cart:cleanup-expired-guest-carts {--days=7 : Number of days after which guest carts expire}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired guest carts to free up database space and maintain security';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = now()->subDays($days);
        
        $this->info("Cleaning up guest carts older than {$days} days (before {$cutoffDate})...");
        
        // Find expired guest carts
        $expiredCarts = Cart::whereNotNull('session_id')
            ->whereNull('user_id')
            ->where('updated_at', '<', $cutoffDate)
            ->get();
        
        if ($expiredCarts->isEmpty()) {
            $this->info('No expired guest carts found.');
            return 0;
        }
        
        $this->info("Found {$expiredCarts->count()} expired guest carts to clean up.");
        
        $deletedCount = 0;
        $deletedItemsCount = 0;
        
        foreach ($expiredCarts as $cart) {
            // Count items before deletion for reporting
            $itemsCount = $cart->items()->count();
            
            // Delete cart items first
            $cart->items()->delete();
            
            // Delete the cart
            $cart->delete();
            
            $deletedCount++;
            $deletedItemsCount += $itemsCount;
            
            $this->line("Deleted cart {$cart->id} with {$itemsCount} items (session: {$cart->session_id})");
        }
        
        $this->info("Cleanup completed successfully!");
        $this->info("Deleted {$deletedCount} expired guest carts");
        $this->info("Deleted {$deletedItemsCount} total cart items");
        
        // Log the cleanup for monitoring
        Log::info('Expired guest carts cleanup completed', [
            'deleted_carts' => $deletedCount,
            'deleted_items' => $deletedItemsCount,
            'cutoff_date' => $cutoffDate->toISOString(),
            'executed_at' => now()->toISOString()
        ]);
        
        return 0;
    }
}
