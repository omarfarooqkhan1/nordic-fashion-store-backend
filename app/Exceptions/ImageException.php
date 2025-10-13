<?php

namespace App\Exceptions;

use Exception;

class ImageException extends Exception
{
    /**
     * Create a new ImageException for invalid image
     *
     * @param string $message
     * @return self
     */
    public static function invalidImage(string $message): self
    {
        return new self($message);
    }

    /**
     * Create a new ImageException for upload failure
     *
     * @param string $message
     * @return self
     */
    public static function uploadFailed(string $message): self
    {
        return new self("Image upload failed: {$message}");
    }

    /**
     * Create a new ImageException for delete failure
     *
     * @param string $message
     * @return self
     */
    public static function deleteFailed(string $message): self
    {
        return new self("Image deletion failed: {$message}");
    }
}