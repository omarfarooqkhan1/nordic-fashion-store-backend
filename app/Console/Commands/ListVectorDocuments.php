<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\VectorStoreService;

class ListVectorDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vectorstore:list {--limit=10} {--type=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List documents in the vector store';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(VectorStoreService $vectorStoreService)
    {
        $limit = (int) $this->option('limit');
        $type = $this->option('type');
        
        try {
            // Simple approach: search for a broad term to get all documents
            $documents = $vectorStoreService->search('', $limit);
            
            if (empty($documents)) {
                $this->warn('No documents found in the collection.');
                return Command::SUCCESS;
            }
            
            $this->info("Showing " . count($documents) . " documents:");
            $this->newLine();
            
            foreach ($documents as $index => $doc) {
                $metadata = $doc['metadata'];
                $distance = $doc['distance'];
                
                $this->line("<comment>Document " . ($index + 1) . ":</comment>");
                $this->line("Type: " . ($metadata['type'] ?? 'unknown'));
                $this->line("Title: " . ($metadata['title'] ?? 'Untitled'));
                
                if (isset($metadata['question'])) {
                    $this->line("Question: " . $metadata['question']);
                }
                
                if (isset($metadata['category'])) {
                    $this->line("Category: " . $metadata['category']);
                }
                
                $this->line("Distance: " . number_format($distance, 4));
                $this->line("Content preview: " . substr($doc['document'], 0, 100) . "...");
                $this->newLine();
            }
        } catch (\Exception $e) {
            $this->error('Failed to list vector store documents: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }
}