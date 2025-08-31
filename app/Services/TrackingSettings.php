<?php

namespace App\Services;

use App\Models\TrackingSetting;
use Illuminate\Support\Facades\Cache;

class TrackingSettings
{
    const CACHE_KEY = 'tracking.settings.v1';

    public static function get(): TrackingSetting
    {
        return Cache::remember(self::CACHE_KEY, 300, function () {
            return TrackingSetting::query()->first() ?? new TrackingSetting([
                'default_currency' => 'UAH',
            ]);
        });
    }

    public static function refresh(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
