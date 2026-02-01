<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HeroImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class HeroImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $heroImages = HeroImage::active()->ordered()->get();
return response()->json($heroImages);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Check if we have a file upload
            if ($request->hasFile('image')) {
                $request->validate([
                    'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:20480', // 20MB
                    'alt_text' => 'nullable|string|max:255',
                    'sort_order' => 'nullable|integer|min:0',
                    'is_active' => 'nullable|boolean'
                ]);

                // Handle file upload
                $file = $request->file('image');

                // Use LocalImageService for upload
                $localImageService = app(\App\Services\LocalImageService::class);
                $result = $localImageService->uploadImage($file, 'hero-images');

                if ($result) {
                    $heroImage = HeroImage::create([
                        'image_url' => $result['secure_url'],
                        'alt_text' => $request->alt_text,
                        'sort_order' => $request->sort_order ?? 0,
                        'is_active' => $request->is_active ?? true,
                    ]);
                } else {
                    throw new \Exception('Failed to upload image to local storage');
                }
return response()->json([
                    'success' => true,
                    'data' => $heroImage,
                    'message' => 'Hero image created successfully'
                ], 201);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create hero image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(HeroImage $heroImage)
    {
        return response()->json($heroImage);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HeroImage $heroImage)
    {
        $validator = Validator::make($request->all(), [
            'image_url' => 'sometimes|required|string',
            'alt_text' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $heroImage->update($request->all());
return response()->json($heroImage);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HeroImage $heroImage)
    {
        $heroImage->delete();
return response()->json(['message' => 'Hero image deleted successfully']);
    }

    /**
     * Get all hero images for admin (including inactive)
     */
    public function adminIndex()
    {
        $heroImages = HeroImage::ordered()->get();
return response()->json($heroImages);
    }

    /**
     * Reorder hero images
     */
    public function reorder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array',
            'images.*.id' => 'required|integer|exists:hero_images,id',
            'images.*.sort_order' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->images as $imageData) {
            HeroImage::where('id', $imageData['id'])->update(['sort_order' => $imageData['sort_order']]);
        }

        return response()->json(['message' => 'Hero images reordered successfully']);
    }
}