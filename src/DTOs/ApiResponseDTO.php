<?php

namespace App\DTOs;

class ApiResponseDTO
{
    private int $value;
    private string $category;

    public function __construct(int $value, string $category)
    {
        $this->value = $value;
        $this->category = $category;
    }

    public function getValue(): int { return $this->value; }
    public function getCategory(): string { return $this->category; }
}