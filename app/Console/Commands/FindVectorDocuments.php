<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\VectorStoreService;

class FindVectorDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vectorstore:find {query} {--limit=5}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find documents in the vector store based on a query';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(VectorStoreService $vectorStoreService)
    {
        $query = $this->argument('query');
        $limit = (int) $this->option('limit');
        
        $this->info("Searching for documents matching: '{$query}' (limit: {$limit})");
        
        try {
            $documents = $vectorStoreService->search($query, $limit);
            
            if (empty($documents)) {
                $this->warn('No documents found matching your query.');
                return Command::SUCCESS;
            }
            
            $this->info("Found " . count($documents) . " documents:");
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
            $this->error('Failed to search vector store: ' . $e->getMessage());
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }
}