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
            $fileSizeMB = round($fileSizeBytes / 1024 / 1024, 2);
            
            Log::info('Uploading image to Cloudinary', [
                'original_size_mb' => $fileSizeMB,
                'file_path' => basename($filePath),
                'compression_settings' => $options
            ]);

            $result = $this->cloudinary->uploadApi()->upload($filePath, $options);

            // Log compression results
            $uploadedSizeBytes = $result['bytes'] ?? 0;
            $uploadedSizeMB = round($uploadedSizeBytes / 1024 / 1024, 2);
            $compressionRatio = $fileSizeBytes > 0 ? round((1 - $uploadedSizeBytes / $fileSizeBytes) * 100, 1) : 0;

            Log::info('Image uploaded to Cloudinary', [
                'public_id' => $result['public_id'],
                'secure_url' => $result['secure_url'],
                'original_size_mb' => $fileSizeMB,
                'compressed_size_mb' => $uploadedSizeMB,
                'compression_ratio' => $compressionRatio . '%',
                'format' => $result['format'] ?? 'unknown'
            ]);

            return $result->getArrayCopy();
        } catch (Exception $e) {
            Log::error('Failed to upload image to Cloudinary', [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);

            return null;
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
        } catch (Exception $e) {
            Log::error('Failed to delete image from Cloudinary', [
                'public_id' => $publicId,
                'error' => $e->getMessage()
            ]);

            return false;
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
        } catch (Exception $e) {
            Log::error('Failed to generate transformation URL', [
                'public_id' => $publicId,
                'error' => $e->getMessage()
            ]);

            return '';
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
            ];

            Log::info('Cloudinary storage usage', $storageInfo);
            
            return $storageInfo;
        } catch (Exception $e) {
            Log::error('Failed to get storage usage', [
                'error' => $e->getMessage()
            ]);

            return null;
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
                    $results['failed']++;
                    Log::warning('Failed to delete old image', [
                        'public_id' => $resource['public_id'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $results['freed_mb'] = round($results['freed_bytes'] / 1024 / 1024, 2);

            Log::info('Cleanup completed', $results);
            
            return $results;
        } catch (Exception $e) {
            Log::error('Failed to cleanup old images', [
                'error' => $e->getMessage()
            ]);

            return [
                'deleted' => 0,
                'failed' => 0,
                'freed_bytes' => 0,
                'freed_mb' => 0,
                'error' => $e->getMessage()
            ];
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
}
