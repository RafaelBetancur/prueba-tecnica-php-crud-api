<?php

namespace App\Models;

class ApiResult
{
    private ?int $id;
    private int $value;
    private string $category;
    private int $attemptNumber;
    private bool $isImproved;
    private string $createdAt;
    private string $updatedAt;

    // Getters y setters
    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }
    
    public function getValue(): int { return $this->value; }
    public function setValue(int $value): void { $this->value = $value; }
    
    public function getCategory(): string { return $this->category; }
    public function setCategory(string $category): void { $this->category = $category; }
    
    public function getAttemptNumber(): int { return $this->attemptNumber; }
    public function setAttemptNumber(int $attemptNumber): void { $this->attemptNumber = $attemptNumber; }
    
    public function isImproved(): bool { return $this->isImproved; }
    public function setIsImproved(bool $isImproved): void { $this->isImproved = $isImproved; }
    
    public function getCreatedAt(): string { return $this->createdAt; }
    public function setCreatedAt(string $createdAt): void { $this->createdAt = $createdAt; }
    
    public function getUpdatedAt(): string { return $this->updatedAt; }
    public function setUpdatedAt(string $updatedAt): void { $this->updatedAt = $updatedAt; }
}