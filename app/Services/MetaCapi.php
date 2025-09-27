<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaCapi
{
    protected string $pixelId;
    protected string $accessToken;
    protected string $apiVersion;

    public function __construct(string $pixelId, string $accessToken, string $apiVersion = 'v20.0')
    {
        $this->pixelId     = $pixelId;
        $this->accessToken = $accessToken;
        $this->apiVersion  = $apiVersion;
    }

    public function send(array $events, ?string $testCode = null)
    {
        $endpoint = "https://graph.facebook.com/{$this->apiVersion}/{$this->pixelId}/events";

        $payload = [
            'data'         => $events,
            'access_token' => $this->accessToken,
        ];
        if ($testCode) {
            $payload['test_event_code'] = $testCode;
        }

        // ---- ЛОГ: що відправляємо (токен маскуємо) ----
        $logPayload = $payload;
        $logPayload['access_token'] = '***hidden***';
        Log::info('CAPI_HTTP_POST', [
            'endpoint' => $endpoint,
            'payload'  => $logPayload,
        ]);

        // ---- ВІДПРАВКА ----
        $resp = Http::timeout(8)
            ->connectTimeout(4)
            ->retry(2, 250)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($endpoint, $payload);

        // ---- ЛОГ: що відповів Graph API ----
        $body = null;
        try {
            $body = $resp->json();
        } catch (\Throwable $e) {
            $body = $resp->body();
        }

        Log::info('CAPI_HTTP_RESPONSE', [
            'status' => $resp->status(),
            'body'   => $body,
        ]);

        // ---- Роздільник ----
        Log::info(str_repeat('─', 120)); // або можна Log::info(''); для пустого рядка

        return $resp;
    }
}
