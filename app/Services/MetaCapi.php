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

        // ===== ЛОГ ДО ВІДПРАВКИ =====
        $raw = (bool) env('CAPI_LOG_RAW', false); // true = показуємо все без маски
        $logPayload = $payload;

        if (!$raw) {
            // маскуємо токен
            $logPayload['access_token'] = '***hidden***';
            // маскуємо ключі user_data
            if (isset($logPayload['data']) && is_array($logPayload['data'])) {
                $mask = static function (string $v): string {
                    $len = mb_strlen($v);
                    if ($len <= 12) return '***masked***';
                    return mb_substr($v, 0, 6) . '…' . mb_substr($v, -4);
                };
                foreach ($logPayload['data'] as &$ev) {
                    if (isset($ev['user_data']) && is_array($ev['user_data'])) {
                        foreach (['fbc','fbp','em','ph','fn','ln','external_id','ct','st','zp','country'] as $k) {
                            if (!empty($ev['user_data'][$k]) && is_string($ev['user_data'][$k])) {
                                $ev['user_data'][$k] = $mask($ev['user_data'][$k]);
                            }
                        }
                    }
                }
                unset($ev);
            }
        }

        Log::info('CAPI_HTTP_POST', [
            'endpoint' => $endpoint,
            'payload'  => $logPayload, // ← такої форми піде POST (з маскуванням при raw=false)
        ]);

        // ===== ВІДПРАВКА =====
        $resp = Http::timeout(8)
            ->connectTimeout(4)
            ->retry(2, 250)
            ->asJson() // гарантуємо JSON
            ->post($endpoint, $payload);

        // ===== ЛОГ ВІДПОВІДІ =====
        $rawBody = $resp->body();
        $bodyJson = null;
        try { $bodyJson = $resp->json(); } catch (\Throwable $e) { /* ignore */ }

        Log::info('CAPI_HTTP_RESPONSE', [
            'status' => $resp->status(),
            'body'   => $bodyJson ?? mb_strimwidth($rawBody, 0, 4000, '…'),
        ]);
        Log::info(str_repeat('─', 100)); // роздільник у логах

        return $resp;
    }
}
