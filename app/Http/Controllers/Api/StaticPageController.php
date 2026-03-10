<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StaticPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StaticPageController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => StaticPage::all()
        ]);
    }

    public function show($slug)
    {
        $page = StaticPage::where('slug', $slug)->first();
        
        if (!$page) {
            return response()->json([
                'message' => 'Page not found'
            ], 404);
        }

        return response()->json([
            'data' => $page
        ]);
    }

    public function update(Request $request, StaticPage $staticPage)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string',
            'content' => 'sometimes|required|string',
            'images' => 'sometimes|array',
            'images.*' => 'array',
        ]);

        $staticPage->update($validated);

        return response()->json([
            'data' => $staticPage
        ]);
    }

    public function uploadImage(Request $request, StaticPage $staticPage)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max
            'position' => 'sometimes|string|in:top,middle,bottom,left,right,center,full-width',
            'caption' => 'sometimes|string|max:255',
            'alt_text' => 'sometimes|string|max:255',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('static-pages', 'public');
            $url = Storage::url($path);

            // Add image to images array with metadata
            $images = $staticPage->images ?? [];
            $images[] = [
                'id' => uniqid(),
                'url' => $url,
                'path' => $path,
                'position' => $request->input('position', 'center'), // Default position
                'caption' => $request->input('caption', ''),
                'alt_text' => $request->input('alt_text', ''),
                'width' => $request->input('width', '100%'),
                'height' => $request->input('height', 'auto'),
                'uploaded_at' => now(),
            ];

            $staticPage->update(['images' => $images]);

            return response()->json([
                'message' => 'Image uploaded successfully',
                'image' => $images[count($images) - 1]
            ]);
        }

        return response()->json([
            'message' => 'No image provided'
        ], 400);
    }

    public function deleteImage(Request $request, StaticPage $staticPage)
    {
        $request->validate([
            'image_path' => 'required|string',
        ]);

        $imagePath = $request->input('image_path');
        
        // Remove from storage
        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }

        // Remove from images array
        $images = $staticPage->images ?? [];
        $images = array_filter($images, function ($img) use ($imagePath) {
            return $img['path'] !== $imagePath;
        });

        $staticPage->update(['images' => array_values($images)]);

        return response()->json([
            'message' => 'Image deleted successfully'
        ]);
    }

    public function updateImagePosition(Request $request, StaticPage $staticPage)
    {
        $request->validate([
            'image_id' => 'required|string',
            'position' => 'required|string|in:top,middle,bottom,left,right,center,full-width',
            'caption' => 'sometimes|string|max:255',
            'alt_text' => 'sometimes|string|max:255',
            'width' => 'sometimes|string',
            'height' => 'sometimes|string',
        ]);

        $imageId = $request->input('image_id');
        $images = $staticPage->images ?? [];

        // Find and update the image
        foreach ($images as &$img) {
            if ($img['id'] === $imageId) {
                $img['position'] = $request->input('position');
                $img['caption'] = $request->input('caption', $img['caption'] ?? '');
                $img['alt_text'] = $request->input('alt_text', $img['alt_text'] ?? '');
                $img['width'] = $request->input('width', $img['width'] ?? '100%');
                $img['height'] = $request->input('height', $img['height'] ?? 'auto');
                break;
            }
        }

        $staticPage->update(['images' => $images]);

        return response()->json([
            'message' => 'Image position updated successfully',
            'images' => $images
        ]);
    }

    public function reorderImages(Request $request, StaticPage $staticPage)
    {
        $request->validate([
            'image_ids' => 'required|array',
            'image_ids.*' => 'string',
        ]);

        $imageIds = $request->input('image_ids');
        $images = $staticPage->images ?? [];
        $imageMap = [];

        // Create a map of images by ID
        foreach ($images as $img) {
            $imageMap[$img['id']] = $img;
        }

        // Reorder based on provided IDs
        $reorderedImages = [];
        foreach ($imageIds as $id) {
            if (isset($imageMap[$id])) {
                $reorderedImages[] = $imageMap[$id];
            }
        }

        $staticPage->update(['images' => $reorderedImages]);

        return response()->json([
            'message' => 'Images reordered successfully',
            'images' => $reorderedImages
        ]);
    }
}
