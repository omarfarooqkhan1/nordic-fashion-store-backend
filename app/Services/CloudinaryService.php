<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Exception;
use Illuminate\Support\Facades\Log;

class CloudinaryService
{

    private $cloudinary;

    public function __construct()
    {
        $config = [
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud_name'),
                'api_key' => config('cloudinary.api_key'),
                'api_secret' => config('cloudinary.api_secret'),
            ],
            'url' => [
                'secure' => true
            ]
        ];

        Configuration::instance($config);
        $this->cloudinary = new Cloudinary($config);
    }

    /**
     * Upload an image file to Cloudinary with optimized settings for free tier
     *
     * @param string $filePath Path to the image file
     * @param string $publicId Optional public ID for the image
     * @param string $folder Optional folder to organize images
     * @param array $compressionOptions Override default compression settings
     * @return array|null Returns the upload result or null on failure
     */
    public function uploadImage(string $filePath, string $publicId = null, string $folder = 'nordic-skin-products', array $compressionOptions = []): ?array
    {
        try {
            // Default compression settings optimized for storage efficiency
            $defaultOptions = [
                'folder' => $folder,
                'resource_type' => 'image',
                'quality' => 'auto:good', // Better compression while maintaining good quality
                'fetch_format' => 'auto', // Auto-select best format (WebP when supported)
                'flags' => 'lossy', // Enable lossy compression for better size reduction
                'transformation' => [
                    [
                        'width' => 1200, // Max width to control file size
                        'height' => 1200, // Max height to control file size
                        'crop' => 'limit', // Only resize if larger than specified dimensions
                        'quality' => 'auto:good',
                        'format' => 'auto'
                    ]
                ]
            ];

            // Merge with any custom compression options
            $options = array_merge($defaultOptions, $compressionOptions);

            if ($publicId) {
                $options['public_id'] = $publicId;
            }

            // Check file size before upload
            $fileSizeBytes = filesize($filePath);
            $fileSizeMB = round($fileSizeBytes / 1024 / 1024, 2);$result = $this->cloudinary->uploadApi()->upload($filePath, $options);

            // Log compression results
            $uploadedSizeBytes = $result['bytes'] ?? 0;
            $uploadedSizeMB = round($uploadedSizeBytes / 1024 / 1024, 2);
            $compressionRatio = $fileSizeBytes > 0 ? round((1 - $uploadedSizeBytes / $fileSizeBytes) * 100, 1) : 0;return $result->getArrayCopy();
        } catch (Exception $e) {return null;
        }
    }

    /**
     * Delete an image from Cloudinary
     *
     * @param string $publicId The public ID of the image to delete
     * @return bool
     */
    public function deleteImage(string $publicId): bool
    {
        try {
            $result = $this->cloudinary->uploadApi()->destroy($publicId);
            return $result['result'] === 'ok';
        } catch (Exception $e) {return false;
        }
    }

    /**
     * Generate a transformation URL for an image with storage-optimized settings
     *
     * @param string $publicId The public ID of the image
     * @param array $transformations Array of transformations to apply
     * @return string
     */
    public function getTransformedUrl(string $publicId, array $transformations = []): string
    {
        try {
            $cloudName = config('cloudinary.cloud_name');
            $baseUrl = "https://res.cloudinary.com/{$cloudName}/image/upload/";
            
            // Default optimized transformations for web delivery
            $defaultTransformations = 'q_auto:good,f_auto,w_800,h_800,c_limit';
            
            $transformationString = empty($transformations) 
                ? $defaultTransformations . '/' 
                : implode(',', $transformations) . '/';
                
            return $baseUrl . $transformationString . $publicId;
        } catch (Exception $e) {return '';
        }
    }

    /**
     * Get storage usage information (free tier monitoring)
     *
     * @return array|null
     */
    public function getStorageUsage(): ?array
    {
        try {
            $usage = $this->cloudinary->adminApi()->usage();
            
            $storageInfo = [
                'used_bytes' => $usage['storage']['usage'] ?? 0,
                'used_mb' => round(($usage['storage']['usage'] ?? 0) / 1024 / 1024, 2),
                'limit_bytes' => 1073741824, // 1GB in bytes for free tier
                'limit_mb' => 1024,
                'percentage_used' => round((($usage['storage']['usage'] ?? 0) / 1073741824) * 100, 2),
                'transformations_used' => $usage['transformations']['usage'] ?? 0,
                'transformations_limit' => 25000, // Free tier limit
                'bandwidth_used_bytes' => $usage['bandwidth']['usage'] ?? 0,
                'bandwidth_used_mb' => round(($usage['bandwidth']['usage'] ?? 0) / 1024 / 1024, 2)
            ];return $storageInfo;
        } catch (Exception $e) {return null;
        }
    }

    /**
     * Clean up old unused images to free storage space
     *
     * @param int $daysOld Delete images older than this many days
     * @return array
     */
    public function cleanupOldImages(int $daysOld = 30): array
    {
        try {
            $results = [
                'deleted' => 0,
                'failed' => 0,
                'freed_bytes' => 0
            ];

            // Get images older than specified days
            $cutoffDate = date('Y-m-d', strtotime("-{$daysOld} days"));
            
            $resources = $this->cloudinary->searchApi()
                ->expression("folder:nordic-skin-products AND created_at<{$cutoffDate}")
                ->maxResults(100)
                ->execute();

            foreach ($resources['resources'] as $resource) {
                try {
                    $publicId = $resource['public_id'];
                    
                    // Check if image is still referenced in database
                    $isReferenced = \App\Models\Image::where('url', 'LIKE', "%{$publicId}%")->exists();
                    
                    if (!$isReferenced) {
                        $result = $this->cloudinary->uploadApi()->destroy($publicId);
                        if ($result['result'] === 'ok') {
                            $results['deleted']++;
                            $results['freed_bytes'] += $resource['bytes'] ?? 0;
                        } else {
                            $results['failed']++;
                        }
                    }
                } catch (Exception $e) {
                    $results['failed']++;}
            }

            $results['freed_mb'] = round($results['freed_bytes'] / 1024 / 1024, 2);return $results;
        } catch (Exception $e) {return [
                'deleted' => 0,
                'failed' => 0,
                'freed_bytes' => 0,
                'freed_mb' => 0,
                'error' => $e->getMessage()
            ];
        }
    }


    /**
     * Upload a video file to Cloudinary with optimized settings for free tier
     *
     * @param string $filePath Path to the video file
     * @param string $publicId Optional public ID for the video
     * @param string $folder Optional folder to organize videos
     * @param array $options Override default upload settings
     * @return array|null Returns the upload result or null on failure
     */
    public function uploadVideo(string $filePath, string $publicId = null, string $folder = 'nordic-skin-products', array $options = []): ?array
    {
        try {
            // Default options for video upload
            $defaultOptions = [
                'folder' => $folder,
                'resource_type' => 'video',
                'quality' => 'auto',
                'fetch_format' => 'auto',
                'transformation' => [
                    [
                        'width' => 1280, // Max width for videos
                        'height' => 1280, // Max height for videos
                        'crop' => 'limit',
                        'quality' => 'auto',
                        'format' => 'auto'
                    ]
                ]
            ];

            $uploadOptions = array_merge($defaultOptions, $options);
            if ($publicId) {
                $uploadOptions['public_id'] = $publicId;
            }

            // Check file size before upload
            $fileSizeBytes = filesize($filePath);
            $fileSizeMB = round($fileSizeBytes / 1024 / 1024, 2);$result = $this->cloudinary->uploadApi()->upload($filePath, $uploadOptions);

            // Log upload results
            $uploadedSizeBytes = $result['bytes'] ?? 0;
            $uploadedSizeMB = round($uploadedSizeBytes / 1024 / 1024, 2);
            $compressionRatio = $fileSizeBytes > 0 ? round((1 - $uploadedSizeBytes / $fileSizeBytes) * 100, 1) : 0;return $result->getArrayCopy();
        } catch (Exception $e) {return null;
        }
    }

    /**
     * Upload image with high quality settings (for premium products)
     *
     * @param string $filePath
     * @param string $publicId
     * @param string $folder
     * @return array|null
     */
    public function uploadHighQualityImage(string $filePath, string $publicId = null, string $folder = 'nordic-skin-products'): ?array
    {
        $highQualityOptions = [
            'quality' => 'auto:best', // Higher quality
            'flags' => 'preserve_transparency', // Preserve PNG transparency
            'transformation' => [
                [
                    'width' => 1600, // Higher resolution for premium products
                    'height' => 1600,
                    'crop' => 'limit',
                    'quality' => 'auto:best',
                    'format' => 'auto'
                ]
            ]
        ];

        return $this->uploadImage($filePath, $publicId, $folder, $highQualityOptions);
    }

    /**
     * Delete custom jacket images from Cloudinary
     *
     * @param string|null $frontImageUrl
     * @param string|null $backImageUrl
     * @return array
     */
    public function deleteCustomJacketImages(?string $frontImageUrl, ?string $backImageUrl): array
    {
        $results = [
            'deleted' => 0,
            'failed' => 0,
            'errors' => []
        ];

        try {
            // Extract public IDs from URLs
            $frontPublicId = $this->extractPublicIdFromUrl($frontImageUrl);
            $backPublicId = $this->extractPublicIdFromUrl($backImageUrl);

            // Delete front image if exists
            if ($frontPublicId) {
                try {
                    $result = $this->cloudinary->uploadApi()->destroy($frontPublicId);
                    if ($result['result'] === 'ok') {
                        $results['deleted']++;} else {
                        $results['failed']++;
                        $results['errors'][] = "Failed to delete front image: {$frontPublicId}";
                    }
                } catch (Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "Error deleting front image {$frontPublicId}: " . $e->getMessage();
                }
            }

            // Delete back image if exists
            if ($backPublicId) {
                try {
                    $result = $this->cloudinary->uploadApi()->destroy($backPublicId);
                    if ($result['result'] === 'ok') {
                        $results['deleted']++;} else {
                        $results['failed']++;
                        $results['errors'][] = "Failed to delete back image: {$backPublicId}";
                    }
                } catch (Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "Error deleting back image {$backPublicId}: " . $e->getMessage();
                }
            }} catch (Exception $e) {$results['errors'][] = 'General cleanup error: ' . $e->getMessage();
        }

        return $results;
    }

    /**
     * Extract public ID from Cloudinary URL
     *
     * @param string|null $url
     * @return string|null
     */
    private function extractPublicIdFromUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        try {
            // Parse Cloudinary URL to extract public ID
            // Example URL: https://res.cloudinary.com/cloud_name/image/upload/v1234567890/folder/image-name.png
            $parts = parse_url($url);
            if (!$parts || !isset($parts['path'])) {
                return null;
            }

            $pathParts = explode('/', trim($parts['path'], '/'));
            
            // Find the upload part and get everything after it
            $uploadIndex = array_search('upload', $pathParts);
            if ($uploadIndex === false || $uploadIndex >= count($pathParts) - 1) {
                return null;
            }

            // Get the public ID (everything after 'upload' excluding version)
            $publicIdParts = array_slice($pathParts, $uploadIndex + 1);
            
            // Remove version if present (starts with 'v')
            if (!empty($publicIdParts) && preg_match('/^v\d+$/', $publicIdParts[0])) {
                array_shift($publicIdParts);
            }

            $publicId = implode('/', $publicIdParts);
            
            // Remove file extension
            $publicId = preg_replace('/\.[^.]*$/', '', $publicId);
            
            return $publicId;
        } catch (Exception $e) {return null;
        }
    }
}
