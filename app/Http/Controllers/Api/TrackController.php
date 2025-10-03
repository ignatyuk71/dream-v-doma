<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\MetaCapi;

/**
 * Контролер серверних подій для Meta CAPI.
 * Кожен публічний метод — це ендпойнт події (PV/VC/ATC/IC/Lead),
 * які зводяться у спільну логіку через handleEvent().
 */
class TrackController extends Controller
{

    /**
     * CAPI: PageView
     * Приймає з фронта: event_id (опц.), page_url (опц.)
     * Відправляє у Meta: PageView з user_data (IP/UA + fbc/fbp)
     */
    public function pv(Request $req)
    {
        // 1) Налаштування
        $t = DB::table('tracking_settings')->first();
        if (!$t) {
            return response()->json(['ok' => false, 'skip' => 'no_settings'], 200);
        }

        // CAPI має бути увімкнено, і мають бути pixel_id + token
        if ((int)($t->capi_enabled ?? 0) !== 1 || empty($t->pixel_id) || empty($t->capi_token)) {
            return response()->json(['ok' => false, 'skip' => 'capi_disabled_or_missing_creds'], 200);
        }

        // Не стріляємо з адмін-зон (за правилом exclude_admin)
        if ((int)($t->exclude_admin ?? 1) === 1 && $req->is('admin*')) {
            return response()->json(['ok' => false, 'skip' => 'admin_excluded'], 200);
        }

        // Сьогодні працюємо лише з PageView — прапорець має дозволяти
        if (!(bool)($t->send_page_view ?? true)) {
            return response()->json(['ok' => false, 'skip' => 'page_view_disabled'], 200);
        }

        // 2) Побудова події
        $eventId = (string)($req->input('event_id') ?: ('pv-'.bin2hex(random_bytes(4)).'-'.time()));
        $eventSourceUrl = $this->eventSourceUrl($req) ?? url()->current();

        $userData = $this->collectUserData($req, $eventSourceUrl);

        $event = [
            'event_name'       => 'PageView',
            'event_time'       => time(),                 // unix seconds
            'action_source'    => 'website',
            'event_source_url' => $eventSourceUrl,
            'event_id'         => $eventId,
            'user_data'        => $userData,
        ];

        // 3) Відправка через сервіс
        $apiVersion = $t->capi_api_version ?: 'v20.0';
        $testCode   = $t->capi_test_code ?: null;

        $meta = new MetaCapi($t->pixel_id, $t->capi_token, $apiVersion);
        $resp = $meta->send([$event], $testCode);

        // 4) Відповідь фронту (логування робить сервіс)
        return response()->json([
            'ok'        => $resp->successful(),
            'status'    => $resp->status(),
            'event_id'  => $eventId,
        ], 200);
    }

   
    /**
     * ViewContent — подія перегляду товару/контенту.
     *
     * 🔹 Використовується для відслідковування відвідування сторінки товару.
     * 🔹 Meta рекомендує надсилати масив contents[] у форматі:
     *     [{ "id": "SKU123", "quantity": 1, "item_price": 399.00 }]
     * 🔹 Якщо contents[] не передане — використовуємо "фолбек" з id/sku/price/quantity.
     * 🔹 Значення value = сума (ціна * кількість).
     * 🔹 Валюта береться з запиту або з налаштувань (default = UAH).
     */
    
