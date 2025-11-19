<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\VectorStoreService;

class InitializeKnowledgeBase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vectorstore:initialize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize the vector store with all knowledge base data including terms and privacy policy';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(VectorStoreService $vectorStoreService)
    {
        $this->info('Initializing knowledge base...');
        
        try {
            $vectorStoreService->initializeKnowledgeBase();
            $this->info('Knowledge base initialized successfully!');
        } catch (\Exception $e) {
            $this->error('Failed to initialize knowledge base: ' . $e->getMessage());
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }
}