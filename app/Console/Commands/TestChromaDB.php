<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use HelgeSverre\Chromadb\Chromadb;

class TestChromaDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vectorstore:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test ChromaDB connection and operations';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $this->info('Testing ChromaDB connection...');
            
            // Create ChromaDB client
            $client = new Chromadb(
                token: config('chromadb.api_key'),
                host: config('chromadb.host'),
                port: config('chromadb.port'),
                tenant: 'default_tenant',
                database: 'default_database'
            );
            
            $this->info('Client created successfully');
            
            // Test heartbeat
            $this->info('Testing heartbeat...');
            $collections = $client->collections();
            $this->info('Collections resource created');
            
            // List collections
            $this->info('Listing collections...');
            $response = $collections->list();
            $collectionList = $response->json();
            $this->info('Collections: ' . json_encode($collectionList));
            
            // Create a test collection
            $this->info('Creating test collection...');
            $collectionName = 'test_collection_' . time();
            $createResponse = $collections->create(
                name: $collectionName,
                getOrCreate: true
            );
            $createdCollection = $createResponse->json();
            $collectionId = $createdCollection['id']; // Get the actual collection ID
            $this->info('Created collection ID: ' . $collectionId);
            
            // Get the collection
            $this->info('Getting collection...');
            $getResponse = $collections->get($collectionId); // Use ID instead of name
            $this->info('Get response: ' . json_encode($getResponse->json()));
            
            // Add a test document
            $this->info('Adding test document...');
            $items = $client->items();
            $addResponse = $items->add(
                collectionId: $collectionId, // Use ID instead of name
                ids: ['test_id_1'],
                embeddings: [[0.1, 0.2, 0.3]],
                metadatas: [['type' => 'test', 'source' => 'test']],
                documents: ['This is a test document']
            );
            $this->info('Add response: ' . json_encode($addResponse->json()));
            
            // Count items
            $this->info('Counting items...');
            $count = $items->count($collectionId); // Use ID instead of name
            $this->info("Item count: {$count}");
            
            // Delete test collection
            $this->info('Deleting test collection...');
            $deleteResponse = $collections->delete($collectionId); // Use ID instead of name
            $this->info('Delete response: ' . json_encode($deleteResponse->json()));
            
            $this->info('Test completed successfully!');
        } catch (\Exception $e) {
            $this->error('Test failed: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }
}