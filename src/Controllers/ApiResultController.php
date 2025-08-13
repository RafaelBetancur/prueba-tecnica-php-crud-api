<?php

namespace App\Controllers;

use App\Models\ApiResult;
use App\Repositories\ApiResultRepository;
use App\Services\ImprovementService;
use PDO;

class ApiResultController
{
    private ImprovementService $improvementService;
    private ApiResultRepository $repository;

    public function __construct(PDO $pdo)
    {
        $this->repository = new ApiResultRepository($pdo);
        $apiService = new \App\Services\ApiService(
            new \GuzzleHttp\Client(),
            $_ENV['API_BASE_URL'],
            $_ENV['USER_ID']
        );
        $this->improvementService = new ImprovementService($apiService, $this->repository);
    }

    public function initializeData(): array
    {
        return $this->improvementService->initializeData(100);
    }

    public function improveAllBadResults(): array
    {
        return $this->improvementService->improveAllBadResults();
    }

    public function getAllResults(): array
    {
        return $this->repository->findAll();
    }

    public function getResult(int $id): ?array
    {
        $result = $this->repository->find($id);
        return $result ? $this->formatResult($result) : null;
    }

    public function createResult(array $data): array
    {
        $apiResult = new ApiResult();
        $apiResult->setValue($data['value']);
        $apiResult->setCategory($data['category']);
        $apiResult->setAttemptNumber($data['attempt_number'] ?? 1);
        $apiResult->setIsImproved($data['is_improved'] ?? false);
        
        $this->repository->save($apiResult);
        
        return $this->formatResult($apiResult);
    }

    public function deleteAllResults(): array
{
    $this->repository->deleteAll();
    return [
        'success' => true,
        'message' => 'Todos los registros fueron eliminados correctamente',
        'deleted_count' => $this->repository->getTotalCount()
    ];
}

    public function updateResult(int $id, array $data): ?array
    {
        $apiResult = $this->repository->find($id);
        
        if (!$apiResult) {
            return null;
        }
        
        $apiResult->setValue($data['value'] ?? $apiResult->getValue());
        $apiResult->setCategory($data['category'] ?? $apiResult->getCategory());
        $apiResult->setAttemptNumber($data['attempt_number'] ?? $apiResult->getAttemptNumber());
        $apiResult->setIsImproved($data['is_improved'] ?? $apiResult->isImproved());
        
        $this->repository->update($apiResult);
        
        return $this->formatResult($apiResult);
    }

    public function deleteResult(int $id): bool
    {
        $this->repository->delete($id);
        return true;
    }

    public function getExecutionLogs(): array
    {
        return $this->repository->getExecutionLogs();
    }

    private function formatResult(ApiResult $apiResult): array
    {
        return [
            'id' => $apiResult->getId(),
            'value' => $apiResult->getValue(),
            'category' => $apiResult->getCategory(),
            'attempt_number' => $apiResult->getAttemptNumber(),
            'is_improved' => $apiResult->isImproved(),
            'created_at' => $apiResult->getCreatedAt(),
            'updated_at' => $apiResult->getUpdatedAt()
        ];
    }
}