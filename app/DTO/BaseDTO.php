<?php

namespace App\DTO;

abstract class BaseDTO
{
    /**
     * Create DTO from array
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): self
    {
        $dto = new static();
        
        foreach ($data as $key => $value) {
            if (property_exists($dto, $key)) {
                $dto->$key = $value;
            }
        }
        
        return $dto;
    }

    /**
     * Convert DTO to array
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = [];
        
        foreach (get_object_vars($this) as $key => $value) {
            $data[$key] = $value;
        }
        
        return $data;
    }

    /**
     * Get only fillable attributes
     *
     * @param array $fillable
     * @return array
     */
    public function toFillableArray(array $fillable): array
    {
        $data = $this->toArray();
        return array_intersect_key($data, array_flip($fillable));
    }
}