     public function vc(\Illuminate\Http\Request $req)
     {
         // 1) Налаштування
         $t = \DB::table('tracking_settings')->first();
         if (!$t) {
             return response()->json(['ok' => false, 'skip' => 'no_settings'], 200);
         }
     
         if ((int)($t->capi_enabled ?? 0) !== 1 || empty($t->pixel_id) || empty($t->capi_token)) {
             return response()->json(['ok' => false, 'skip' => 'capi_disabled_or_missing_creds'], 200);
         }
     
         // Вимкнути з адмін-зон (плюс підстраховка по URL)
         if ((int)($t->exclude_admin ?? 1) === 1) {
             $path = (string) parse_url($req->fullUrl(), PHP_URL_PATH);
             if ($req->is('admin*') || str_contains($path, '/admin')) {
                 return response()->json(['ok' => false, 'skip' => 'admin_excluded'], 200);
             }
         }
     
         // Дозвіл саме на VC
         if (!(bool)($t->send_view_content ?? true)) {
             return response()->json(['ok' => false, 'skip' => 'view_content_disabled'], 200);
         }
     
         // 2) Подія
         $eventId        = (string)($req->input('event_id') ?: ('vc-'.bin2hex(random_bytes(4)).'-'.time()));
         $eventSourceUrl = $this->eventSourceUrl($req) ?? url()->current();
         $userData       = $this->collectUserData($req, $eventSourceUrl); // IP/UA + fbc/fbp (з валідацією fbclid у collectUserData)
     
         // 3) Дані товару: або product{...}, або contents[]
         $p        = (array)($req->input('product') ?? []);
         $sku      = isset($p['sku']) ? (string)$p['sku'] : null;
         $id       = isset($p['id'])  ? (string)$p['id']  : null;
         $cid      = $sku ?: $id; // content_id (краще SKU)
         if (!$cid) {
             return response()->json(['ok' => false, 'skip' => 'missing_content_id'], 200);
         }
     
         $name     = isset($p['name']) ? (string)$p['name'] : null;
         $cat      = isset($p['category']) ? (string)$p['category'] : null;
         $currency = isset($p['currency']) && $p['currency'] ? strtoupper((string)$p['currency']) : strtoupper($t->default_currency ?? 'UAH');
     
         // Якщо прийшов масив contents[] — нормалізуємо і перерахуємо value
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
     
                 $contents[] = [
                     'id'         => $iid,
                     'quantity'   => $qty,
                     'item_price' => $ip,
                 ];
                 $sum += $ip * $qty;
             }
             $totalValue = round($sum, 2);
         } else {
             // Фолбек: 1 товар із ціною, якщо вона є
             $value = array_key_exists('price', $p) && $p['price'] !== null ? (float)$p['price'] : null;
             if ($value !== null && $value < 0) $value = 0.0;
     
             if ($value !== null) {
                 $contents   = [[ 'id' => $cid, 'quantity' => 1, 'item_price' => $value ]];
                 $totalValue = round($value, 2);
             }
         }
     
         // 4) custom_data для VC
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
     
         // 5) Збирання і відправка
         $event = [
             'event_name'       => 'ViewContent',
             'event_time'       => time(),
             'action_source'    => 'website',
             'event_source_url' => $eventSourceUrl,
             'event_id'         => $eventId,
             'user_data'        => $userData,
             'custom_data'      => $custom,
         ];
     
         $meta = new \App\Services\MetaCapi($t->pixel_id, $t->capi_token, $t->capi_api_version ?: 'v20.0');
         $resp = $meta->send([$event], $t->capi_test_code ?: null);
     
         return response()->json([
             'ok'       => $resp->successful(),
             'status'   => $resp->status(),
             'event_id' => $eventId,
         ], 200);
     }
     
    
    /**
     * CAPI: AddToCart
     * Очікує з фронта (JSON):
     * {
     *   "event_id": "atc-... (опц.)",
     *   "page_url": "https://... (опц.)",
     *   "currency": "UAH|USD|...",
     *   "contents": [{ "id":"SKU", "quantity":1, "item_price":399.00 }, ...],
     *   "name": "Назва товару (опц.)"
     * }
     */
    public function atc(\Illuminate\Http\Request $req)
    {
        // 1) Налаштування
        $t = \DB::table('tracking_settings')->first();
        if (!$t) {
            return response()->json(['ok' => false, 'skip' => 'no_settings'], 200);
        }

        // CAPI має бути увімкнений і мають бути креденшіали
        if ((int)($t->capi_enabled ?? 0) !== 1 || empty($t->pixel_id) || empty($t->capi_token)) {
            return response()->json(['ok' => false, 'skip' => 'capi_disabled_or_missing_creds'], 200);
        }

        // Виключити адмін-зони (підстраховка і по URL-шляху)
        if ((int)($t->exclude_admin ?? 1) === 1) {
            $path = (string) parse_url($req->fullUrl(), PHP_URL_PATH);
            if ($req->is('admin*') || str_contains($path, '/admin')) {
                return response()->json(['ok' => false, 'skip' => 'admin_excluded'], 200);
            }
        }

        // Подія має бути дозволена
        if ((int)($t->send_add_to_cart ?? 1) !== 1) {
            return response()->json(['ok' => false, 'skip' => 'add_to_cart_disabled'], 200);
        }

        // 2) Заголовки події
        $eventId        = (string)($req->input('event_id') ?: ('atc-'.bin2hex(random_bytes(4)).'-'.time()));
        $eventSourceUrl = $this->eventSourceUrl($req) ?? url()->current();
        $userData       = $this->collectUserData($req, $eventSourceUrl); // IP/UA + fbc/fbp з валід. fbclid

        // 3) Дані кошика з тіла
        $currency = strtoupper((string)($req->input('currency') ?: ($t->default_currency ?? 'UAH')));
        $name     = $req->filled('name') ? (string)$req->input('name') : null;

        // contents[]
        $contentsIn = $req->input('contents');
        if (!is_array($contentsIn) || empty($contentsIn)) {
            return response()->json(['ok' => false, 'skip' => 'missing_contents'], 200);
        }

        $contents   = [];
        $sum        = 0.0;
        foreach ($contentsIn as $row) {
            $row = (array)$row;

            $id  = isset($row['id']) ? trim((string)$row['id']) : '';
            if ($id === '') continue;

            $qty = isset($row['quantity']) ? (int)$row['quantity'] : 1;
            if ($qty <= 0) $qty = 1;

            $ip  = isset($row['item_price']) ? (float)$row['item_price'] : 0.0;
            if ($ip < 0) $ip = 0.0;

            $contents[] = [
                'id'         => $id,
                'quantity'   => $qty,
                'item_price' => $ip,
            ];
            $sum += $qty * $ip;
        }

        if (empty($contents)) {
            return response()->json(['ok' => false, 'skip' => 'empty_contents_after_norm'], 200);
        }

        $value = round($sum, 2);
        $contentIds = array_values(array_map(fn($c) => (string)$c['id'], $contents));

        // 4) Збирання події
        $event = [
            'event_name'       => 'AddToCart',
            'event_time'       => time(),
            'action_source'    => 'website',
            'event_source_url' => $eventSourceUrl,
            'event_id'         => $eventId,
            'user_data'        => $userData,
            'custom_data'      => array_filter([
                'content_type'     => 'product',
                'content_ids'      => $contentIds,
                'contents'         => $contents,
                'value'            => $value,
                'currency'         => $currency,
                'content_name'     => $name, // опційно
            ], static fn($v) => $v !== null),
        ];

        // 5) Відправка у Meta
        $apiVersion = $t->capi_api_version ?: 'v20.0';
        $testCode   = $t->capi_test_code ?: null;

        $meta = new \App\Services\MetaCapi($t->pixel_id, $t->capi_token, $apiVersion);
        $resp = $meta->send([$event], $testCode);

        return response()->json([
            'ok'       => $resp->successful(),
            'status'   => $resp->status(),
            'event_id' => $eventId,
        ], 200);
    }




    
    /**
     * CAPI: InitiateCheckout
     * Очікує JSON:
     * {
     *   "event_id": "ic-..."              (опц.)
     *   "page_url": "https://..."         (опц.)
     *   "currency": "UAH|USD|..."         (опц.; fallback -> settings.default_currency)
     *   "contents": [                     (обов’язково; ≥1 валідний item)
     *     { "id":"SKU", "quantity":1, "item_price":399.00 }, ...
     *   ],
     *   "num_items": 3                    (опц.; якщо нема — рахуємо із contents)
     *   "name": "Перший товар"            (опц.)
     *   "value": 1197.00                  (опц.; якщо нема — рахуємо із contents)
     * }
     */
    public function ic(Request $req)
    {
        // 1) Налаштування
        $t = DB::table('tracking_settings')->first();
        if (!$t) {
            return response()->json(['ok' => false, 'skip' => 'no_settings'], 200);
        }

        // CAPI вмикнено та є креденшіали
        if ((int)($t->capi_enabled ?? 0) !== 1 || empty($t->pixel_id) || empty($t->capi_token)) {
            return response()->json(['ok' => false, 'skip' => 'capi_disabled_or_missing_creds'], 200);
        }

        // Виключити адмін-зони
        if ((int)($t->exclude_admin ?? 1) === 1 && $req->is('admin*')) {
            return response()->json(['ok' => false, 'skip' => 'admin_excluded'], 200);
        }

        // Подія дозволена?
        if ((int)($t->send_initiate_checkout ?? 0) !== 1) {
            return response()->json(['ok' => false, 'skip' => 'initiate_checkout_disabled'], 200);
        }

        // 2) Заголовки події
        $eventId        = (string)($req->input('event_id') ?: ('ic-'.bin2hex(random_bytes(4)).'-'.time()));
        $eventSourceUrl = $this->eventSourceUrl($req) ?? url()->current();
        $userData       = $this->collectUserData($req, $eventSourceUrl);

        // 3) Дані кошика
        $currency = strtoupper((string)($req->input('currency') ?: ($t->default_currency ?? 'UAH')));
        $name     = $req->filled('name') ? (string)$req->input('name') : null;

        // contents[]
        $contentsIn = $req->input('contents');
        if (!is_array($contentsIn) || empty($contentsIn)) {
            return response()->json(['ok' => false, 'skip' => 'missing_contents'], 200);
        }

        $contents   = [];
        $sum        = 0.0;
        $itemsCount = 0;

        foreach ($contentsIn as $row) {
            $row = (array)$row;

            $id = isset($row['id']) ? trim((string)$row['id']) : '';
            if ($id === '') continue;

            $qty = isset($row['quantity']) ? (int)$row['quantity'] : 1;
            if ($qty <= 0) $qty = 1;

            $ip = isset($row['item_price']) ? (float)$row['item_price'] : 0.0;
            if ($ip < 0) $ip = 0.0;

            $contents[] = [
                'id'         => $id,
                'quantity'   => $qty,
                'item_price' => $ip,
            ];
            $sum        += $qty * $ip;
            $itemsCount += $qty;
        }

        if (empty($contents)) {
            return response()->json(['ok' => false, 'skip' => 'empty_contents_after_norm'], 200);
        }

        // num_items / value — беремо з тіла або рахуємо
        $numItems = $req->filled('num_items') ? max(0, (int)$req->input('num_items')) : $itemsCount;
        $value    = $req->filled('value')     ? round((float)$req->input('value'), 2) : round($sum, 2);

        $contentIds = array_values(array_map(fn($c) => (string)$c['id'], $contents));

        // 4) Подія
        $event = [
            'event_name'       => 'InitiateCheckout',
            'event_time'       => time(),
            'action_source'    => 'website',
            'event_source_url' => $eventSourceUrl,
            'event_id'         => $eventId,
            'user_data'        => $userData,
            'custom_data'      => array_filter([
                'content_type'     => 'product',
                'content_ids'      => $contentIds,
                'contents'         => $contents,
                'num_items'        => $numItems,
                'value'            => $value,
                'currency'         => $currency,
                'content_name'     => $name, // опційно
            ], static fn($v) => $v !== null),
        ];

        // 5) Відправка у Meta
        $apiVersion = $t->capi_api_version ?: 'v20.0';
        $testCode   = $t->capi_test_code ?: null;

        $meta = new MetaCapi($t->pixel_id, $t->capi_token, $apiVersion);
        $resp = $meta->send([$event], $testCode);

        return response()->json([
            'ok'       => $resp->successful(),
            'status'   => $resp->status(),
            'event_id' => $eventId,
        ], 200);
    }


    /**
     * Lead — надсилання лід-події з довільними полями content_name/status/value.
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
     * CAPI: Purchase
     * Очікує JSON:
     * {
     *   "event_id": "purchase-...",              // опц.
     *   "page_url": "https://...",               // опц.
     *   "currency": "UAH|USD|...",               // опц. (fallback: settings.default_currency)
     *   "contents": [                            // обов'язково: ≥1
     *     { "id":"SKU", "quantity":1, "item_price":399.00 }, ...
     *   ],
     *   "num_items": 3,                          // опц. (якщо нема — рахуємо)
     *   "value": 1299.00,                        // опц. (якщо нема — subtotal + shipping + tax)
     *   "shipping": 0,                           // опц.
     *   "tax": 0,                                // опц.
     *   "order_number": "A12345",                // опц. (для ідентифікації замовлення)
     *
     *   // опційно для кращого матчінгу (бек їх ХЕШУЄ у user_data):
     *   "email": "user@example.com",
     *   "phone": "+380501112233",
     *   "first_name": "Ivan",
     *   "last_name": "Petrenko",
     *   "external_id": "uid_123"
     * }
     */
    public function purchase(Request $req)
    {
        // 1) Налаштування
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

        // 2) Заголовки події
        $eventId        = (string)($req->input('event_id') ?: ('purchase-'.bin2hex(random_bytes(4)).'-'.time()));
        $eventSourceUrl = $this->eventSourceUrl($req) ?? url()->current();

        // Базовий user_data (IP/UA + fbc/fbp)
        $userData = $this->collectUserData($req, $eventSourceUrl);

        // Додатковий PII → хеш у user_data
        $userData = array_merge($userData, $this->hashPiiFromRequest($req));

        // 3) Дані замовлення
        $currency = strtoupper((string)($req->input('currency') ?: ($t->default_currency ?? 'UAH')));

        $contentsIn = $req->input('contents');
        if (!is_array($contentsIn) || empty($contentsIn)) {
            return response()->json(['ok'=>false,'skip'=>'missing_contents'], 200);
        }

        $contents   = [];
        $sum        = 0.0;
        $itemsCount = 0;

        foreach ($contentsIn as $row) {
            $row = (array)$row;

            $id = isset($row['id']) ? trim((string)$row['id']) : '';
            if ($id === '') continue;

            $qty = isset($row['quantity']) ? (int)$row['quantity'] : 1;
            if ($qty <= 0) $qty = 1;

            $ip = isset($row['item_price']) ? (float)$row['item_price'] : 0.0;
            if ($ip < 0) $ip = 0.0;

            $contents[] = [
                'id'         => $id,
                'quantity'   => $qty,
                'item_price' => $ip,
            ];
            $sum        += $qty * $ip;
            $itemsCount += $qty;
        }
        if (empty($contents)) {
            return response()->json(['ok'=>false,'skip'=>'empty_contents_after_norm'], 200);
        }

        $shipping = $req->filled('shipping') ? max(0, (float)$req->input('shipping')) : 0.0;
        $tax      = $req->filled('tax')      ? max(0, (float)$req->input('tax'))      : 0.0;

        // value/num_items
        $numItems = $req->filled('num_items') ? max(0, (int)$req->input('num_items')) : $itemsCount;
        $value    = $req->filled('value')     ? round((float)$req->input('value'), 2) : round($sum + $shipping + $tax, 2);

        $orderNo  = $req->filled('order_number') ? (string)$req->input('order_number') : null;
        $contentIds = array_values(array_map(fn($c) => (string)$c['id'], $contents));

        // 4) Подія
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

        // 5) Відправка
        $meta = new MetaCapi($t->pixel_id, $t->capi_token, $t->capi_api_version ?: 'v20.0');
        $resp = $meta->send([$event], $t->capi_test_code ?: null);

        return response()->json([
            'ok'       => $resp->successful(),
            'status'   => $resp->status(),
            'event_id' => $eventId,
        ], 200);
    }

    /**
     * PII → SHA-256 згідно Meta (lowercase/trim/без пробілів, телефон у цифри з +, email lowercase).
     * Повертає масив з ключами, які очікує Meta: em, ph, fn, ln, external_id (усе захешоване, окрім external_id — дозволяється raw або hashed).
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
            // нормалізуємо: лишаємо цифри; якщо був + на початку — зберігаємо
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

        // external_id: Meta дозволяє raw, але краще теж хешувати для приватності
        $eid = $req->input('external_id');
        if (is_string($eid) && $eid !== '') {
            $out['external_id'] = hash('sha256', trim($eid));
        }

        return $out;
    }

     /**
     * Визначаємо джерело URL події:
     * 1) явний page_url з тіла; 2) Referer; 3) поточний URL
     */
    private function eventSourceUrl(Request $req): ?string
    {
        if ($u = $req->input('page_url')) return (string)$u;
        if ($r = $req->headers->get('referer')) return (string)$r;
        return $req->fullUrl();
    }

    /**
     * user_data для CAPI: IP, UA, fbc/fbp (якщо є).
     * Якщо _fbc немає, але є fbclid у URL → згенерувати fbc.
     */
    private function collectUserData(Request $req, string $eventSourceUrl): array
    {
        $data = [
            'client_ip_address' => (string)$req->ip(),
            'client_user_agent' => (string)$req->userAgent(),
        ];

        // _fbc/_fbp з кукі (якщо є)
        if (is_string($req->cookie('_fbc'))) {
            $v = trim((string)$req->cookie('_fbc'));
            if ($v !== '') $data['fbc'] = $v;
        }
        if (is_string($req->cookie('_fbp'))) {
            $v = trim((string)$req->cookie('_fbp'));
            if ($v !== '') $data['fbp'] = $v;
        }

        // fallback для fbc: якщо нема кукі, але в URL є fbclid → згенерувати fbc
        if (!isset($data['fbc'])) {
            $fbclid = $this->extractFbclid($eventSourceUrl) ?: $this->extractFbclid($req->fullUrl());
            if (!$fbclid && is_string($req->input('page_url'))) {
                $fbclid = $this->extractFbclid((string)$req->input('page_url'));
            }
            if ($fbclid) {
                // формат: fb.2.<timestamp>.<fbclid>
                $data['fbc'] = 'fb.2.' . time() . '.' . $fbclid;
            }
        }

        return $data;
    }

    /**
     * Дістаємо fbclid із рядка URL, якщо він там є.
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
