<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\MetaCapi;

/**
 * Контролер серверних подій для Meta CAPI.
 * Кожен публічний метод — це ендпойнт події (PV/VC/ATC/IC/Lead/Purchase).
 * Додано гейт атрибуції: без _fbc/fbclid події не відправляються.
 */
class TrackController extends Controller
{
    /**
     * CAPI: PageView
     * Приймає: event_id (опц.), page_url (опц.)
     * Відправляє: PageView з user_data (IP/UA + fbc/fbp)
     */
    public function pv(Request $req)
    {
        $t = DB::table('tracking_settings')->first();
        if (!$t) return response()->json(['ok' => false, 'skip' => 'no_settings'], 200);

        if ((int)($t->capi_enabled ?? 0) !== 1 || empty($t->pixel_id) || empty($t->capi_token)) {
            return response()->json(['ok' => false, 'skip' => 'capi_disabled_or_missing_creds'], 200);
        }
        if ((int)($t->exclude_admin ?? 1) === 1 && $req->is('admin*')) {
            return response()->json(['ok' => false, 'skip' => 'admin_excluded'], 200);
        }
        if (!(bool)($t->send_page_view ?? true)) {
            return response()->json(['ok' => false, 'skip' => 'page_view_disabled'], 200);
        }

        $eventId        = (string)($req->input('event_id') ?: ('pv-'.bin2hex(random_bytes(4)).'-'.time()));
        $eventSourceUrl = $this->eventSourceUrl($req) ?? url()->current();

        // ⛔ Гейт атрибуції
        if (!$this->hasFbAttribution($req, $eventSourceUrl)) {
            return response()->json(['ok' => false, 'skip' => 'no_fb_attribution'], 200);
        }

        $userData = $this->collectUserData($req, $eventSourceUrl);

        $event = [
            'event_name'       => 'PageView',
            'event_time'       => time(),
            'action_source'    => 'website',
            'event_source_url' => $eventSourceUrl,
            'event_id'         => $eventId,
            'user_data'        => $userData,
        ];

        $meta = new MetaCapi($t->pixel_id, $t->capi_token, $t->capi_api_version ?: 'v20.0');
        $resp = $meta->send([$event], $t->capi_test_code ?: null);

        return response()->json([
            'ok'       => $resp->successful(),
            'status'   => $resp->status(),
            'event_id' => $eventId,
        ], 200);
    }

