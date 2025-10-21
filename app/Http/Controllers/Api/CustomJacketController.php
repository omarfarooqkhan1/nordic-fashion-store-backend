<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomJacketCartItem;
use App\Services\LocalImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class CustomJacketController extends Controller
{
    protected $localImageService;

    public function __construct(LocalImageService $localImageService)
    {
        $this->localImageService = $localImageService;
    }

    /**
     * Add custom jacket to cart
     */
    public function addToCart(Request $request)
    {
        try {
            // Debug: Log what we're receiving
            Log::info('Custom jacket addToCart request received', [
                'all_input' => $request->all(),
                'files' => $request->allFiles(),
                'has_file_front' => $request->hasFile('front_image'),
                'has_file_back' => $request->hasFile('back_image'),
                'content_type' => $request->header('Content-Type'),
                'content_length' => $request->header('Content-Length'),
                'method' => $request->method(),
                'url' => $request->url(),
                'user' => $request->user() ? ['id' => $request->user()->id, 'email' => $request->user()->email] : null,
                'session_id_from_input' => $request->input('session_id'),
                'session_id_from_query' => $request->query('session_id'),
                'session_id_from_header' => $request->header('X-Session-Id')
            ]);

            $request->validate([
                'front_image' => 'required|image|max:10240', // 10MB max
                'back_image' => 'required|image|max:10240', // 10MB max
                'jacket_data' => 'required|string',
                'session_id' => 'nullable|string' // Make session_id optional for authenticated users
            ]);

            // First, try to get user from the request (this works for Auth0 JWT tokens)
            $user = $request->user();
            Log::info('CustomJacket addToCart - User from request (Auth0)', ['user' => $user ? ['id' => $user->id, 'email' => $user->email] : null]);
            
            // If no user from request, try to authenticate using Sanctum token
            if (!$user) {
                $user = $this->authenticateWithSanctum($request);
                Log::info('CustomJacket addToCart - User from Sanctum authentication', ['user' => $user ? ['id' => $user->id, 'email' => $user->email] : null]);
            }
            
            $sessionId = $request->input('session_id');
            
            // Validate access - either authenticated user or valid session ID
            if (!$user && !$sessionId) {
                return response()->json(['message' => 'Authentication required or valid session ID needed'], 400);
            }
            
            if ($user && $sessionId) {
                // Authenticated user with session ID - this shouldn't happen normally
                Log::warning('Authenticated user attempting to use session ID', [
                    'user_id' => $user->id,
                    'session_id' => $sessionId
                ]);
                // For authenticated users, ignore session ID and use user ID
                $sessionId = null;
            }

            $jacketData = json_decode($request->input('jacket_data'), true);
            if (!$jacketData) {
                return response()->json(['message' => 'Invalid jacket data'], 400);
            }

            // Get the uploaded files
            $frontImage = $request->file('front_image');
            $backImage = $request->file('back_image');

            // Validate files exist
            if (!$frontImage || !$backImage) {
                Log::error('Custom jacket images missing', [
                    'has_front' => $frontImage ? 'yes' : 'no',
                    'has_back' => $backImage ? 'yes' : 'no'
                ]);
                throw new \Exception('Front and back images are required');
            }

            // Validate files are valid
            if (!$frontImage->isValid() || !$backImage->isValid()) {
                Log::error('Custom jacket images invalid', [
                    'front_valid' => $frontImage->isValid(),
                    'back_valid' => $backImage->isValid(),
                    'front_error' => $frontImage->getError(),
                    'back_error' => $backImage->getError()
                ]);
                throw new \Exception('Invalid image files uploaded');
            }

            Log::info('Uploading custom jacket images', [
                'front_name' => $frontImage->getClientOriginalName(),
                'back_name' => $backImage->getClientOriginalName(),
                'front_size' => $frontImage->getSize(),
                'back_size' => $backImage->getSize()
            ]);

            // Upload to local storage
            try {
                $frontResult = $this->localImageService->uploadImage(
                    $frontImage
                );
            } catch (\Exception $e) {
                Log::error('Failed to upload front image', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw new \Exception('Failed to upload front image: ' . $e->getMessage());
            }

            try {
                $backResult = $this->localImageService->uploadImage(
                    $backImage
                );
            } catch (\Exception $e) {
                Log::error('Failed to upload back image', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw new \Exception('Failed to upload back image: ' . $e->getMessage());
            }

            if (!$frontResult || !$backResult) {
                Log::error('Upload returned null', [
                    'front_result' => $frontResult,
                    'back_result' => $backResult
                ]);
                throw new \Exception('Failed to upload images to local storage');
            }

            Log::info('Custom jacket images uploaded successfully', [
                'front_url' => $frontResult['secure_url'],
                'back_url' => $backResult['secure_url']
            ]);

            // Debug logging before creating the record
            Log::info('Creating custom jacket record with values:', [
                'user' => $user ? ['id' => $user->id, 'email' => $user->email] : null,
                'user_id_to_save' => $user ? $user->id : null,
                'session_id' => $sessionId,
                'jacket_data' => $jacketData
            ]);
            
            // Create custom jacket item in database
            $customJacket = CustomJacketCartItem::create([
                'item_id' => Str::uuid(),
                'session_id' => $sessionId, // Use the validated session ID
                'user_id' => $user ? $user->id : null, // Assign user ID if authenticated
                'name' => $jacketData['name'],
                'color' => $jacketData['color'],
                'size' => $jacketData['size'],
                'quantity' => $jacketData['quantity'],
                'price' => $jacketData['price'],
                'front_image_url' => $frontResult['secure_url'],
                'back_image_url' => $backResult['secure_url'],
                'logos' => $jacketData['logos'],
                'custom_description' => $jacketData['customDescription'] ?? null,
            ]);
            
            // Debug logging after creating the record
            Log::info('Custom jacket record created:', [
                'item_id' => $customJacket->item_id,
                'user_id_saved' => $customJacket->user_id,
                'session_id_saved' => $customJacket->session_id
            ]);

            Log::info('Custom jacket added to cart', [
                'session_id' => $sessionId,
                'jacket_id' => $customJacket->item_id,
                'front_image_url' => $frontResult['secure_url'],
                'back_image_url' => $backResult['secure_url']
            ]);

            // Return the created item in the expected format
            return response()->json([
                'id' => $customJacket->item_id,
                'type' => 'custom_jacket',
                'name' => $customJacket->name,
                'color' => $customJacket->color,
                'size' => $customJacket->size,
                'quantity' => $customJacket->quantity,
                'price' => $customJacket->price,
                'frontImageUrl' => $customJacket->front_image_url,
                'backImageUrl' => $customJacket->back_image_url,
                'logos' => $customJacket->logos,
                'customDescription' => $customJacket->custom_description,
                'createdAt' => $customJacket->created_at->toISOString(),
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to add custom jacket to cart', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'session_id' => $request->input('session_id')
            ]);

            return response()->json([
                'error' => 'Failed to add custom jacket to cart',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove custom jacket from cart
     */
    public function removeFromCart(Request $request, $customItemId)
    {
        try {
            // First, try to get user from the request (this works for Auth0 JWT tokens)
            $user = $request->user();
            Log::info('CustomJacket removeFromCart - User from request (Auth0)', ['user' => $user ? ['id' => $user->id, 'email' => $user->email] : null]);
            
            // If no user from request, try to authenticate using Sanctum token
            if (!$user) {
                $user = $this->authenticateWithSanctum($request);
                Log::info('CustomJacket removeFromCart - User from Sanctum authentication', ['user' => $user ? ['id' => $user->id, 'email' => $user->email] : null]);
            }
            
            $sessionId = $request->query('session_id');
            
            // Validate access - either authenticated user or valid session ID
            if (!$user && !$sessionId) {
                return response()->json(['message' => 'Authentication required or valid session ID needed'], 400);
            }
            
            // Debug logging for item search
            Log::info('Searching for custom jacket item:', [
                'customItemId' => $customItemId,
                'user' => $user ? ['id' => $user->id, 'email' => $user->email] : null,
                'sessionId' => $sessionId,
                'search_by_user_id' => $user ? $user->id : 'N/A',
                'search_by_session_id' => $sessionId ?? 'N/A'
            ]);
            
            // Find custom jacket item based on user or session
            $customItem = null;
            if ($user) {
                $customItem = CustomJacketCartItem::where('item_id', $customItemId)
                    ->where('user_id', $user->id)
                    ->first();
                Log::info('Searching by user_id:', ['user_id' => $user->id, 'found' => $customItem ? 'YES' : 'NO']);
            } else {
                $customItem = CustomJacketCartItem::where('item_id', $customItemId)
                    ->where('session_id', $sessionId)
                    ->first();
                Log::info('Searching by session_id:', ['session_id' => $sessionId, 'found' => $customItem ? 'YES' : 'NO']);
            }

            if (!$customItem) {
                return response()->json(['error' => 'Custom jacket not found'], 404);
            }

            // Delete images from local storage
            $this->deleteCustomJacketImages(
                $customItem->front_image_url,
                $customItem->back_image_url
            );

            // Delete from database
            $customItem->delete();

            Log::info('Custom jacket removed from cart', [
                'custom_item_id' => $customItemId,
                'user_id' => $user ? $user->id : null,
                'session_id' => $sessionId
            ]);

            return response()->json(['message' => 'Custom jacket removed from cart']);

        } catch (\Exception $e) {
            Log::error('Failed to remove custom jacket from cart', [
                'error' => $e->getMessage(),
                'custom_item_id' => $customItemId
            ]);

            return response()->json([
                'error' => 'Failed to remove custom jacket from cart',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get custom jacket cart items
     */
    public function getCart(Request $request)
    {
        try {
            // First, try to get user from the request (this works for Auth0 JWT tokens)
            $user = $request->user();
            Log::info('CustomJacket getCart - User from request (Auth0)', ['user' => $user ? ['id' => $user->id, 'email' => $user->email] : null]);
            
            // If no user from request, try to authenticate using Sanctum token
            if (!$user) {
                $user = $this->authenticateWithSanctum($request);
                Log::info('CustomJacket getCart - User from Sanctum authentication', ['user' => $user ? ['id' => $user->id, 'email' => $user->email] : null]);
            }
            
            $sessionId = $request->query('session_id'); // Use query parameter for GET requests
            
            // Validate access - either authenticated user or valid session ID
            if (!$user && !$sessionId) {
                return response()->json(['message' => 'Authentication required or valid session ID needed'], 400);
            }
            
            // Get custom jacket items based on user or session
            $customItems = collect();
            if ($user) {
                $customItems = CustomJacketCartItem::where('user_id', $user->id)->get();
            } else {
                $customItems = CustomJacketCartItem::where('session_id', $sessionId)->get();
            }

            $formattedItems = $customItems->map(function ($item) {
                return [
                    'id' => $item->item_id,
                    'type' => 'custom_jacket',
                    'name' => $item->name,
                    'color' => $item->color,
                    'size' => $item->size,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'frontImageUrl' => $item->front_image_url,
                    'backImageUrl' => $item->back_image_url,
                    'logos' => $item->logos,
                    'customDescription' => $item->custom_description,
                    'createdAt' => $item->created_at?->toISOString(),
                ];
            });

            return response()->json($formattedItems);

        } catch (\Exception $e) {
            Log::error('Failed to get custom jacket cart', [
                'error' => $e->getMessage(),
                'user_id' => $user ? $user->id : null,
                'session_id' => $sessionId
            ]);

            return response()->json([
                'error' => 'Failed to get custom jacket cart',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Validate that the session ID belongs to the requesting user
     */
    private function validateSessionOwnership(Request $request): bool
    {
        $user = $request->user();
        $sessionId = $request->input('session_id') ?? $request->query('session_id');
        
        if ($user) {
            // Authenticated users should not use session-based carts
            // They should have their cart tied to their user ID
            Log::warning('Authenticated user attempting to use session-based cart', [
                'user_id' => $user->id,
                'session_id' => $sessionId
            ]);
            return false;
        }
        
        // For guest users, validate session ID format and ensure it's not being hijacked
        if (!$sessionId || strlen($sessionId) < 20) {
            return false;
        }
        
        // Additional validation: ensure session ID is not being used by multiple IPs
        // This is a basic check - in production you might want more sophisticated session validation
        $clientIp = $request->ip();
        $existingSession = CustomJacketCartItem::where('session_id', $sessionId)->first();
        
        if ($existingSession) {
            // Log the IP for monitoring (you could store this in the database for better tracking)
            Log::info('Session access', [
                'session_id' => $sessionId,
                'client_ip' => $clientIp,
                'timestamp' => now()
            ]);
        }
        
        return true;
    }

    /**
     * Authenticate user using Sanctum token from Authorization header
     */
    private function authenticateWithSanctum(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }
        
        $token = substr($authHeader, 7); // Remove 'Bearer ' prefix
        
        try {
            $personalAccessToken = PersonalAccessToken::findToken($token);
            if ($personalAccessToken && (!$personalAccessToken->expires_at || $personalAccessToken->expires_at->isFuture())) {
                $user = $personalAccessToken->tokenable;
                if ($user) {
                    // For Sanctum, we need to set the user on the request
                    $request->setUserResolver(function () use ($user) {
                        return $user;
                    });
                    return $user;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to authenticate user with Sanctum token in CustomJacketController', ['error' => $e->getMessage()]);
        }
        
        return null;
    }

    /**
     * Update custom jacket quantity in cart
     */
    public function updateQuantity(Request $request, $customItemId)
    {
        try {
            Log::info('Custom jacket updateQuantity request received', [
                'custom_item_id' => $customItemId,
                'all_input' => $request->all(),
                'user' => $request->user() ? ['id' => $request->user()->id, 'email' => $request->user()->email] : null,
                'session_id_from_input' => $request->input('session_id'),
                'session_id_from_query' => $request->query('session_id'),
                'session_id_from_header' => $request->header('X-Session-Id')
            ]);

            $request->validate([
                'quantity' => 'required|integer|min:1|max:10',
                'session_id' => 'nullable|string'
            ]);

            // First, try to get user from the request (this works for Auth0 JWT tokens)
            $user = $request->user();
            Log::info('CustomJacket updateQuantity - User from request (Auth0)', ['user' => $user ? ['id' => $user->id, 'email' => $request->user()->email] : null]);
            
            // If no user from request, try to authenticate using Sanctum token
            if (!$user) {
                $user = $this->authenticateWithSanctum($request);
                Log::info('CustomJacket updateQuantity - User from Sanctum authentication', ['user' => $user ? ['id' => $user->id, 'email' => $user->email] : null]);
            }
            
            $sessionId = $request->query('session_id');
            
            // Validate access - either authenticated user or valid session ID
            if (!$user && !$sessionId) {
                return response()->json(['message' => 'Authentication required or valid session ID needed'], 400);
            }

            // Find the custom jacket cart item by UUID (item_id)
            $query = CustomJacketCartItem::where('item_id', $customItemId);
            
            if ($user) {
                $query->where('user_id', $user->id);
            } else {
                $query->where('session_id', $sessionId);
            }
            
            // Debug logging for item search
            Log::info('Searching for custom jacket item:', [
                'customItemId' => $customItemId,
                'user' => $user ? ['id' => $user->id, 'email' => $user->email] : null,
                'sessionId' => $sessionId,
                'search_by_user_id' => $user ? $user->id : 'N/A',
                'search_by_session_id' => $sessionId ?? 'N/A',
                'query_sql' => $query->toSql(),
                'query_bindings' => $query->getBindings()
            ]);
            
            $item = $query->first();
            
            if (!$item) {
                // Log what items exist for debugging
                $allItems = CustomJacketCartItem::all();
                Log::warning('Custom jacket not found. Available items:', [
                    'total_items' => $allItems->count(),
                    'items' => $allItems->map(function($item) {
                        return [
                            'id' => $item->id,
                            'item_id' => $item->item_id,
                            'user_id' => $item->user_id,
                            'session_id' => $item->session_id,
                            'name' => $item->name
                        ];
                    })->toArray()
                ]);
                
                return response()->json(['message' => 'Custom jacket not found in cart'], 404);
            }

            // Update the quantity
            $item->quantity = $request->input('quantity');
            $item->save();

            Log::info('Custom jacket quantity updated successfully', [
                'item_id' => $item->id,
                'new_quantity' => $item->quantity,
                'user_id' => $user ? $user->id : null,
                'session_id' => $sessionId
            ]);

            // Return the updated item
            return response()->json([
                'id' => $item->id,
                'color' => $item->color,
                'size' => $item->size,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'frontImageUrl' => $item->front_image_url,
                'backImageUrl' => $item->back_image_url,
                'logos' => $item->logos,
                'customDescription' => $item->custom_description,
                'createdAt' => $item->created_at->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update custom jacket quantity', [
                'error' => $e->getMessage(),
                'custom_item_id' => $customItemId,
                'user_id' => $user ?? null,
                'session_id' => $sessionId ?? null
            ]);

            return response()->json([
                'error' => 'Failed to update custom jacket quantity',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete custom jacket images from local storage
     *
     * @param string|null $frontImageUrl
     * @param string|null $backImageUrl
     * @return array
     */
    private function deleteCustomJacketImages(?string $frontImageUrl, ?string $backImageUrl): array
    {
        $results = [
            'deleted' => 0,
            'failed' => 0,
            'errors' => []
        ];

        try {
            // Extract local paths from URLs
            $frontPath = $this->extractLocalPathFromUrl($frontImageUrl);
            $backPath = $this->extractLocalPathFromUrl($backImageUrl);

            // Delete front image if exists
            if ($frontPath) {
                try {
                    if ($this->localImageService->deleteImage($frontPath)) {
                        $results['deleted']++;
                        Log::info('Front custom jacket image deleted from local storage', ['path' => $frontPath]);
                    } else {
                        $results['failed']++;
                        $results['errors'][] = "Failed to delete front image: {$frontPath}";
                    }
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "Error deleting front image {$frontPath}: " . $e->getMessage();
                }
            }

            // Delete back image if exists
            if ($backPath) {
                try {
                    if ($this->localImageService->deleteImage($backPath)) {
                        $results['deleted']++;
                        Log::info('Back custom jacket image deleted from local storage', ['path' => $backPath]);
                    } else {
                        $results['failed']++;
                        $results['errors'][] = "Failed to delete back image: {$backPath}";
                    }
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "Error deleting back image {$backPath}: " . $e->getMessage();
                }
            }

            Log::info('Custom jacket images cleanup completed', $results);
            
        } catch (\Exception $e) {
            Log::error('Failed to cleanup custom jacket images', [
                'error' => $e->getMessage(),
                'front_url' => $frontImageUrl,
                'back_url' => $backImageUrl
            ]);
            
            $results['errors'][] = 'General cleanup error: ' . $e->getMessage();
        }

        return $results;
    }

    /**
     * Extract local path from local storage URL
     *
     * @param string|null $url
     * @return string|null
     */
    private function extractLocalPathFromUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        try {
            $baseUrl = config('app.url');
            
            // Remove the base URL to get the path
            if (strpos($url, $baseUrl) === 0) {
                $path = str_replace($baseUrl, '', $url);
                
                // Handle /storage/ pattern
                if (strpos($path, '/storage/') === 0) {
                    return str_replace('/storage/', '', $path);
                }
            }
            
            return null;
        } catch (\Exception $e) {
            Log::warning('Failed to extract local path from URL', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
