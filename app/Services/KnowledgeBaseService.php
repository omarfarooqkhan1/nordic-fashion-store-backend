<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Faq;

class KnowledgeBaseService
{
    protected $vectorStore;

    public function __construct(VectorStoreService $vectorStore)
    {
        $this->vectorStore = $vectorStore;
    }

    /**
     * Retrieve relevant information based on the user query
     *
     * @param string $query
     * @return array
     */
    public function retrieveRelevantInformation(string $query): array
    {
        // Use the vector store to search for relevant documents
        $documents = $this->vectorStore->search($query, 5);
        
        // Format the retrieved documents
        $formattedInfo = $this->vectorStore->formatRetrievedDocuments($documents);
        
        return [
            'documents' => $documents,
            'formatted' => $formattedInfo
        ];
    }
    
    /**
     * Format the retrieved information for the chatbot prompt
     *
     * @param array $info
     * @return string
     */
    public function formatRetrievedInfo(array $info): string
    {
        return $info['formatted'] ?? '';
    }
}