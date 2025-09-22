<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\MetaCapi;

/**
 * Контролер серверних подій для Meta CAPI.
 */
class TrackController extends Controller
{
    /** 
     * Кеш налаштувань у межах одного HTTP-запиту 
     * (мінус зайві звернення до БД) 
     * @var object|null 
     */
    private $settingsCache = null;

    /* ===================== PUBLIC ENDPOINTS ===================== */

    /** PageView */
    public function pv(Request $request)
    {
        // ✦ Простий тестовий лог
        Log::info('Тестовий лог працює!', [
            'time' => now()->toDateTimeString(),
            'ip'   => $request->ip(),
        ]);

        return $this->handleEvent('PageView', $request, function () {
            return []; // PV без custom_data
        }, 'send_page_view'); // <- прибрано іменовані аргументи
    }

    /** ViewContent */
    public function vc(Request $request)
    {
        return $this->handleEvent('ViewContent', $request, function () use ($request) {
            $contents = $this->contentsFromRequest($request);

            if (!empty($contents)) {
                $value = $this->calcValue($contents);

                return [
                    'content_type' => 'product',
                    'content_ids'  => array_map(fn($c) => (string)$c['id'], $contents),
                    'contents'     => $contents,
                    'value'        => $value,
                    'currency'     => $request->input('currency', $this->currency()),
                    'content_name' => $request->input('name') ?: $request->input('content_name'),
                ];
            }

            $pid      = (string)($request->input('id') ?? $request->input('sku') ?? '');
            $price    = $this->num(
                $request->input('price', $request->input('item_price', $request->input('value', 0)))
            );
            $qty      = (int)$request->input('quantity', 1);
            $currency = $request->input('currency', $this->currency());

            $data = [
                'content_type' => 'product',
                'content_ids'  => $pid ? [$pid] : [],
                'value'        => $this->num($price * max(1, $qty)),
                'currency'     => $currency,
            ];

            if ($pid) {
                $data['contents'] = [[
                    'id'         => $pid,
                    'quantity'   => $qty,
                    'item_price' => $price,
                ]];
            }

            if ($request->filled('name')) {
                $data['content_name'] = $request->input('name');
            }

            return $data;
        }, 'send_view_content');
    }

    /** AddToCart */
    public function atc(Request $request)
    {
        return $this->handleEvent('AddToCart', $request, function () use ($request) {
            $contents = $this->contentsFromRequest($request);

            if (!empty($contents)) {
                $value = $request->filled('value')
                    ? $this->num($request->input('value'))
                    : $this->calcValue($contents);

                return [
                    'content_type' => 'product',
                    'content_ids'  => array_map(fn($c) => (string)$c['id'], $contents),
                    'contents'     => $contents,
                    'value'        => $value,
                    'currency'     => $request->input('currency', $this->currency()),
                ];
            }

            $pid      = (string)($request->input('id') ?? $request->input('sku') ?? '');
            $qty      = (int)$request->input('quantity', 1);
            $price    = $this->num($request->input('price', $request->input('item_price', 0)));
            $currency = $request->input('currency', $this->currency());
            $value    = $this->num($qty * $price);

            return [
                'content_type' => 'product',
                'content_ids'  => $pid ? [$pid] : [],
                'contents'     => $pid ? [[
                    'id'         => $pid,
                    'quantity'   => $qty,
                    'item_price' => $price,
                ]] : [],
                'value'        => $value,
                'currency'     => $currency,
            ];
        }, 'send_add_to_cart');
    }

    /** InitiateCheckout */
    public function ic(Request $request)
    {
        return $this->handleEvent('InitiateCheckout', $request, function () use ($request) {
            $contents = $this->contentsFromRequest($request);

            if (empty($contents)) {
                $items = (array)$request->input('items', []);
                foreach ($items as $i) {
                    $id = (string)($i['variant_sku'] ?? $i['sku'] ?? $i['id'] ?? '');
                    if ($id === '') continue;
                    $qty = (int)($i['quantity'] ?? 1);
                    $pr  = $this->num($i['price'] ?? $i['item_price'] ?? 0);
                    $contents[] = ['id' => $id, 'quantity' => $qty, 'item_price' => $pr];
                }
            }

            $value    = $request->filled('value')
                ? $this->num($request->input('value'))
                : $this->calcValue($contents);

            $currency = $request->input('currency', $this->currency());
            $numItems = array_reduce($contents, fn($s, $c) => $s + (int)$c['quantity'], 0);
            $ids      = array_map(fn($c) => (string)$c['id'], $contents);

            $data = [
                'content_type' => 'product',
                'content_ids'  => $ids,
                'contents'     => $contents,
                'num_items'    => $numItems,
                'value'        => $value,
                'currency'     => $currency,
            ];

            if ($request->filled('content_name') || $request->filled('name')) {
                $data['content_name'] = (string)($request->input('content_name') ?? $request->input('name'));
            }

            return $data;
        }, 'send_initiate_checkout');
    }

