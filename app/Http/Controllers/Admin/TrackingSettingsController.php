<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrackingSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrackingSettingsController extends Controller
{
    public function index()
    {
        $tracking = TrackingSetting::query()->first();

        $trackingSummary = [
            'pixel_enabled'  => (bool)($tracking->pixel_enabled ?? false),
            'capi_enabled'   => (bool)($tracking->capi_enabled ?? false),
            'pixel_id'       => $tracking->pixel_id ?? null,
            'default_currency' => $tracking->default_currency ?? 'UAH',
            'events' => [
                'page_view'        => (bool)($tracking->send_page_view ?? false),
                'view_content'     => (bool)($tracking->send_view_content ?? false),
                'add_to_cart'      => (bool)($tracking->send_add_to_cart ?? false),
                'initiate_checkout'=> (bool)($tracking->send_initiate_checkout ?? false),
                'purchase'         => (bool)($tracking->send_purchase ?? false),
                'lead'             => (bool)($tracking->send_lead ?? false),
            ],
        ];

        return view('admin.settings_pixel.index', compact('tracking', 'trackingSummary'));
    }

    public function edit()
    {
        $settings = TrackingSetting::query()->first() ?? new TrackingSetting([
            'pixel_enabled'         => false,
            'exclude_admin'         => true,
            'send_page_view'        => true,
            'send_view_content'     => true,
            'send_add_to_cart'      => true,
            'send_initiate_checkout'=> true,
            'send_purchase'         => true,
            'send_lead'             => false,
            'require_consent'       => false,
            'capi_enabled'          => false,
            'capi_api_version'      => 'v20.0',
            'default_currency'      => 'UAH',
        ]);

        return view('admin.settings_pixel.tracking', compact('settings'));
    }

    public function update(Request $request)
    {
        // Список чекбоксів, які треба привести до boolean
        $bools = [
            'pixel_enabled','exclude_admin','require_consent',
            'send_page_view','send_view_content','send_add_to_cart',
            'send_initiate_checkout','send_purchase','send_lead',
            'capi_enabled',
        ];

        // Перетворюємо "on"/null → true/false перед валідацією
        foreach ($bools as $key) {
            $request->merge([$key => $request->boolean($key)]);
        }

        // Валідейшн
        $validated = $request->validate([
            // Meta Pixel
            'pixel_enabled'   => ['required', 'boolean'],
            'pixel_id'        => ['nullable', 'string', 'max:50',
                                  Rule::requiredIf(fn() => $request->boolean('pixel_enabled'))],
            'exclude_admin'   => ['required', 'boolean'],
            'require_consent' => ['required', 'boolean'],

            // Події
            'send_page_view'         => ['required', 'boolean'],
            'send_view_content'      => ['required', 'boolean'],
            'send_add_to_cart'       => ['required', 'boolean'],
            'send_initiate_checkout' => ['required', 'boolean'],
            'send_purchase'          => ['required', 'boolean'],
            'send_lead'              => ['required', 'boolean'],

            // CAPI
            'capi_enabled'    => ['required', 'boolean'],
            'capi_token'      => [Rule::requiredIf(fn() => $request->boolean('capi_enabled')),
                                  'nullable', 'string', 'max:255'],
            'capi_test_code'  => ['nullable', 'string', 'max:100'],
            'capi_api_version'=> ['nullable', 'string', 'max:20'],

            // Інше
            'default_currency'=> ['nullable', 'string', 'size:3'],
        ]);

        // Санітизація
        if (isset($validated['pixel_id'])) {
            $validated['pixel_id'] = trim($validated['pixel_id']);
        }
        if (!empty($validated['default_currency'])) {
            $validated['default_currency'] = strtoupper($validated['default_currency']);
        }
        if (!empty($validated['capi_api_version'])) {
            $validated['capi_api_version'] = strtolower($validated['capi_api_version']);
        }

        // Зберігаємо
        $settings = TrackingSetting::query()->first() ?? new TrackingSetting();
        $settings->fill($validated)->save();

        return back()->with('success', 'Налаштування трекінгу успішно збережені.');
    }
}
