<?php

namespace App\Services\Embeddings;

use HelgeSverre\Chromadb\Embeddings\EmbeddingFunction;
use HelgeSverre\Chromadb\Exceptions\EmbeddingException;
use Gemini\Client;
use Gemini\Resources\EmbeddingModel;

/**
 * Google Gemini Embeddings provider.
 *
 * Supports Google Gemini embedding models for generating vector representations
 * of text for use with ChromaDB.
 */
class GeminiEmbeddings implements EmbeddingFunction
{
    private EmbeddingModel $embeddingModel;

    /**
     * Create a new Gemini embeddings instance.
     *
     * @param  string  $apiKey  Your Google Gemini API key
     * @param  string  $model  The embedding model to use
     */
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'models/gemini-embedding-001' // Correct model name
    ) {
        $client = \Gemini::client($this->apiKey);
        $this->embeddingModel = $client->embeddingModel($this->model);
    }

    /**
     * Generate embeddings for the given texts using Google Gemini's API.
     *
     * @param  array<string>  $texts  The texts to embed
     * @return array<array<float>> The embedding vectors
     *
     * @throws EmbeddingException
     */
    public function generate(array $texts): array
    {
        if (empty($texts)) {
            throw EmbeddingException::emptyInput();
        }

        try {
            // Generate embeddings for all texts
            $response = $this->embeddingModel->batchEmbedContents($texts);
            
            // Extract just the embedding arrays
            return array_map(
                fn ($embedding) => $embedding->values,
                $response->embeddings
            );
        } catch (\Exception $e) {
            // Check if it's a rate limit error
            if (strpos($e->getMessage(), 'quota') !== false || strpos($e->getMessage(), 'rate') !== false) {
                throw EmbeddingException::rateLimitExceeded('Gemini', 60, $e->getMessage());
            }
            
            throw EmbeddingException::apiError('Gemini', $e->getMessage(), $e);
        }
    }
}