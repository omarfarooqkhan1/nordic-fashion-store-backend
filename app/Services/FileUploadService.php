<?php

namespace App\Services;

use App\Services\Storage\StorageInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class FileUploadService
{
    protected $storage;

    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Upload a file
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param string|null $filename
     * @return array|null
     */
    public function uploadFile(UploadedFile $file, string $folder, ?string $filename = null): ?array
    {
        try {
            $result = $this->storage->upload($file, $folder, $filename);

            if ($result) {
                Log::info('File uploaded successfully', [
                    'folder' => $folder,
                    'filename' => $result['filename'],
                    'url' => $result['url']
                ]);

                return $result;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to upload file', [
                'folder' => $folder,
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Upload multiple files
     *
     * @param array $files
     * @param string $folder
     * @return array
     */
    public function uploadFiles(array $files, string $folder): array
    {
        $uploadedFiles = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $result = $this->uploadFile($file, $folder);
                if ($result) {
                    $uploadedFiles[] = $result;
                }
            }
        }

        return $uploadedFiles;
    }

    /**
     * Delete a file
     *
     * @param string $path
     * @return bool
     */
    public function deleteFile(string $path): bool
    {
        try {
            $result = $this->storage->delete($path);

            if ($result) {
                Log::info('File deleted successfully', [
                    'path' => $path
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to delete file', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Get file URL
     *
     * @param string $path
     * @return string
     */
    public function getFileUrl(string $path): string
    {
        return $this->storage->getUrl($path);
    }

    /**
     * Check if file exists
     *
     * @param string $path
     * @return bool
     */
    public function fileExists(string $path): bool
    {
        return $this->storage->exists($path);
    }
}