<?php

namespace App\Services;

use App\DTOs\ApiResponseDTO;
use App\Exceptions\ApiException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ApiService
{
    private Client $client;
    private string $baseUrl;
    private string $userId;

    public function __construct(Client $client, string $baseUrl, string $userId)
    {
        $this->client = $client;
        $this->baseUrl = $baseUrl;
        $this->userId = $userId;
    }

    /**
     * @throws ApiException
     */
    public function fetchData(): ApiResponseDTO
    {
        try {
            $response = $this->client->get($this->baseUrl, [
                'query' => ['user_id' => $this->userId]
            ]);
            
            $data = json_decode($response->getBody()->getContents(), true);
            
            if (json_last_error() !== JSON_ERROR_NONE || !isset($data['value'], $data['category'])) {
                throw new ApiException('Invalid API response format');
            }
            
            return new ApiResponseDTO($data['value'], $data['category']);
            
        } catch (GuzzleException $e) {
            throw new ApiException('API request failed: ' . $e->getMessage());
        }
    }
}