    /**
     * ViewContent — перегляд товару/контенту.
     * Очікує product{} або contents[]; value — сума цін * кількість.
     */
    public function vc(Request $req)
    {
        $t = DB::table('tracking_settings')->first();
        if (!$t) return response()->json(['ok' => false, 'skip' => 'no_settings'], 200);

        if ((int)($t->capi_enabled ?? 0) !== 1 || empty($t->pixel_id) || empty($t->capi_token)) {
            return response()->json(['ok' => false, 'skip' => 'capi_disabled_or_missing_creds'], 200);
        }

        if ((int)($t->exclude_admin ?? 1) === 1) {
            $path = (string) parse_url($req->fullUrl(), PHP_URL_PATH);
            if ($req->is('admin*') || str_contains($path, '/admin')) {
                return response()->json(['ok' => false, 'skip' => 'admin_excluded'], 200);
            }
        }

        if (!(bool)($t->send_view_content ?? true)) {
            return response()->json(['ok' => false, 'skip' => 'view_content_disabled'], 200);
        }

        $eventId        = (string)($req->input('event_id') ?: ('vc-'.bin2hex(random_bytes(4)).'-'.time()));
        $eventSourceUrl = $this->eventSourceUrl($req) ?? url()->current();

        // ⛔ Гейт атрибуції
        if (!$this->hasFbAttribution($req, $eventSourceUrl)) {
            return response()->json(['ok' => false, 'skip' => 'no_fb_attribution'], 200);
        }

        $userData = $this->collectUserData($req, $eventSourceUrl);

        // Дані товару
        $p   = (array)($req->input('product') ?? []);
        $sku = isset($p['sku']) ? (string)$p['sku'] : null;
        $id  = isset($p['id'])  ? (string)$p['id']  : null;
        $cid = $sku ?: $id;
        if (!$cid) return response()->json(['ok' => false, 'skip' => 'missing_content_id'], 200);

        $name     = isset($p['name']) ? (string)$p['name'] : null;
        $cat      = isset($p['category']) ? (string)$p['category'] : null;
        $currency = isset($p['currency']) && $p['currency']
            ? strtoupper((string)$p['currency'])
            : strtoupper($t->default_currency ?? 'UAH');

        $contentsIn = $req->input('contents');
        $contents   = [];
        $totalValue = null;

        if (is_array($contentsIn) && !empty($contentsIn)) {
            $sum = 0.0;
            foreach ($contentsIn as $row) {
                $row = (array)$row;
                $iid = (string)($row['id'] ?? $cid);
                $qty = max(1, (int)($row['quantity'] ?? 1));
                $ip  = (float)($row['item_price'] ?? 0);
                if ($ip < 0) $ip = 0.0;

                $contents[] = ['id' => $iid, 'quantity' => $qty, 'item_price' => $ip];
                $sum += $ip * $qty;
            }
            $totalValue = round($sum, 2);
        } else {
            $value = array_key_exists('price', $p) && $p['price'] !== null ? (float)$p['price'] : null;
            if ($value !== null && $value < 0) $value = 0.0;

            if ($value !== null) {
                $contents   = [[ 'id' => $cid, 'quantity' => 1, 'item_price' => $value ]];
                $totalValue = round($value, 2);
            }
        }

        $custom = [
            'content_type' => 'product',
            'content_ids'  => [$cid],
        ];
        if (!empty($contents))             $custom['contents']         = $contents;
        if ($name)                         $custom['content_name']     = $name;
        if ($cat)                          $custom['content_category'] = $cat;
        if ($totalValue !== null) {
            $custom['value']    = $totalValue;
            $custom['currency'] = $currency;
        }

        $event = [
            'event_name'       => 'ViewContent',
            'event_time'       => time(),
            'action_source'    => 'website',
            'event_source_url' => $eventSourceUrl,
            'event_id'         => $eventId,
            'user_data'        => $userData,
            'custom_data'      => $custom,
        ];

        $meta = new MetaCapi($t->pixel_id, $t->capi_token, $t->capi_api_version ?: 'v20.0');
        $resp = $meta->send([$event], $t->capi_test_code ?: null);

        return response()->json([
            'ok'       => $resp->successful(),
            'status'   => $resp->status(),
            'event_id' => $eventId,
        ], 200);
    }

