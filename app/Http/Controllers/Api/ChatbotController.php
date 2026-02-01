<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\KnowledgeBaseService;
use Gemini\Laravel\Facades\Gemini;
use Gemini\Data\SafetySetting;
use Gemini\Enums\HarmCategory;
use Gemini\Enums\HarmBlockThreshold;

class ChatbotController extends Controller
{
    protected $knowledgeBaseService;

    public function __construct(KnowledgeBaseService $knowledgeBaseService)
    {
        $this->knowledgeBaseService = $knowledgeBaseService;
    }

public function chat(Request $request)
    {
        $prompt = $request->input('prompt');
        
        if (empty($prompt)) {
            return response()->json([
                'success' => false,
                'error' => 'Prompt is required'
            ], 400);
        }

        // Create a cache key based on the prompt
        $cacheKey = 'chatbot_response_' . md5($prompt);

        // Check if we have a cached response
        $cachedResponse = Cache::get($cacheKey);
        if ($cachedResponse) {
            return response()->json([
                'success' => true,
                'response' => $cachedResponse,
                'timestamp' => now()->toISOString(),
                'cached' => true
            ]);
        }

        try {
            // Retrieve relevant information from knowledge base
            $relevantInfo = $this->knowledgeBaseService->retrieveRelevantInformation($prompt);
            $formattedInfo = $this->knowledgeBaseService->formatRetrievedInfo($relevantInfo);

            // Create a more contextual prompt
            $contextualPrompt = "You are a helpful fashion assistant for Nord Flex, a Nordic fashion store. "
                . "Use the following relevant information to answer the customer query accurately:\n\n"
                . $formattedInfo
                . "\n\nCustomer query: " . $prompt
                . "\n\nPlease provide a helpful and accurate response based on the information above.";

            // Generate response using Gemini with safety settings
            $generativeModel = Gemini::generativeModel('gemini-2.0-flash')
                ->withSafetySetting(new SafetySetting(
                    category: HarmCategory::HARM_CATEGORY_HARASSMENT,
                    threshold: HarmBlockThreshold::BLOCK_MEDIUM_AND_ABOVE
                ))
                ->withSafetySetting(new SafetySetting(
                    category: HarmCategory::HARM_CATEGORY_HATE_SPEECH,
                    threshold: HarmBlockThreshold::BLOCK_MEDIUM_AND_ABOVE
                ))
                ->withSafetySetting(new SafetySetting(
                    category: HarmCategory::HARM_CATEGORY_SEXUALLY_EXPLICIT,
                    threshold: HarmBlockThreshold::BLOCK_MEDIUM_AND_ABOVE
                ))
                ->withSafetySetting(new SafetySetting(
                    category: HarmCategory::HARM_CATEGORY_DANGEROUS_CONTENT,
                    threshold: HarmBlockThreshold::BLOCK_MEDIUM_AND_ABOVE
                ));

            $result = $generativeModel->generateContent($contextualPrompt);
            $responseText = $result->text();

            // Cache the response for 1 hour
            Cache::put($cacheKey, $responseText, 3600);
return response()->json([
                'success' => true,
                'response' => $responseText,
                'timestamp' => now()->toISOString(),
                'cached' => false
            ]);
        } catch (\Exception $e) {// Fallback response
            $fallbackResponse = "I apologize, but I'm currently experiencing technical difficulties. Please try again later or contact our support team at support@nordflex.store for immediate assistance.";
return response()->json([
                'success' => true,
                'response' => $fallbackResponse,
                'timestamp' => now()->toISOString(),
                'cached' => false
            ]);
        }
    }
}