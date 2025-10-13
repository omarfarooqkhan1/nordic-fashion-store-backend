<?php

namespace App\Services\Storage;

use Illuminate\Http\UploadedFile;

interface StorageInterface
{
    /**
     * Upload a file
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param string|null $filename
     * @return array|null
     */
    public function upload(UploadedFile $file, string $folder, ?string $filename = null): ?array;

    /**
     * Delete a file
     *
     * @param string $path
     * @return bool
     */
    public function delete(string $path): bool;

    /**
     * Get file URL
     *
     * @param string $path
     * @return string
     */
    public function getUrl(string $path): string;

    /**
     * Check if file exists
     *
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool;
}