    /**
     * CAPI: AddToCart
     * Очікує JSON з contents[]; value = сума цін * кількість.
     */
    public function atc(Request $req)
    {
        $t = DB::table('tracking_settings')->first();
        if (!$t) return response()->json(['ok' => false, 'skip' => 'no_settings'], 200);

        if ((int)($t->capi_enabled ?? 0) !== 1 || empty($t->pixel_id) || empty($t->capi_token)) {
            return response()->json(['ok' => false, 'skip' => 'capi_disabled_or_missing_creds'], 200);
        }
        if ((int)($t->exclude_admin ?? 1) === 1) {
            $path = (string) parse_url($req->fullUrl(), PHP_URL_PATH);
            if ($req->is('admin*') || str_contains($path, '/admin')) {
                return response()->json(['ok' => false, 'skip' => 'admin_excluded'], 200);
            }
        }
        if ((int)($t->send_add_to_cart ?? 1) !== 1) {
            return response()->json(['ok' => false, 'skip' => 'add_to_cart_disabled'], 200);
        }

        $eventId        = (string)($req->input('event_id') ?: ('atc-'.bin2hex(random_bytes(4)).'-'.time()));
        $eventSourceUrl = $this->eventSourceUrl($req) ?? url()->current();

        // ⛔ Гейт атрибуції
        if (!$this->hasFbAttribution($req, $eventSourceUrl)) {
            return response()->json(['ok' => false, 'skip' => 'no_fb_attribution'], 200);
        }

        $userData = $this->collectUserData($req, $eventSourceUrl);

        $currency = strtoupper((string)($req->input('currency') ?: ($t->default_currency ?? 'UAH')));
        $name     = $req->filled('name') ? (string)$req->input('name') : null;

        $contentsIn = $req->input('contents');
        if (!is_array($contentsIn) || empty($contentsIn)) {
            return response()->json(['ok' => false, 'skip' => 'missing_contents'], 200);
        }

        $contents = [];
        $sum      = 0.0;
        foreach ($contentsIn as $row) {
            $row = (array)$row;
            $id  = isset($row['id']) ? trim((string)$row['id']) : '';
            if ($id === '') continue;
            $qty = isset($row['quantity']) ? (int)$row['quantity'] : 1;
            if ($qty <= 0) $qty = 1;
            $ip  = isset($row['item_price']) ? (float)$row['item_price'] : 0.0;
            if ($ip < 0) $ip = 0.0;

            $contents[] = ['id' => $id, 'quantity' => $qty, 'item_price' => $ip];
            $sum += $qty * $ip;
        }
        if (empty($contents)) {
            return response()->json(['ok' => false, 'skip' => 'empty_contents_after_norm'], 200);
        }

        $value      = round($sum, 2);
        $contentIds = array_values(array_map(fn($c) => (string)$c['id'], $contents));

        $event = [
            'event_name'       => 'AddToCart',
            'event_time'       => time(),
            'action_source'    => 'website',
            'event_source_url' => $eventSourceUrl,
            'event_id'         => $eventId,
            'user_data'        => $userData,
            'custom_data'      => array_filter([
                'content_type' => 'product',
                'content_ids'  => $contentIds,
                'contents'     => $contents,
                'value'        => $value,
                'currency'     => $currency,
                'content_name' => $name,
            ], static fn($v) => $v !== null),
        ];

        $meta = new MetaCapi($t->pixel_id, $t->capi_token, $t->capi_api_version ?: 'v20.0');
        $resp = $meta->send([$event], $t->capi_test_code ?: null);

        return response()->json([
            'ok'       => $resp->successful(),
            'status'   => $resp->status(),
            'event_id' => $eventId,
        ], 200);
    }

