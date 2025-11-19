<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ChromaDB Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the ChromaDB vector database.
    |
    */

    'host' => env('CHROMA_DB_HOST', 'http://localhost'),
    'port' => env('CHROMA_DB_PORT', '9000'),
    'api_key' => env('CHROMA_DB_API_KEY', null),
    'default_collection' => env('CHROMA_DB_DEFAULT_COLLECTION', 'nordflex_knowledge_base'),
];