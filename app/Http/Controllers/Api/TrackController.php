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
    /** Кеш налаштувань у межах одного HTTP-запиту (мінус зайві звернення до БД) */
    private ?object $settingsCache = null;

    /* ===================== PUBLIC ENDPOINTS ===================== */

    /**
     * PageView — базова подія перегляду сторінки.
     * Нічого не пишемо в custom_data (рекомендація Meta).
     * Дедуп: бажано передавати з фронта той самий event_id у fbq і в цей ендпойнт.
     */
    public function pv(Request $request)
    {
        return $this->handleEvent('PageView', $request, function () {
            return []; // PV без custom_data
        }, flag: 'send_page_view'); // якщо прапорця немає у БД — вважаємо увімкненим
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
    public function vc(Request $request)
    {
        return $this->handleEvent('ViewContent', $request, function () use ($request) {
    
            // --- 1) Новий формат: contents[] = [{id, quantity, item_price}]
            $contents = $this->contentsFromRequest($request);
    
            if (!empty($contents)) {
                $value = $this->calcValue($contents);
    
                return [
                    'content_type' => 'product',
                    'content_ids'  => array_map(fn($c) => (string)$c['id'], $contents), // масив ID
                    'contents'     => $contents,                                       // деталі товарів
                    'value'        => $value,                                          // сума
                    'currency'     => strtoupper(trim((string)$request->input('currency', $this->currency()))), // валюта
                    'content_name' => $request->input('content_name') ?? $request->input('name'), // назва (опц.)
                ];
            }
    
            // --- 2) Фолбек: окремі поля (id/sku + price + quantity)
            $pid      = (string)($request->input('id') ?? $request->input('sku') ?? '');
            $price    = $this->num(
                $request->input('price', $request->input('item_price', $request->input('value', 0)))
            );
            $qty      = (int)$request->input('quantity', 1);
            $currency = strtoupper(trim((string)$request->input('currency', $this->currency())));
    
            $data = [
                'content_type' => 'product',
                'content_ids'  => $pid ? [$pid] : [],                // ID товару
                'value'        => $this->num($price * max(1, $qty)), // вартість = ціна * кількість
                'currency'     => $currency,
            ];
    
            // додаємо contents[], якщо є ID
            if ($pid) {
                $data['contents'] = [[
                    'id'         => $pid,
                    'quantity'   => $qty,
                    'item_price' => $price,
                ]];
            }
    
            // додаємо назву, якщо передана (content_name або name)
            if ($request->filled('content_name') || $request->filled('name')) {
                $data['content_name'] = (string) ($request->input('content_name') ?? $request->input('name'));
            }
    
            return $data;
        }, flag: 'send_view_content');
    }
    


    /**
     * AddToCart — подія додавання товару в кошик.
     *
     * 🔹 Використовується для відслідковування натиску кнопки «Додати в кошик».
     * 🔹 Meta рекомендує надсилати масив contents[] у форматі:
     *     [{ "id": "SKU123", "quantity": 2, "item_price": 799.00 }]
     * 🔹 Якщо contents[] немає — використовуємо "фолбек" з id/sku/price/quantity.
     * 🔹 Значення value = або передане явно, або обчислене як ціна * кількість.
     * 🔹 Валюта береться з запиту або з налаштувань (default = UAH).
     */
    public function atc(Request $request)
    {
        return $this->handleEvent('AddToCart', $request, function () use ($request) {
    
            // --- 1) Новий формат: contents[] = [{id, quantity, item_price}]
            $contents = $this->contentsFromRequest($request);
    
            if (!empty($contents)) {
                // Якщо є value у запиті — беремо його, інакше рахуємо самі
                $value = $request->filled('value')
                    ? $this->num($request->input('value'))
                    : $this->calcValue($contents);
    
                return [
                    'content_type' => 'product',
                    'content_ids'  => array_map(fn($c) => (string)$c['id'], $contents), // масив ID
                    'contents'     => $contents,                                       // товари з qty і цінами
                    'value'        => $value,                                          // сума
                    'currency'     => strtoupper(trim((string)$request->input('currency', $this->currency()))), // валюта
                ];
            }
    
            // --- 2) Фолбек: окремі поля (id/sku + price + quantity)
            $pid      = (string)($request->input('id') ?? $request->input('sku') ?? '');
            $qty      = (int)$request->input('quantity', 1);
            $price    = $this->num($request->input('price', $request->input('item_price', 0)));
            $currency = strtoupper(trim((string)$request->input('currency', $this->currency())));
            $value    = $this->num($qty * $price);
    
            return [
                'content_type' => 'product',
                'content_ids'  => $pid ? [$pid] : [], // масив із одним ID (або пустий)
                'contents'     => $pid ? [[           // contents з одним елементом
                    'id'         => $pid,
                    'quantity'   => $qty,
                    'item_price' => $price,
                ]] : [],
                'value'        => $value,
                'currency'     => $currency,
            ];
        }, flag: 'send_add_to_cart');
    }
    

    /**
     * InitiateCheckout — початок оформлення замовлення.
     *
     * 🔹 Основний формат: contents[] = [{ id, quantity, item_price }]
     * 🔹 Fallback: items[]/старі поля → нормалізуємо у contents[]
     * 🔹 value = передане явно або сума (qty * item_price)
     * 🔹 Додаємо content_ids[] для сумісності з рекомендаціями Meta
     * 🔹 (опц.) content_name — якщо передано
     */
    public function ic(Request $request)
    {
        return $this->handleEvent('InitiateCheckout', $request, function () use ($request) {
    
            // 1) Основний шлях: contents[] з тіла
            $contents = $this->contentsFromRequest($request);
    
            // 2) Fallback: items[] → приводимо до contents[]
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
    
            // 3) Підсумки: ТІЛЬКИ subtotal (без shipping/tax)
            $subtotal = $this->calcValue($contents);
            $value    = $request->filled('value')
                ? $this->num($request->input('value'))   // якщо явно передали — беремо як є
                : $this->num($subtotal);                 // інакше — сума позицій
    
            if ($value < 0) $value = 0.00;
    
            $currency = strtoupper(trim((string)$request->input('currency', $this->currency())));
            $numItems = array_reduce($contents, fn($s, $c) => $s + (int)$c['quantity'], 0);
            $ids      = array_map(fn($c) => (string)$c['id'], $contents);
    
            // 4) custom_data
            $data = [
                'content_type' => 'product',
                'content_ids'  => $ids,
                'contents'     => $contents,
                'num_items'    => $numItems,
                'value'        => $value,
                'currency'     => $currency,
            ];
    
            // опціонально: назва (якщо прийшла з фронта)
            if ($request->filled('content_name') || $request->filled('name')) {
                $data['content_name'] = (string)($request->input('content_name') ?? $request->input('name'));
            }
    
            return $data;
        }, flag: 'send_initiate_checkout');
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
     * Purchase — підтвердження покупки.
     *
     * 🔹 Основний формат: contents[] = [{ id, quantity, item_price }]
     * 🔹 Fallback: items[] (variant_sku|sku|id, quantity, price|item_price) → нормалізуємо до contents[]
     * 🔹 value = передане явно або (сума позицiй + shipping + tax)
     * 🔹 Додаємо content_ids[], num_items, currency (UPPERCASE)
     * 🔹 (опц.) content_name / order_number — якщо передані
     */
    public function purchase(Request $request)
    {
        return $this->handleEvent('Purchase', $request, function () use ($request) {
            // 1) Основний шлях: contents[] = [{ id, quantity, item_price }]
            $contents = $this->contentsFromRequest($request);

            // 2) Фолбек: items[] → приводимо до contents[]
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

           // 3) Суми / валюта
            $shipping = $this->num($request->input('shipping', 0));
            $tax      = $this->num($request->input('tax', 0));

            // ❗ value = тільки сума товарів (subtotal)
            $subtotal = $this->calcValue($contents);

            $value = $request->filled('value')
                ? $this->num($request->input('value'))
                : $this->num($subtotal);

            if ($value < 0) $value = 0.00; // на всяк випадок від від’ємних

            $currency   = strtoupper((string)$request->input('currency', $this->currency()));
            $numItems   = array_reduce($contents, fn($s, $c) => $s + (int)$c['quantity'], 0);
            $contentIds = array_map(fn($c) => (string)$c['id'], $contents);

            // 4) custom_data для Meta
            $data = [
                'content_type' => 'product',
                'content_ids'  => $contentIds,
                'contents'     => $contents,
                'num_items'    => $numItems,
                'value'        => $value,
                'currency'     => $currency,
            ];

            // необов’язкові поля — додаємо, якщо є
            if ($shipping > 0) $data['shipping'] = $shipping;
            if ($tax > 0)      $data['tax']      = $tax;

            if ($request->filled('order_number')) {
                $data['order_number'] = (string) $request->input('order_number');
            }
            if ($request->filled('content_name') || $request->filled('name')) {
                $data['content_name'] = (string) ($request->input('content_name') ?? $request->input('name'));
            }

            return $data;
        }, flag: 'send_purchase');
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
    
        // 0) Глобально вимкнено CAPI
        if (!$s || (int)($s->capi_enabled ?? 0) !== 1) {
            return response()->json(['ok' => true, 'skipped' => 'capi_disabled'], 202);
        }
    
        // 1) Перевірка прапорця конкретної події
        if (!$this->flagEnabled($s, $flag)) {
            return response()->json(['ok' => true, 'skipped' => "flag_{$flag}_disabled"], 202);
        }
    
        // 2) Адмінські урли — відсікти
        if ((int)($s->exclude_admin ?? 1) === 1) {
            $url = $this->eventSourceUrl($req);
            if ($this->looksLikeAdmin($url) || $req->is('admin*')) {
                return response()->json(['ok' => true, 'skipped' => 'admin_excluded'], 202);
            }
        }
    
        // 3) Наявність Pixel/Token
        $pixelId = (string)($s->pixel_id ?? '');
        $token   = (string)($s->capi_token ?? '');
        if ($pixelId === '' || $token === '') {
            return response()->json(['ok' => false, 'error' => 'missing_pixel_or_token'], 422);
        }
    
        // 4) Зібрати user_data та зупинитись, якщо немає валідного _fbc
        $ud = $this->userData($req);
        if (empty($ud)) {
            // немає _fbc (або він плейсхолдер) → НЕ шлемо подію
            return response()->json(['ok' => true, 'skipped' => 'no_valid_fbc'], 202);
        }
    
        // 5) custom_data (за потреби)
        $custom = $buildCustomData();
    
        // 6) Формування події
        $event = [
            'event_name'       => $name,
            'event_time'       => (int)($req->input('event_time') ?: time()),
            'action_source'    => 'website',
            'event_source_url' => $this->eventSourceUrl($req),
            'event_id'         => (string)($req->input('event_id') ?: $this->makeEventId($name)),
            'user_data'        => $ud, // не змінюємо
        ];
        if (!empty($custom)) {
            $event['custom_data'] = $custom;
        }
    
        // 7) test_event_code (якщо є)
        $testCode = $req->input('test_event_code', $s->capi_test_code ?? null);
    
        // 8) Надсилання до Meta
        try {
            $capi = new MetaCapi($pixelId, $token, (string)($s->capi_api_version ?? 'v20.0'));
            $resp = $capi->send([$event], $testCode);
        } catch (\Throwable $e) {
            return response()->json([
                'ok'    => false,
                'error' => 'capi_exception',
                'msg'   => $e->getMessage(),
            ], 502);
        }
    
        $body = $resp->json();
    
        if (!$resp->ok() || (is_array($body) && isset($body['error']))) {
            return response()->json([
                'ok'     => false,
                'error'  => 'capi_request_failed',
                'status' => $resp->status(),
                'body'   => $body,
            ], 502);
        }
    
        if (is_array($body) && array_key_exists('events_received', $body) && (int)$body['events_received'] < 1) {
            return response()->json([
                'ok'     => false,
                'error'  => 'events_not_received',
                'status' => $resp->status(),
                'body'   => $body,
            ], 502);
        }
    
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
        if (preg_match('/[?&]fbclid=([^&#]+)/', $url, $m)) {
            return $m[1]; // тут воно береться "як є"
        }
        return null;
    }

    /**
     * Формує user_data для Meta CAPI.
     *
     * - IP / User-Agent завжди (IP береться з CF-Connecting-IP / X-Forwarded-For, інакше Request::ip()).
     * - _fbc: із cookie as-is або будується з fbclid у URL (через pickFbc()), без змін регістру/декодування.
     * - _fbp: із cookie as-is.
     * - external_id: не хешується (рекомендація Meta), обрізається до 128 символів.
     * - PII (email, phone, fn, ln): тільки SHA-256 після нормалізації.
     * - Якщо немає валідного _fbc → повертає [], а handleEvent має пропустити подію (202).
     */

    // ─────────────────────────────────────────────
    // Формування user_data для Meta CAPI
    // ─────────────────────────────────────────────
    private function userData(Request $req): array
    {
        // IP + User-Agent — базові ключі для матчингу
        $data = [
            'client_ip_address' => $this->realIp($req),
            'client_user_agent' => (string) $req->userAgent(),
        ];

        // _fbc — додаємо тільки якщо є валідний (але НЕ відкидаємо подію, якщо його нема)
        if ($fbc = $this->pickFbc($req)) {
            $data['fbc'] = $fbc;
        }

        // _fbp — віддаємо "як є" із cookie (ніяких trim/хешів)
        $fbp = $req->cookie('_fbp');
        if (is_string($fbp) && $fbp !== '') {
            $data['fbp'] = $fbp;
        }

        // external_id — необов’язковий; якщо є, шлемо таким, як вирішили бізнес-правилами
        // (можна raw UUID, а можна попередньо захешувати — головне: однаково у Browser і Server)
        $ext = $req->cookie('_extid');
        if (is_string($ext) && ($ext = trim($ext)) !== '') {
            $data['external_id'] = mb_substr($ext, 0, 128);
        } elseif ($req->filled('external_id')) {
            $extBody = trim((string) $req->input('external_id'));
            if ($extBody !== '') {
                $data['external_id'] = mb_substr($extBody, 0, 128);
            }
        }

        // PII — ТІЛЬКИ SHA-256 після нормалізації значень
        if ($em = $this->normEmail($req->input('email'))) {
            $data['em'] = hash('sha256', $em);
        }
        if ($pn = $this->normPhone($req->input('phone'))) {
            $data['ph'] = hash('sha256', $pn);
        }
        if ($fn = $this->normName($req->input('first_name') ?? $req->input('fn'))) {
            $data['fn'] = hash('sha256', $fn);
        }
        if ($ln = $this->normName($req->input('last_name') ?? $req->input('ln'))) {
            $data['ln'] = hash('sha256', $ln);
        }

        return $data;
    }


    private function realIp(Request $req): string
    {
        foreach (['CF-Connecting-IP', 'X-Forwarded-For'] as $h) {
            $v = (string) $req->headers->get($h, '');
            if ($v !== '') {
                return trim(explode(',', $v)[0]);
            }
        }
        return (string) $req->ip();
    }

    
    // ─────────────────────────────────────────────
    // Валідний fallback для _fbc (без змін cookie)
    // ─────────────────────────────────────────────
    private function pickFbc(Request $req): ?string
    {
        // 1) Якщо є cookie _fbc — повертаємо як є (жодних trim/strtolower/urldecode)
        $cookie = $req->cookie('_fbc');
        if (is_string($cookie) && $cookie !== '') {
            // Якщо це явний плейсхолдер типу "...fbclid" — вважаємо невалідним
            if (preg_match('/\.fbclid$/', $cookie)) {
                return null;
            }
            // Перевіряємо базовий формат: fb.<версія>.<13-значні мс>.<щось>
            if (preg_match('/^fb\.\d\.\d{13}\..+$/', $cookie)) {
                return $cookie; // повертаємо 1:1, інакше Meta вважатиме "изменённое значение"
            }
            // Дивний формат? Краще нічого не відправляти, ніж псувати діагностику Meta
            return null;
        }

        // 2) Cookie немає — пробуємо зібрати _fbc з fbclid параметра URL
        //    ВАЖЛИВО: fbclid беремо сирим, без зміни регістру/декодування
        $srcUrl = $this->eventSourceUrl($req);
        $fbclid = $this->parseFbclid($srcUrl) ?? $req->query('fbclid');

        if (is_string($fbclid) && $fbclid !== '' && $fbclid !== 'fbclid') {
            // Префікс має бути fb.2., а мітка часу — у мілісекундах (13 цифр)
            $ms = now()->valueOf(); // 13-значні мс
            return 'fb.2.' . $ms . '.' . $fbclid;
        }

        // 3) Нема ні cookie, ні fbclid — просто не додаємо fbc (це ОК)
        return null;
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
