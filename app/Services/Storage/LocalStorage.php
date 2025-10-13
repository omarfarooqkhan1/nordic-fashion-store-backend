<?php

namespace App\Services\Storage;

use App\Services\Storage\StorageInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class LocalStorage implements StorageInterface
{
    private $disk;
    private $basePath;
    private $publicUrl;

    public function __construct()
    {
        $this->disk = 'public';
        $this->basePath = 'images';
        $this->publicUrl = config('app.url') . '/storage';
    }

    /**
     * Upload a file
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param string|null $filename
     * @return array|null
     */
    public function upload(UploadedFile $file, string $folder, ?string $filename = null): ?array
    {
        try {
            // Generate filename if not provided
            if (!$filename) {
                $extension = $file->getClientOriginalExtension();
                $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '_' . time() . '_' . uniqid() . '.' . $extension;
            }

            // Create directory structure
            $directory = $this->basePath;
            $fullPath = $directory . '/' . $filename;

            // Store the file
            $path = $file->storeAs($directory, $filename, $this->disk);

            if (!$path) {
                return null;
            }

            // Generate public URL
            $publicUrl = '/' . $fullPath;

            return [
                'path' => $fullPath,
                'url' => $publicUrl,
                'filename' => $filename,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to upload file to local storage', [
                'file' => $file->getClientOriginalName(),
                'folder' => $folder,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Delete a file
     *
     * @param string $path
     * @return bool
     */
    public function delete(string $path): bool
    {
        try {
            if (Storage::disk($this->disk)->exists($path)) {
                return Storage::disk($this->disk)->delete($path);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete file from local storage', [
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
    public function getUrl(string $path): string
    {
        return $this->publicUrl . '/' . $path;
    }

    /**
     * Check if file exists
     *
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }
}