    /**
     * CAPI: InitiateCheckout
     * Очікує JSON з contents[]; num_items/value — з тіла або рахуємо.
     */
    public function ic(Request $req)
    {
        $t = DB::table('tracking_settings')->first();
        if (!$t) return response()->json(['ok' => false, 'skip' => 'no_settings'], 200);

        if ((int)($t->capi_enabled ?? 0) !== 1 || empty($t->pixel_id) || empty($t->capi_token)) {
            return response()->json(['ok' => false, 'skip' => 'capi_disabled_or_missing_creds'], 200);
        }
        if ((int)($t->exclude_admin ?? 1) === 1 && $req->is('admin*')) {
            return response()->json(['ok' => false, 'skip' => 'admin_excluded'], 200);
        }
        if ((int)($t->send_initiate_checkout ?? 0) !== 1) {
            return response()->json(['ok' => false, 'skip' => 'initiate_checkout_disabled'], 200);
        }

        $eventId        = (string)($req->input('event_id') ?: ('ic-'.bin2hex(random_bytes(4)).'-'.time()));
        $eventSourceUrl = $this->eventSourceUrl($req) ?? url()->current();

        // ⛔ Гейт атрибуції
        if (!$this->hasFbAttribution($req, $eventSourceUrl)) {
            return response()->json(['ok' => false, 'skip' => 'no_fb_attribution'], 200);
        }

        $userData = $this->collectUserData($req, $eventSourceUrl);

        $currency   = strtoupper((string)($req->input('currency') ?: ($t->default_currency ?? 'UAH')));
        $name       = $req->filled('name') ? (string)$req->input('name') : null;
        $contentsIn = $req->input('contents');
        if (!is_array($contentsIn) || empty($contentsIn)) {
            return response()->json(['ok' => false, 'skip' => 'missing_contents'], 200);
        }

        $contents   = [];
        $sum        = 0.0;
        $itemsCount = 0;

        foreach ($contentsIn as $row) {
            $row = (array)$row;
            $id  = isset($row['id']) ? trim((string)$row['id']) : '';
            if ($id === '') continue;
            $qty = isset($row['quantity']) ? (int)$row['quantity'] : 1;
            if ($qty <= 0) $qty = 1;
            $ip  = isset($row['item_price']) ? (float)$row['item_price'] : 0.0;
            if ($ip < 0) $ip = 0.0;

            $contents[] = ['id' => $id, 'quantity' => $qty, 'item_price' => $ip];
            $sum        += $qty * $ip;
            $itemsCount += $qty;
        }
        if (empty($contents)) {
            return response()->json(['ok' => false, 'skip' => 'empty_contents_after_norm'], 200);
        }

        $numItems   = $req->filled('num_items') ? max(0, (int)$req->input('num_items')) : $itemsCount;
        $value      = $req->filled('value')     ? round((float)$req->input('value'), 2) : round($sum, 2);
        $contentIds = array_values(array_map(fn($c) => (string)$c['id'], $contents));

        $event = [
            'event_name'       => 'InitiateCheckout',
            'event_time'       => time(),
            'action_source'    => 'website',
            'event_source_url' => $eventSourceUrl,
            'event_id'         => $eventId,
            'user_data'        => $userData,
            'custom_data'      => array_filter([
                'content_type' => 'product',
                'content_ids'  => $contentIds,
                'contents'     => $contents,
                'num_items'    => $numItems,
                'value'        => $value,
                'currency'     => $currency,
                'content_name' => $name,
            ], static fn($v) => $v !== null),
        ];

        $meta = new MetaCapi($t->pixel_id, $t->capi_token, $t->capi_api_version ?: 'v20.0');
        $resp = $meta->send([$event], $t->capi_test_code ?: null);

        return response()->json([
            'ok'       => $resp->successful(),
            'status'   => $resp->status(),
            'event_id' => $eventId,
        ], 200);
    }

    /**
     * Lead — приклад (залишається як у тебе; якщо handleEvent має всередині перевірки, можна перенести гейт туди)
     */
    public function lead(Request $request)
    {
        return $this->handleEvent('Lead', $request, function () use ($request) {
            return [
                'content_name' => $request->input('content_name', 'lead'),
                'status'       => $request->input('status', 'submitted'),
                'value'        => $this->num($request->input('value', 0)),
                'currency'     => $request->input('currency', $this->currency()),
            ];
        }, flag: 'send_lead');
    }

