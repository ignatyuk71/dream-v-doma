<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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

        return Http::withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($endpoint, $payload);
    }
}
