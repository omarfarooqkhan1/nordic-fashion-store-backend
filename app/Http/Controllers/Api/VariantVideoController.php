<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VariantVideoController extends Controller
{
    /**
     * Upload a video for all variants of a given color for a product.
     */
    public function upload(Request $request, Product $product)
    {
        try {
            // Log the incoming request
            \Log::info('Video upload request received', [
                'product_id' => $product->id,
                'color' => $request->input('color'),
                'has_video' => $request->hasFile('video'),
                'video_size' => $request->hasFile('video') ? $request->file('video')->getSize() : null,
                'video_mime' => $request->hasFile('video') ? $request->file('video')->getMimeType() : null,
            ]);
            
            $request->validate([
                'color' => 'required|string',
                'video' => 'required|file|mimes:mp4,mov,webm|max:51200', // 50MB in KB
            ]);

            $color = $request->input('color');
            $file = $request->file('video');
            
            \Log::info('Video upload started', [
                'product_id' => $product->id,
                'color' => $color,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType()
            ]);
            
            // Ensure the directory exists
            $storageDir = storage_path('app/public/videos');
            if (!file_exists($storageDir)) {
                mkdir($storageDir, 0755, true);
            }
            
            $filename = uniqid('variant_video_') . '.' . $file->getClientOriginalExtension();
            // Store directly to 'videos' folder within the public disk
            $path = $file->storeAs('videos', $filename, 'public');
            $relativePath = '/storage/videos/' . $filename;
            
            \Log::info('Video stored', [
                'path' => $path,
                'relative_path' => $relativePath
            ]);

            // Update all variants of this color for the product
            $variants = $product->variants()->where('color', $color)->get();
            
            if ($variants->isEmpty()) {
                \Log::warning('No variants found for color', [
                    'product_id' => $product->id,
                    'color' => $color
                ]);
                
                return response()->json([
                    'message' => 'No variants found with the specified color.',
                    'video_path' => $relativePath,
                    'video_url' => $relativePath,
                ], 404);
            }
            
            foreach ($variants as $variant) {
                $variant->video_url = $relativePath;
                $variant->save();
            }
            
            \Log::info('Video attached to variants', [
                'variant_count' => $variants->count(),
                'variant_ids' => $variants->pluck('id')
            ]);
            
            return response()->json([
                'message' => 'Video uploaded and attached to all variants of this color.',
                'video_path' => $relativePath,
                'video_url' => $relativePath,
                'variant_ids' => $variants->pluck('id'),
                'variant_count' => $variants->count(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Video upload validation failed', [
                'errors' => $e->errors(),
                'max_upload' => ini_get('upload_max_filesize'),
                'max_post' => ini_get('post_max_size'),
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'php_limits' => [
                    'upload_max_filesize' => ini_get('upload_max_filesize'),
                    'post_max_size' => ini_get('post_max_size'),
                ]
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Video upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Video upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
