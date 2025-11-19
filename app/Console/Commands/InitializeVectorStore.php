<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\VectorStoreService;

class InitializeVectorStore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vector-store:initialize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize the vector store with knowledge base data';

    /**
     * Execute the console command.
     */
    public function handle(VectorStoreService $vectorStore)
    {
        $this->info('Initializing vector store with knowledge base data...');
        
        try {
            $vectorStore->initializeKnowledgeBase();
            $this->info('Vector store initialized successfully!');
        } catch (\Exception $e) {
            $this->error('Failed to initialize vector store: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}