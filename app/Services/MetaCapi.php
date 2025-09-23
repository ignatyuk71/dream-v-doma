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
            'data'         => $events,            // ← саме це летить у Meta
            'access_token' => $this->accessToken, // ← частина POST
        ];
        if ($testCode) {
            $payload['test_event_code'] = $testCode;
        }

        // ===== ЛОГ ДО ВІДПРАВКИ (точний payload) =====
        $raw = (bool) env('CAPI_LOG_RAW', false); // true = без масок

        $logPayload = $payload;

        // Маскуємо токен, якщо raw = false
        if (!$raw && isset($logPayload['access_token'])) {
            $logPayload['access_token'] = '***hidden***';
        }

        // Маскуємо fbc/fbp всередині подій, якщо raw = false
        if (!$raw && isset($logPayload['data']) && is_array($logPayload['data'])) {
            foreach ($logPayload['data'] as &$ev) {
                if (isset($ev['user_data']['fbc'])) {
                    $v = (string) $ev['user_data']['fbc'];
                    $ev['user_data']['fbc'] = mb_substr($v, 0, 6).'…'.mb_substr($v, -4);
                }
                if (isset($ev['user_data']['fbp'])) {
                    $v = (string) $ev['user_data']['fbp'];
                    $ev['user_data']['fbp'] = mb_substr($v, 0, 6).'…'.mb_substr($v, -4);
                }
            }
            unset($ev);
        }

        Log::info('CAPI_HTTP_POST', [
            'endpoint' => $endpoint,
            'payload'  => $logPayload, // ← 1-в-1 структура того, що піде у POST (з маскуванням при raw=false)
        ]);

        // ===== ВІДПРАВКА =====
        $resp = Http::timeout(8)
            ->connectTimeout(4)
            ->retry(2, 250)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($endpoint, $payload);

        // ===== ЛОГ ВІДПОВІДІ =====
        Log::info('CAPI_HTTP_RESPONSE', [
            'status' => $resp->status(),
            'body'   => $resp->json(),
        ]);
        Log::info(str_repeat('─', 100)); // візуальний роздільник

        return $resp;
    }
}