    /** Lead */
    public function lead(Request $request)
    {
        return $this->handleEvent('Lead', $request, function () use ($request) {
            return [
                'content_name' => $request->input('content_name', 'lead'),
                'status'       => $request->input('status', 'submitted'),
                'value'        => $this->num($request->input('value', 0)),
                'currency'     => $request->input('currency', $this->currency()),
            ];
        }, 'send_lead');
    }

    /** Purchase */
    public function purchase(Request $request)
    {
        return $this->handleEvent('Purchase', $request, function () use ($request) {
            $contents = $this->contentsFromRequest($request);

            if (empty($contents)) {
                $items = (array)$request->input('items', []);
                foreach ($items as $i) {
                    $id = (string)($i['variant_sku'] ?? $i['sku'] ?? $i['id'] ?? '');
                    if ($id === '') continue;
                    $qty = (int)($i['quantity'] ?? 1);
                    $pr  = $this->num($i['price'] ?? $i['item_price'] ?? 0);
                    $contents[] = ['id' => $id, 'quantity' => $qty, 'item_price' => $pr];
                }
            }

            $shipping = $this->num($request->input('shipping', 0));
            $tax      = $this->num($request->input('tax', 0));

            $calc  = $this->calcValue($contents) + $shipping + $tax;
            $value = $request->filled('value')
                ? $this->num($request->input('value'))
                : $this->num($calc);

            if ($value < 0) $value = 0.00;

            $currency   = strtoupper((string)$request->input('currency', $this->currency()));
            $numItems   = array_reduce($contents, fn($s, $c) => $s + (int)$c['quantity'], 0);
            $contentIds = array_map(fn($c) => (string)$c['id'], $contents);

            $data = [
                'content_type' => 'product',
                'content_ids'  => $contentIds,
                'contents'     => $contents,
                'num_items'    => $numItems,
                'value'        => $value,
                'currency'     => $currency,
            ];

            if ($shipping > 0) $data['shipping'] = $shipping;
            if ($tax > 0)      $data['tax']      = $tax;

            if ($request->filled('order_number')) {
                $data['order_number'] = (string)$request->input('order_number');
            }
            if ($request->filled('content_name') || $request->filled('name')) {
                $data['content_name'] = (string)($request->input('content_name') ?? $request->input('name'));
            }

            return $data;
        }, 'send_purchase');
    }


    /* ===================== CORE HANDLER ===================== */

