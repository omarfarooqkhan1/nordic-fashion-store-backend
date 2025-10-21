<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
// Removed Intervention Image dependency - using native GD functions

class LocalImageService
{
    private $disk;
    private $basePath;
    private $publicUrl;

    public function __construct()
    {
        $this->disk = 'public'; // Use public disk for web accessibility
        $this->basePath = 'images';
        $this->publicUrl = config('app.url') . '/storage';
    }

    /**
     * Upload and process an image file locally
     *
     * @param string|UploadedFile $file Path to file or UploadedFile instance
     * @param string $folder Folder to organize images (e.g., 'products', 'reviews', 'blogs')
     * @param string $filename Optional custom filename
     * @param array $options Processing options
     * @return array|null Returns upload result or null on failure
     */
    public function uploadImage($file, ?string $folder = null, ?string $filename = null, array $options = []): ?array
    {
        try {
            // Handle both file paths and UploadedFile instances
            if ($file instanceof UploadedFile) {
                $filePath = $file->getRealPath();
                $originalName = $file->getClientOriginalName();
                $mimeType = $file->getMimeType();
            } else {
                $filePath = $file;
                $originalName = basename($filePath);
                $mimeType = mime_content_type($filePath);
            }

            // Validate file type
            if (!$this->isValidImageType($mimeType)) {
                throw new Exception("Invalid image type: {$mimeType}");
            }

            // Generate filename if not provided
            if (!$filename) {
                $extension = $this->getFileExtension($mimeType);
                $filename = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '_' . time() . '_' . uniqid() . '.' . $extension;
            }

            // Create directory structure
            // For blogs, store directly in blogs folder without images prefix
            if ($folder) {
                $fullPath = $this->basePath . '/' . $folder . '/' . $filename;
                $publicUrl = '/storage/' . $fullPath;
            } else {
                // Store all images directly under /images for simplicity
                $fullPath = $this->basePath . '/' . $filename;
                $publicUrl = $this->publicUrl . '/' . $fullPath;
            }

            // Get original file size
            $originalSize = filesize($filePath);
            $originalSizeMB = round($originalSize / 1024 / 1024, 2);

            // Process and optimize image
            $processedImage = $this->processImage($filePath, $options);
            
            // Store the processed image
            $stored = Storage::disk($this->disk)->put($fullPath, $processedImage);
            
            if (!$stored) {
                throw new Exception("Failed to store image");
            }

            // Get stored file size
            $storedSize = Storage::disk($this->disk)->size($fullPath);
            $storedSizeMB = round($storedSize / 1024 / 1024, 2);
            $compressionRatio = $originalSize > 0 ? round((1 - $storedSize / $originalSize) * 100, 1) : 0;

            // Log upload results
            Log::info('Image uploaded locally', [
                'filename' => $filename,
                'folder' => $folder,
                'original_size_mb' => $originalSizeMB,
                'stored_size_mb' => $storedSizeMB,
                'compression_ratio' => $compressionRatio . '%',
                'public_url' => $publicUrl
            ]);

            return [
                'public_id' => $fullPath, // Use full path as ID for consistency
                'secure_url' => $publicUrl,
                'url' => $publicUrl,
                'filename' => $filename,
                'folder' => $folder,
                'bytes' => $storedSize,
                'format' => $this->getFileExtension($mimeType),
                'original_size' => $originalSize,
                'compressed_size' => $storedSize,
                'compression_ratio' => $compressionRatio
            ];

        } catch (Exception $e) {
            Log::error('Failed to upload image locally', [
                'file' => $file instanceof UploadedFile ? $file->getClientOriginalName() : $file,
                'folder' => $folder,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Process and optimize image using native GD functions
     */
    private function processImage(string $filePath, array $options = []): string
    {
        $defaultOptions = [
            'max_width' => 1200,
            'max_height' => 1200,
            'quality' => 85,
            'format' => 'jpg', // Default to JPEG for better compression
            'resize_mode' => 'limit' // Only resize if larger than max dimensions
        ];

        $options = array_merge($defaultOptions, $options);

        // Get image info
        $imageInfo = getimagesize($filePath);
        if (!$imageInfo) {
            throw new Exception("Invalid image file");
        }

        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        // Create image resource based on type
        $sourceImage = $this->createImageResource($filePath, $mimeType);
        if (!$sourceImage) {
            throw new Exception("Failed to create image resource");
        }

        // Auto-rotate based on EXIF data
        $sourceImage = $this->autoRotateImage($sourceImage, $filePath);

        // Calculate new dimensions
        $newDimensions = $this->calculateNewDimensions(
            $originalWidth, 
            $originalHeight, 
            $options['max_width'], 
            $options['max_height'], 
            $options['resize_mode']
        );

        // Resize image if needed
        if ($newDimensions['width'] !== $originalWidth || $newDimensions['height'] !== $originalHeight) {
            $resizedImage = imagecreatetruecolor($newDimensions['width'], $newDimensions['height']);
            
            // Preserve transparency for PNG and GIF
            if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
                $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
                imagefill($resizedImage, 0, 0, $transparent);
            }

            imagecopyresampled(
                $resizedImage, $sourceImage,
                0, 0, 0, 0,
                $newDimensions['width'], $newDimensions['height'],
                $originalWidth, $originalHeight
            );

            imagedestroy($sourceImage);
            $sourceImage = $resizedImage;
        }

        // Output image to string
        $output = $this->outputImageToString($sourceImage, $options['format'], $options['quality']);
        
        // Clean up
        imagedestroy($sourceImage);

        return $output;
    }

    /**
     * Create image resource from file
     */
    private function createImageResource(string $filePath, string $mimeType)
    {
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                return imagecreatefromjpeg($filePath);
            case 'image/png':
                return imagecreatefrompng($filePath);
            case 'image/gif':
                return imagecreatefromgif($filePath);
            case 'image/webp':
                return imagecreatefromwebp($filePath);
            default:
                return false;
        }
    }

    /**
     * Auto-rotate image based on EXIF data
     */
    private function autoRotateImage($image, string $filePath)
    {
        if (!function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($filePath);
        if (!$exif || !isset($exif['Orientation'])) {
            return $image;
        }

        $orientation = $exif['Orientation'];
        $width = imagesx($image);
        $height = imagesy($image);

        switch ($orientation) {
            case 3:
                $image = imagerotate($image, 180, 0);
                break;
            case 6:
                $image = imagerotate($image, -90, 0);
                break;
            case 8:
                $image = imagerotate($image, 90, 0);
                break;
        }

        return $image;
    }

    /**
     * Calculate new dimensions for resizing
     */
    private function calculateNewDimensions(int $originalWidth, int $originalHeight, int $maxWidth, int $maxHeight, string $resizeMode): array
    {
        if ($resizeMode === 'limit') {
            // Only resize if larger than max dimensions
            if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
                return ['width' => $originalWidth, 'height' => $originalHeight];
            }
        }

        // Calculate aspect ratio
        $aspectRatio = $originalWidth / $originalHeight;
        $maxAspectRatio = $maxWidth / $maxHeight;

        if ($aspectRatio > $maxAspectRatio) {
            // Image is wider than target aspect ratio
            $newWidth = $maxWidth;
            $newHeight = round($maxWidth / $aspectRatio);
        } else {
            // Image is taller than target aspect ratio
            $newHeight = $maxHeight;
            $newWidth = round($maxHeight * $aspectRatio);
        }

        return ['width' => $newWidth, 'height' => $newHeight];
    }

    /**
     * Output image to string
     */
    private function outputImageToString($image, string $format, int $quality): string
    {
        ob_start();
        
        switch ($format) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($image, null, $quality);
                break;
            case 'png':
                imagepng($image, null, round((100 - $quality) / 10)); // PNG quality is 0-9
                break;
            case 'gif':
                imagegif($image);
                break;
            case 'webp':
                imagewebp($image, null, $quality);
                break;
            default:
                imagejpeg($image, null, $quality);
        }
        
        $output = ob_get_contents();
        ob_end_clean();
        
        return $output;
    }

    /**
     * Delete an image from local storage
     */
    public function deleteImage(string $publicId): bool
    {
        try {
            // Remove the base path prefix if present
            $filePath = str_replace($this->basePath . '/', '', $publicId);
            $fullPath = $this->basePath . '/' . $filePath;

            if (Storage::disk($this->disk)->exists($fullPath)) {
                $deleted = Storage::disk($this->disk)->delete($fullPath);
                
                Log::info('Image deleted locally', [
                    'file_path' => $fullPath,
                    'success' => $deleted
                ]);

                return $deleted;
            }

            Log::warning('Image not found for deletion', [
                'file_path' => $fullPath
            ]);

            return true; // Consider it successful if file doesn't exist

        } catch (Exception $e) {
            Log::error('Failed to delete image locally', [
                'public_id' => $publicId,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Generate optimized URL for image delivery
     */
    public function getOptimizedUrl(string $publicId, array $transformations = []): string
    {
        try {
            // For local storage, we can implement on-the-fly resizing
            // For now, return the direct URL
            if (strpos($publicId, 'http') === 0) {
                return $publicId; // Already a full URL
            }

            return $this->publicUrl . '/' . $publicId;

        } catch (Exception $e) {
            Log::error('Failed to generate optimized URL', [
                'public_id' => $publicId,
                'error' => $e->getMessage()
            ]);

            return '';
        }
    }

    /**
     * Get storage usage information
     */
    public function getStorageUsage(): array
    {
        try {
            $totalSize = 0;
            $fileCount = 0;

            // Calculate total size of images directory
            $files = Storage::disk($this->disk)->allFiles($this->basePath);
            
            foreach ($files as $file) {
                $totalSize += Storage::disk($this->disk)->size($file);
                $fileCount++;
            }

            $totalSizeMB = round($totalSize / 1024 / 1024, 2);
            $totalSizeGB = round($totalSizeMB / 1024, 2);

            return [
                'total_files' => $fileCount,
                'total_size_bytes' => $totalSize,
                'total_size_mb' => $totalSizeMB,
                'total_size_gb' => $totalSizeGB,
                'storage_type' => 'local'
            ];

        } catch (Exception $e) {
            Log::error('Failed to get storage usage', [
                'error' => $e->getMessage()
            ]);

            return [
                'total_files' => 0,
                'total_size_bytes' => 0,
                'total_size_mb' => 0,
                'total_size_gb' => 0,
                'storage_type' => 'local',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Clean up old images (older than specified days)
     */
    public function cleanupOldImages(int $daysOld = 30): array
    {
        try {
            $cutoffDate = now()->subDays($daysOld);
            $deletedFiles = [];
            $deletedSize = 0;

            $files = Storage::disk($this->disk)->allFiles($this->basePath);
            
            foreach ($files as $file) {
                $lastModified = Storage::disk($this->disk)->lastModified($file);
                $fileDate = \Carbon\Carbon::createFromTimestamp($lastModified);

                if ($fileDate->lt($cutoffDate)) {
                    $fileSize = Storage::disk($this->disk)->size($file);
                    if (Storage::disk($this->disk)->delete($file)) {
                        $deletedFiles[] = $file;
                        $deletedSize += $fileSize;
                    }
                }
            }

            $deletedSizeMB = round($deletedSize / 1024 / 1024, 2);

            Log::info('Cleanup completed', [
                'deleted_files' => count($deletedFiles),
                'deleted_size_mb' => $deletedSizeMB,
                'days_old' => $daysOld
            ]);

            return [
                'deleted_files' => count($deletedFiles),
                'deleted_size_bytes' => $deletedSize,
                'deleted_size_mb' => $deletedSizeMB,
                'files' => $deletedFiles
            ];

        } catch (Exception $e) {
            Log::error('Failed to cleanup old images', [
                'error' => $e->getMessage()
            ]);

            return [
                'deleted_files' => 0,
                'deleted_size_bytes' => 0,
                'deleted_size_mb' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate image file type
     */
    private function isValidImageType(string $mimeType): bool
    {
        $allowedTypes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp'
        ];

        return in_array($mimeType, $allowedTypes);
    }

    /**
     * Get file extension from MIME type
     */
    private function getFileExtension(string $mimeType): string
    {
        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];

        return $mimeToExt[$mimeType] ?? 'jpg';
    }

    /**
     * Create multiple sizes of an image (thumbnails, etc.)
     */
    public function createImageSizes(string $publicId, array $sizes = []): array
    {
        try {
            $defaultSizes = [
                'thumbnail' => ['width' => 300, 'height' => 300],
                'medium' => ['width' => 600, 'height' => 600],
                'large' => ['width' => 1200, 'height' => 1200]
            ];

            $sizes = array_merge($defaultSizes, $sizes);
            $createdSizes = [];

            $filePath = storage_path('app/public/' . $publicId);
            
            if (!file_exists($filePath)) {
                throw new Exception("Source image not found: {$filePath}");
            }

            // Get original image info
            $imageInfo = getimagesize($filePath);
            if (!$imageInfo) {
                throw new Exception("Invalid image file");
            }

            $mimeType = $imageInfo['mime'];
            $originalImage = $this->createImageResource($filePath, $mimeType);
            
            if (!$originalImage) {
                throw new Exception("Failed to create image resource");
            }

            foreach ($sizes as $sizeName => $dimensions) {
                // Calculate new dimensions maintaining aspect ratio
                $newDimensions = $this->calculateNewDimensions(
                    $imageInfo[0], 
                    $imageInfo[1], 
                    $dimensions['width'], 
                    $dimensions['height'], 
                    'limit'
                );

                // Create resized image
                $resizedImage = imagecreatetruecolor($newDimensions['width'], $newDimensions['height']);
                
                // Preserve transparency for PNG and GIF
                if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
                    imagealphablending($resizedImage, false);
                    imagesavealpha($resizedImage, true);
                    $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
                    imagefill($resizedImage, 0, 0, $transparent);
                }

                imagecopyresampled(
                    $resizedImage, $originalImage,
                    0, 0, 0, 0,
                    $newDimensions['width'], $newDimensions['height'],
                    $imageInfo[0], $imageInfo[1]
                );

                // Generate filename and path
                $sizeFilename = pathinfo($publicId, PATHINFO_FILENAME) . "_{$sizeName}." . pathinfo($publicId, PATHINFO_EXTENSION);
                $sizePath = dirname($publicId) . '/' . $sizeFilename;

                // Output image to string
                $imageData = $this->outputImageToString($resizedImage, 'jpg', 85);
                
                // Store the image
                $stored = Storage::disk($this->disk)->put($sizePath, $imageData);
                
                // Clean up
                imagedestroy($resizedImage);
                
                if ($stored) {
                    $createdSizes[$sizeName] = [
                        'url' => $this->publicUrl . '/' . $sizePath,
                        'path' => $sizePath,
                        'width' => $newDimensions['width'],
                        'height' => $newDimensions['height']
                    ];
                }
            }

            // Clean up original image
            imagedestroy($originalImage);

            return $createdSizes;

        } catch (Exception $e) {
            Log::error('Failed to create image sizes', [
                'public_id' => $publicId,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }
}
