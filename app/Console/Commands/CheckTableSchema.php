<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckTableSchema extends Command
{
    protected $signature = 'check:schema {table}';
    protected $description = 'Check table schema';

    public function handle()
    {
        $table = $this->argument('table');
        
        if (!Schema::hasTable($table)) {
            $this->error("Table {$table} does not exist");
            return;
        }

        $columns = Schema::getColumnListing($table);
        $this->info("Columns in {$table} table:");
        
        foreach ($columns as $column) {
            $this->line("- {$column}");
        }
        
        // Get detailed column info
        $this->info("\nDetailed column information:");
        $columnDetails = DB::select("DESCRIBE {$table}");
        
        foreach ($columnDetails as $detail) {
            $this->line("- {$detail->Field}: {$detail->Type} " . 
                      ($detail->Null === 'YES' ? '(nullable)' : '(required)') .
                      ($detail->Key ? " [{$detail->Key}]" : '') .
                      ($detail->Default !== null ? " default: {$detail->Default}" : ''));
        }
    }
}