    /**
     * CAPI: Purchase — очікує contents[], value/num_items/шипінг/податок/PII
     */
    public function purchase(Request $req)
    {
        $t = DB::table('tracking_settings')->first();
        if (!$t) return response()->json(['ok'=>false,'skip'=>'no_settings'], 200);

        if ((int)($t->capi_enabled ?? 0) !== 1 || empty($t->pixel_id) || empty($t->capi_token)) {
            return response()->json(['ok'=>false,'skip'=>'capi_disabled_or_missing_creds'], 200);
        }
        if ((int)($t->exclude_admin ?? 1) === 1 && $req->is('admin*')) {
            return response()->json(['ok'=>false,'skip'=>'admin_excluded'], 200);
        }
        if ((int)($t->send_purchase ?? 0) !== 1) {
            return response()->json(['ok'=>false,'skip'=>'purchase_disabled'], 200);
        }

        $eventId        = (string)($req->input('event_id') ?: ('purchase-'.bin2hex(random_bytes(4)).'-'.time()));
        $eventSourceUrl = $this->eventSourceUrl($req) ?? url()->current();

        // ⛔ Гейт атрибуції
        if (!$this->hasFbAttribution($req, $eventSourceUrl)) {
            return response()->json(['ok' => false, 'skip' => 'no_fb_attribution'], 200);
        }

        // user_data (IP/UA + fbc/fbp) + PII (email/phone/first/last/external_id → хеш)
        $userData = $this->collectUserData($req, $eventSourceUrl);
        $userData = array_merge($userData, $this->hashPiiFromRequest($req));

        $tCurrency = strtoupper($t->default_currency ?? 'UAH');
        $currency  = strtoupper((string)($req->input('currency') ?: $tCurrency));

        $contentsIn = $req->input('contents');
        if (!is_array($contentsIn) || empty($contentsIn)) {
            return response()->json(['ok'=>false,'skip'=>'missing_contents'], 200);
        }

        $contents   = [];
        $sum        = 0.0;
        $itemsCount = 0;

        foreach ($contentsIn as $row) {
            $row = (array)$row;
            $id  = isset($row['id']) ? trim((string)$row['id']) : '';
            if ($id === '') continue;
            $qty = isset($row['quantity']) ? (int)$row['quantity'] : 1;
            if ($qty <= 0) $qty = 1;
            $ip  = isset($row['item_price']) ? (float)$row['item_price'] : 0.0;
            if ($ip < 0) $ip = 0.0;

            $contents[] = ['id' => $id, 'quantity' => $qty, 'item_price' => $ip];
            $sum        += $qty * $ip;
            $itemsCount += $qty;
        }
        if (empty($contents)) {
            return response()->json(['ok'=>false,'skip'=>'empty_contents_after_norm'], 200);
        }

        $shipping  = $req->filled('shipping') ? max(0, (float)$req->input('shipping')) : 0.0;
        $tax       = $req->filled('tax')      ? max(0, (float)$req->input('tax'))      : 0.0;
        $numItems  = $req->filled('num_items') ? max(0, (int)$req->input('num_items')) : $itemsCount;
        $value     = $req->filled('value')     ? round((float)$req->input('value'), 2) : round($sum + $shipping + $tax, 2);
        $orderNo   = $req->filled('order_number') ? (string)$req->input('order_number') : null;
        $contentIds= array_values(array_map(fn($c) => (string)$c['id'], $contents));

        $event = [
            'event_name'       => 'Purchase',
            'event_time'       => time(),
            'action_source'    => 'website',
            'event_source_url' => $eventSourceUrl,
            'event_id'         => $eventId,
            'user_data'        => $userData,
            'custom_data'      => array_filter([
                'content_type' => 'product',
                'content_ids'  => $contentIds,
                'contents'     => $contents,
                'num_items'    => $numItems,
                'value'        => $value,
                'currency'     => $currency,
                'shipping'     => $shipping,
                'tax'          => $tax,
                'order_number' => $orderNo,
            ], static fn($v) => $v !== null),
        ];

        $meta = new MetaCapi($t->pixel_id, $t->capi_token, $t->capi_api_version ?: 'v20.0');
        $resp = $meta->send([$event], $t->capi_test_code ?: null);

        return response()->json([
            'ok'       => $resp->successful(),
            'status'   => $resp->status(),
            'event_id' => $eventId,
        ], 200);
    }

