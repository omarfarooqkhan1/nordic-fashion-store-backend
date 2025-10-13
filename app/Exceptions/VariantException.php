<?php

namespace App\Exceptions;

use Exception;

class VariantException extends Exception
{
    /**
     * Create a new VariantException for duplicate variant
     *
     * @param string $size
     * @param string $color
     * @return self
     */
    public static function duplicateVariant(string $size, string $color): self
    {
        return new self("A variant with size {$size} and color {$color} already exists for this product.");
    }

    /**
     * Create a new VariantException for invalid variant
     *
     * @param string $message
     * @return self
     */
    public static function invalidVariant(string $message): self
    {
        return new self($message);
    }
}