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
        $request->validate([
            'color' => 'required|string',
            'video' => 'required|file|mimetypes:video/mp4,video/quicktime,video/webm|max:102400', // 100MB
        ]);

        $color = $request->input('color');
        $file = $request->file('video');
        $filename = uniqid('variant_video_') . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('public/variant_videos', $filename);
        $relativePath = str_replace('public/', 'storage/', $path);

        // Update all variants of this color for the product
        $variants = $product->variants()->where('color', $color)->get();
        foreach ($variants as $variant) {
            $variant->video_path = $relativePath;
            $variant->save();
        }
return response()->json([
            'message' => 'Video uploaded and attached to all variants of this color.',
            'video_path' => $relativePath,
            'variant_ids' => $variants->pluck('id'),
        ]);
    }
}
