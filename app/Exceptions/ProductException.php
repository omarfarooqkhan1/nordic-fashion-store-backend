<?php

namespace App\Exceptions;

use Exception;

class ProductException extends Exception
{
    /**
     * Create a new ProductException for duplicate product
     *
     * @param string $name
     * @return self
     */
    public static function duplicateProduct(string $name): self
    {
        return new self("A product with name {$name} already exists.");
    }

    /**
     * Create a new ProductException for invalid product
     *
     * @param string $message
     * @return self
     */
    public static function invalidProduct(string $message): self
    {
        return new self($message);
    }
}