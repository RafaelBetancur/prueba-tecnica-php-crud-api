<?php

namespace App\Repositories;

use App\Models\ApiResult;
use PDO;

class ApiResultRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(ApiResult $apiResult): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO api_results (value, category, attempt_number, is_improved)
            VALUES (:value, :category, :attempt_number, :is_improved)
        ");
        
        $stmt->execute([
            ':value' => $apiResult->getValue(),
            ':category' => $apiResult->getCategory(),
            ':attempt_number' => $apiResult->getAttemptNumber(),
            ':is_improved' => (int)$apiResult->isImproved()
        ]);
        
        $apiResult->setId($this->pdo->lastInsertId());
    }

    public function deleteAll(): void
    {
        // Elimina todos los registros manteniendo la estructura de las tablas
        $this->pdo->exec("DELETE FROM api_results");
        $this->pdo->exec("DELETE FROM execution_logs");
    }
    
    public function getTotalCount(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM api_results");
        return (int)$stmt->fetchColumn();
    }

    public function update(ApiResult $apiResult): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE api_results 
            SET value = :value, 
                category = :category, 
                attempt_number = :attempt_number, 
                is_improved = :is_improved 
            WHERE id = :id
        ");
        
        $stmt->execute([
            ':id' => $apiResult->getId(),
            ':value' => $apiResult->getValue(),
            ':category' => $apiResult->getCategory(),
            ':attempt_number' => $apiResult->getAttemptNumber(),
            ':is_improved' => (int)$apiResult->isImproved()
        ]);
    }

    public function find(int $id): ?ApiResult
    {
        $stmt = $this->pdo->prepare("SELECT * FROM api_results WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) {
            return null;
        }
        
        $apiResult = new ApiResult();
        $apiResult->setId($data['id']);
        $apiResult->setValue($data['value']);
        $apiResult->setCategory($data['category']);
        $apiResult->setAttemptNumber($data['attempt_number']);
        $apiResult->setIsImproved((bool)$data['is_improved']);
        $apiResult->setCreatedAt($data['created_at']);
        $apiResult->setUpdatedAt($data['updated_at']);
        
        return $apiResult;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM api_results ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM api_results WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public function findBadResults(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM api_results WHERE category = 'bad' ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countByCategory(): array
    {
        $stmt = $this->pdo->query("
            SELECT 
                category, 
                COUNT(*) as count 
            FROM api_results 
            GROUP BY category
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function logExecution(int $initialCalls, int $sweeps, int $totalCalls, array $counts): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO execution_logs 
            (total_initial_calls, total_sweeps, total_calls, bad_count, medium_count, good_count)
            VALUES (:initial_calls, :sweeps, :total_calls, :bad, :medium, :good)
        ");
        
        $stmt->execute([
            ':initial_calls' => $initialCalls,
            ':sweeps' => $sweeps,
            ':total_calls' => $totalCalls,
            ':bad' => $counts['bad'] ?? 0,
            ':medium' => $counts['medium'] ?? 0,
            ':good' => $counts['good'] ?? 0
        ]);
    }

    public function getExecutionLogs(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM execution_logs ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}