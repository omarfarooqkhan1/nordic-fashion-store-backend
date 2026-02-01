<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LocalImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LocalStorageController extends Controller
{
    private $localImageService;

    public function __construct(LocalImageService $localImageService)
    {
        $this->localImageService = $localImageService;
    }

    /**
     * Get storage usage information
     */
    public function getStorageUsage(): JsonResponse
    {
        try {
            $usage = $this->localImageService->getStorageUsage();
return response()->json([
                'success' => true,
                'data' => $usage
            ]);

        } catch (\Exception $e) { return response()->json([
                'success' => false,
                'message' => 'Failed to get storage usage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clean up old images
     */
    public function cleanupStorage(Request $request): JsonResponse
    {
        try {
            $daysOld = $request->input('days_old', 30);
            
            if ($daysOld < 1 || $daysOld > 365) {
                return response()->json([
                    'success' => false,
                    'message' => 'Days old must be between 1 and 365'
                ], 400);
            }

            $result = $this->localImageService->cleanupOldImages($daysOld);
return response()->json([
                'success' => true,
                'message' => 'Cleanup completed successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) { return response()->json([
                'success' => false,
                'message' => 'Failed to cleanup storage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a specific image
     */
    public function deleteImage(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'image_path' => 'required|string'
            ]);

            $imagePath = $request->input('image_path');
            $deleted = $this->localImageService->deleteImage($imagePath);
            
            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Image deleted successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete image'
                ], 500);
            }

        } catch (\Exception $e) { return response()->json([
                'success' => false,
                'message' => 'Failed to delete image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get optimized URL for an image
     */
    public function getOptimizedUrl(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'image_path' => 'required|string',
                'transformations' => 'nullable|array'
            ]);

            $imagePath = $request->input('image_path');
            $transformations = $request->input('transformations', []);
            
            $optimizedUrl = $this->localImageService->getOptimizedUrl($imagePath, $transformations);
return response()->json([
                'success' => true,
                'data' => [
                    'original_path' => $imagePath,
                    'optimized_url' => $optimizedUrl
                ]
            ]);

        } catch (\Exception $e) { return response()->json([
                'success' => false,
                'message' => 'Failed to get optimized URL: ' . $e->getMessage()
            ], 500);
        }
    }
}