    /**
     * Перевірка на атрибуцію FB: є _fbc у cookie АБО fbclid у будь-якому з доступних URL.
     */
    private function hasFbAttribution(Request $req, ?string $eventSourceUrl = null): bool
    {
        // cookie _fbc
        $cookieFbc = $req->cookie('_fbc');
        if (is_string($cookieFbc) && trim($cookieFbc) !== '') {
            return true;
        }

        // пошук fbclid у кількох кандидатах
        $candidates = [];
        if ($eventSourceUrl) $candidates[] = $eventSourceUrl;
        if ($r = $req->headers->get('referer')) $candidates[] = (string)$r;
        $candidates[] = $req->fullUrl();
        if (is_string($req->input('page_url'))) {
            $candidates[] = (string)$req->input('page_url');
        }

        foreach ($candidates as $u) {
            if ($this->extractFbclid($u)) {
                return true;
            }
        }

        return false;
        // Якщо потрібно дозволити _fbp як окремий тригер — можна розширити умову.
    }

    /**
     * PII → SHA-256 згідно Meta (email/phone/first/last/external_id).
     */
    private function hashPiiFromRequest(Request $req): array
    {
        $out = [];

        $email = $req->input('email');
        if (is_string($email) && $email !== '') {
            $norm = strtolower(trim($email));
            $out['em'] = hash('sha256', $norm);
        }

        $phone = $req->input('phone');
        if (is_string($phone) && $phone !== '') {
            $p = preg_replace('/\D+/', '', $phone);
            if (strpos(trim($phone), '+') === 0) $p = '+' . $p;
            $out['ph'] = hash('sha256', $p);
        }

        $fn = $req->input('first_name');
        if (is_string($fn) && $fn !== '') {
            $out['fn'] = hash('sha256', mb_strtolower(trim($fn)));
        }

        $ln = $req->input('last_name');
        if (is_string($ln) && $ln !== '') {
            $out['ln'] = hash('sha256', mb_strtolower(trim($ln)));
        }

        $eid = $req->input('external_id');
        if (is_string($eid) && $eid !== '') {
            $out['external_id'] = hash('sha256', trim($eid));
        }

        return $out;
    }

    /**
     * Визначаємо джерело URL події: 1) page_url; 2) Referer; 3) поточний URL.
     */
    private function eventSourceUrl(Request $req): ?string
    {
        if ($u = $req->input('page_url')) return (string)$u;
        if ($r = $req->headers->get('referer')) return (string)$r;
        return $req->fullUrl();
    }

    /**
     * user_data: IP, UA, fbc/fbp (якщо є). Якщо _fbc нема, але є fbclid — генеруємо fbc.
     */
    private function collectUserData(Request $req, string $eventSourceUrl): array
    {
        $data = [
            'client_ip_address' => (string)$req->ip(),
            'client_user_agent' => (string)$req->userAgent(),
        ];

        if (is_string($req->cookie('_fbc'))) {
            $v = trim((string)$req->cookie('_fbc'));
            if ($v !== '') $data['fbc'] = $v;
        }
        if (is_string($req->cookie('_fbp'))) {
            $v = trim((string)$req->cookie('_fbp'));
            if ($v !== '') $data['fbp'] = $v;
        }

        // fallback для fbc, якщо є fbclid
        if (!isset($data['fbc'])) {
            $fbclid = $this->extractFbclid($eventSourceUrl) ?: $this->extractFbclid($req->fullUrl());
            if (!$fbclid && is_string($req->input('page_url'))) {
                $fbclid = $this->extractFbclid((string)$req->input('page_url'));
            }
            if ($fbclid) {
                $data['fbc'] = 'fb.2.' . time() . '.' . $fbclid;
            }
        }

        return $data;
    }

    /**
     * Дістаємо fbclid із рядка URL.
     */
    private function extractFbclid(?string $url): ?string
    {
        if (!$url) return null;
        $parts = parse_url($url);
        if (!isset($parts['query'])) return null;

        parse_str($parts['query'], $qs);
        $fbclid = $qs['fbclid'] ?? null;

        $fbclid = is_string($fbclid) ? trim($fbclid) : null;
        return ($fbclid !== '') ? $fbclid : null;
    }
}
