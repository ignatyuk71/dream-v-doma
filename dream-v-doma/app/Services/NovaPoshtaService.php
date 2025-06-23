<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NovaPoshtaService
{
    protected string $apiKey;
    protected string $endpoint = 'https://api.novaposhta.ua/v2.0/json/';

    public function __construct()
    {
        $this->apiKey = config('services.nova_poshta.api_key');
    }

    /**
     * 🔍 Пошук населених пунктів
     */
    public function searchCities(string $query): array
    {
        try {
            $response = Http::post($this->endpoint, [
                'apiKey' => $this->apiKey,
                'modelName' => 'Address',
                'calledMethod' => 'searchSettlements',
                'methodProperties' => [
                    'CityName' => $query,
                    'Limit' => 10,
                ],
            ]);

            if ($response->successful()) {
                return $response->json('data.0.Addresses') ?? [];
            }

            Log::error('NovaPoshta searchCities error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('NovaPoshta searchCities exception: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * 🏤 Отримання відділень або поштоматів (без фільтрації)
     */
    public function getWarehouses(string $cityRef): array
    {
        try {
            $response = Http::post($this->endpoint, [
                'apiKey' => $this->apiKey,
                'modelName' => 'Address',
                'calledMethod' => 'getWarehouses',
                'methodProperties' => ['CityRef' => $cityRef],
            ]);

            if ($response->successful()) {
                return $response->json('data') ?? [];
            }

            Log::error('NovaPoshta getWarehouses error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('NovaPoshta getWarehouses exception: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * 📦 Отримання типів складів (відділення, поштомати тощо)
     */
    public function getWarehouseTypes(): array
    {
        try {
            $response = Http::post($this->endpoint, [
                'apiKey' => $this->apiKey,
                'modelName' => 'Address',
                'calledMethod' => 'getWarehouseTypes',
                'methodProperties' => new \stdClass(),
            ]);

            if ($response->successful()) {
                return $response->json('data') ?? [];
            }

            Log::error('NovaPoshta getWarehouseTypes error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('NovaPoshta getWarehouseTypes exception: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * 📦 Отримання одного відділення по Ref
     */
    public function getWarehouseByRef(string $ref): ?array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->endpoint, [
                'apiKey' => $this->apiKey,
                'modelName' => 'Address',
                'calledMethod' => 'getWarehouses',
                'methodProperties' => [
                    'Ref' => $ref,
                ],
            ]);

            if ($response->successful() && !empty($response['data'][0])) {
                return [
                    'name' => $response['data'][0]['Description'],
                    'address' => $response['data'][0]['ShortAddress'],
                ];
            }

            Log::error('NovaPoshta getWarehouseByRef error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('NovaPoshta getWarehouseByRef exception: ' . $e->getMessage());
        }

        return null;
    }
}
