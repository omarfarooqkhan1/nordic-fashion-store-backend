<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CloudinaryController extends Controller
{
    protected $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    /**
     * Get storage usage information
     */
    public function getStorageUsage()
    {
        try {
            $usage = $this->cloudinaryService->getStorageUsage();
            
            if (!$usage) {
                return response()->json([
                    'message' => 'Failed to retrieve storage usage'
                ], 500);
            }

            return response()->json($usage);
        } catch (\Exception $e) {
            Log::error('Failed to get Cloudinary storage usage', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to retrieve storage usage'
            ], 500);
        }
    }

    /**
     * Perform storage cleanup
     */
    public function cleanupStorage(Request $request)
    {
        $request->validate([
            'days' => 'sometimes|integer|min:1|max:365'
        ]);

        try {
            $days = $request->input('days', 30);
            $result = $this->cloudinaryService->cleanupOldImages($days);

            return response()->json([
                'message' => 'Cleanup completed successfully',
                'result' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to cleanup Cloudinary storage', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to cleanup storage'
            ], 500);
        }
    }
}