    /**
     * Спільний обробник для всіх подій.
     * - читає налаштування/прапорці;
     * - будує user_data/custom_data;
     * - шле подію через MetaCapi;
     * - повертає JSON-відповідь з мінімальною діагностикою.
     *
     * @param string   $name            Назва події (PageView, ViewContent, ...)
     * @param Request  $req             HTTP-запит
     * @param \Closure $buildCustomData Колбек, що повертає custom_data (масив) або []
     * @param string   $flag            Назва прапорця у БД (send_view_content тощо)
     */
    private function handleEvent(string $name, Request $req, \Closure $buildCustomData, string $flag)
    {
        $s = $this->settings();

        // Глобальне вимкнення CAPI
        if (!$s || (int)($s->capi_enabled ?? 0) !== 1) {
            return response()->json(['ok' => true, 'skipped' => 'capi_disabled'], 202);
        }

        // Перевірка прапорця конкретної події
        if (!$this->flagEnabled($s, $flag)) {
            return response()->json(['ok' => true, 'skipped' => "flag_{$flag}_disabled"], 202);
        }

        // Відсікти адмінські урли (і коли реферер/URL вказує на адмінку)
        if ((int)($s->exclude_admin ?? 1) === 1) {
            $url = $this->eventSourceUrl($req);
            if ($this->looksLikeAdmin($url) || $req->is('admin*')) {
                return response()->json(['ok' => true, 'skipped' => 'admin_excluded'], 202);
            }
        }

        // Перевірка наявності Pixel ID і CAPI token
        $pixelId = (string)($s->pixel_id ?? '');
        $token   = (string)($s->capi_token ?? '');
        if ($pixelId === '' || $token === '') {
            return response()->json(['ok' => false, 'error' => 'missing_pixel_or_token'], 422);
        }

        // custom_data будуємо лише для подій, де він потрібен
        $custom = $buildCustomData();

        // Конструюємо подію Meta
        $event = [
            'event_name'       => $name,
            'event_time'       => (int)($req->input('event_time') ?: time()),
            'action_source'    => 'website',
            'event_source_url' => $this->eventSourceUrl($req),
            'event_id'         => (string)($req->input('event_id') ?: $this->makeEventId($name)),
            'user_data'        => $this->userData($req),
        ];
        if (!empty($custom)) {
            $event['custom_data'] = $custom;
        }

        // test_event_code: дозволяємо override з тіла запиту, інакше — з БД
        $testCode = $req->input('test_event_code', $s->capi_test_code ?? null);

        try {
            $capi = new MetaCapi($pixelId, $token, (string)($s->capi_api_version ?? 'v20.0'));
            $resp = $capi->send([$event], $testCode);
        } catch (\Throwable $e) {
            Log::warning('MetaCAPI exception', ['event' => $name, 'ex' => $e->getMessage()]);
            return response()->json([
                'ok'    => false,
                'error' => 'capi_exception',
                'msg'   => $e->getMessage(),
            ], 502);
        }

        $body = $resp->json();


        // Невдала HTTP-відповідь або помилка у тілі
        if (!$resp->ok() || (is_array($body) && isset($body['error']))) {
            return response()->json([
                'ok'     => false,
                'error'  => 'capi_request_failed',
                'status' => $resp->status(),
                'body'   => $body,
            ], 502);
        }

        // Події не прийняті (validation warning тощо)
        if (is_array($body) && array_key_exists('events_received', $body) && (int)$body['events_received'] < 1) {
            return response()->json([
                'ok'     => false,
                'error'  => 'events_not_received',
                'status' => $resp->status(),
                'body'   => $body,
            ], 502);
        }

        // Успіх
        return response()->json([
            'ok'              => true,
            'event'           => $name,
            'events_received' => is_array($body) ? ($body['events_received'] ?? null) : null,
            'fbtrace_id'      => is_array($body) ? ($body['fbtrace_id'] ?? null) : null,
        ], 200);
    }

    /* ===================== HELPERS ===================== */

    /**
     * Отримати налаштування трекінгу з БД (із кешем на рівні контролера).
     */
    private function settings(): ?object
    {
        if ($this->settingsCache !== null) {
            return $this->settingsCache;
        }
        return $this->settingsCache = DB::table('tracking_settings')->first();
    }

    /**
     * Валюта за замовчуванням із налаштувань, fallback — UAH.
     */
    private function currency(): string
    {
        $s = $this->settings();
        return $s && !empty($s->default_currency) ? (string)$s->default_currency : 'UAH';
    }

    /**
     * Перевірка, чи увімкнений певний прапорець події у БД.
     * Якщо поля немає — вважаємо, що подія дозволена (true).
     * Це покриває випадок з PageView, коли колонки send_page_view може не бути.
     */
    private function flagEnabled(object $s, string $flag): bool
    {
        if (!property_exists($s, $flag)) {
            return true;
        }
        return (int)($s->{$flag} ?? 0) === 1;
    }

    /**
     * Визначити URL джерела події:
     * 1) event_source_url з тіла, 2) url з тіла, 3) Referer, 4) поточний URL.
     */
    private function eventSourceUrl(Request $req): string
    {
        if ($req->filled('event_source_url')) return (string)$req->input('event_source_url');
        if ($req->filled('url'))              return (string)$req->input('url');

        $ref = (string)$req->headers->get('referer', '');
        return $ref !== '' ? $ref : url()->current();
    }

    /**
     * Просте визначення “адмінського” URL для відсікання подій.
     */
    private function looksLikeAdmin(string $url): bool
    {
        return str_contains($url, '/admin') || str_contains($url, '/dashboard');
    }

