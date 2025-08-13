<?php

namespace App\Services;

use App\DTOs\ApiResponseDTO;
use App\Models\ApiResult;
use App\Repositories\ApiResultRepository;
use App\Services\ApiService;

class ImprovementService
{
    private ApiService $apiService;
    private ApiResultRepository $repository;

    public function __construct(ApiService $apiService, ApiResultRepository $repository)
    {
        $this->apiService = $apiService;
        $this->repository = $repository;
    }

    public function initializeData(int $count = 100): array
    {
        $totalCalls = 0;
        
        for ($i = 0; $i < $count; $i++) {
            try {
                $response = $this->apiService->fetchData();
                $totalCalls++;
                
                $apiResult = new ApiResult();
                $apiResult->setValue($response->getValue());
                $apiResult->setCategory($response->getCategory());
                $apiResult->setAttemptNumber(1);
                $apiResult->setIsImproved(false);
                
                $this->repository->save($apiResult);
                
            } catch (\Exception $e) {
                // Log error and continue
                error_log("Error fetching initial data: " . $e->getMessage());
            }
        }
        
        $counts = $this->repository->countByCategory();
        $categoryCounts = array_column($counts, 'count', 'category');
        
        $this->repository->logExecution(
            $count,
            0,
            $totalCalls,
            $categoryCounts
        );
        
        return [
            'total_initial_calls' => $count,
            'total_calls' => $totalCalls,
            'category_counts' => $categoryCounts
        ];
    }

    public function performImprovementSweep(): array
    {
        $badResults = $this->repository->findBadResults();
        $improvedCount = 0;
        $totalCalls = 0;
        
        foreach ($badResults as $badResult) {
            try {
                $response = $this->apiService->fetchData();
                $totalCalls++;
                
                if ($response->getCategory() !== 'bad') {
                    $apiResult = new ApiResult();
                    $apiResult->setId($badResult['id']);
                    $apiResult->setValue($response->getValue());
                    $apiResult->setCategory($response->getCategory());
                    $apiResult->setAttemptNumber($badResult['attempt_number'] + 1);
                    $apiResult->setIsImproved(true);
                    
                    $this->repository->update($apiResult);
                    $improvedCount++;
                }
            } catch (\Exception $e) {
                // Log error and continue
                error_log("Error during improvement sweep: " . $e->getMessage());
            }
        }
        
        $counts = $this->repository->countByCategory();
        $categoryCounts = array_column($counts, 'count', 'category');
        
        return [
            'total_bad_found' => count($badResults),
            'total_improved' => $improvedCount,
            'total_calls' => $totalCalls,
            'category_counts' => $categoryCounts
        ];
    }

    public function improveAllBadResults(): array
    {
        $sweeps = 0;
        $totalCalls = 0;
        $initialCounts = $this->repository->countByCategory();
        $initialCategoryCounts = array_column($initialCounts, 'count', 'category');
        
        do {
            $result = $this->performImprovementSweep();
            $sweeps++;
            $totalCalls += $result['total_calls'];
            
            $currentBadCount = $result['category_counts']['bad'] ?? 0;
            
        } while ($currentBadCount > 0);
        
        $finalCounts = $this->repository->countByCategory();
        $finalCategoryCounts = array_column($finalCounts, 'count', 'category');
        
        $this->repository->logExecution(
            $initialCategoryCounts['bad'] ?? 0,
            $sweeps,
            $totalCalls,
            $finalCategoryCounts
        );
        
        return [
            'total_sweeps' => $sweeps,
            'total_calls' => $totalCalls,
            'initial_counts' => $initialCategoryCounts,
            'final_counts' => $finalCategoryCounts
        ];
    }
}