    /**
     * Нормалізація числових значень (ціни тощо):
     * - коми → крапки,
     * - прибрати все, крім цифр/крапки/мінуса,
     * - привести до float і округлити до 2-х знаків.
     */
    private function num($v): float
    {
        $s = str_replace(',', '.', (string)$v);
        $clean = preg_replace('/[^\d\.\-]/', '', $s);
        $n = (float)$clean;
        return round($n, 2);
    }

    /**
     * SHA-256 для PII (email, ім’я тощо) з нормалізацією до нижнього регістру і тримом.
     * Якщо на вхід приходить порожнє значення — повертає null.
     */
    private function sha256(?string $v): ?string
    {
        if (!$v) return null;
        $v = trim(mb_strtolower($v));
        return $v === '' ? null : hash('sha256', $v);
    }

    /**
     * Нормалізація телефону до цифр (E.164 без “+”, якщо вже міжнародний).
     * Якщо порожньо — повертає null.
     */
    private function normPhone(?string $p): ?string
    {
        if (!$p) return null;
        $digits = preg_replace('/\D+/', '', $p);
        return $digits === '' ? null : $digits;
    }

    /**
     * Витягнути fbclid з URL (щоб зібрати _fbc, якщо cookie немає).
     */
    private function parseFbclid(?string $url): ?string
    {
        if (!$url) return null;
        $parts = parse_url($url);
        if (empty($parts['query'])) return null;
        parse_str($parts['query'], $q);
        return $q['fbclid'] ?? null;
    }

    /**
     * Формує user_data для Meta CAPI.
     *
     * - IP / User-Agent завжди
     * - PII (email, phone, fn, ln) → SHA256 (нижній регістр + trim)
     * - _fbc та _fbp беремо тільки з cookies (ніяких input / генерації)
     * - external_id → SHA256 за наявності
     */
    private function userData(Request $req): array
    {
        $data = [
            'client_ip_address' => $req->ip(),
            'client_user_agent' => (string) $req->userAgent(),
        ];

        // PII (якщо передані)
        $email = $req->input('email');
        $phone = $req->input('phone');
        $fn    = $req->input('first_name') ?? $req->input('fn');
        $ln    = $req->input('last_name')  ?? $req->input('ln');

        if ($h = $this->sha256($email))                  $data['em'] = $h;
        if ($phone && ($norm = $this->normPhone($phone))) $data['ph'] = $this->sha256($norm);
        if ($h = $this->sha256($fn))                     $data['fn'] = $h;
        if ($h = $this->sha256($ln))                     $data['ln'] = $h;

        // Cookies з Meta Pixel (жодних трансформацій)
        if ($fbc = $req->cookie('_fbc')) $data['fbc'] = $fbc;
        if ($fbp = $req->cookie('_fbp')) $data['fbp'] = $fbp;

        // Опціонально external_id
        if ($req->filled('external_id')) {
            $data['external_id'] = $this->sha256((string) $req->input('external_id'));
        }

                // 🚨 тимчасово логнемо повні значення (не маскуємо)
                \Log::info('CAPI raw cookies', [
                    'fbc' => $fbc,
                    'fbp' => $fbp,
                    'fbc_len' => $fbc ? strlen($fbc) : null,
                    'fbp_len' => $fbp ? strlen($fbp) : null,
                ]);

        return $data;
    }
    

    /**
     * Згенерувати event_id, який сумісний із фронтом (для дедуплікації).
     * Формат: <Name>-<12 hex>-<unix time>.
     */
    private function makeEventId(string $name): string
    {
        return $name . '-' . bin2hex(random_bytes(6)) . '-' . time();
    }

    /**
     * Прочитати і нормалізувати contents[] з тіла запиту.
     * Якщо немає або формат не масив — повернути [].
     */
    private function contentsFromRequest(Request $req): array
    {
        $raw = $req->input('contents');
        if (!is_array($raw)) return [];

        $out = [];
        foreach ($raw as $c) {
            $id = (string)($c['id'] ?? $c['sku'] ?? '');
            if ($id === '') continue;
            $qty = (int)($c['quantity'] ?? 1);
            $pr  = $this->num($c['item_price'] ?? $c['price'] ?? 0);
            $out[] = ['id' => $id, 'quantity' => $qty, 'item_price' => $pr];
        }
        return $out;
    }

    /**
     * Порахувати загальну вартість по contents[].
     */
    private function calcValue(array $contents): float
    {
        $sum = 0.0;
        foreach ($contents as $c) {
            $sum += (int)$c['quantity'] * (float)$c['item_price'];
        }
        return $this->num($sum);
    }
}
