<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\MetaCapi;

class TrackController extends Controller
{
    /* ===================== PUBLIC ENDPOINTS ===================== */

    // PageView — без custom_data
// PageView — без custom_data
    public function pv(Request $request)
    {
        return $this->handleEvent('PageView', $request, fn() => [], flag: 'send_page_view');
    }


    // ViewContent
    public function vc(Request $request)
    {
        return $this->handleEvent('ViewContent', $request, function () use ($request) {
            // якщо прийшло contents[] — беремо його
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

            // бек-сов сумісність: id / price / quantity
            $pid      = (string)($request->input('id') ?? $request->input('sku') ?? '');
            $price    = $this->num($request->input('price', $request->input('item_price', $request->input('value', 0))));
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
            if ($request->filled('name')) $data['content_name'] = $request->input('name');

            return $data;
        }, flag: 'send_view_content');
    }

    // AddToCart
    public function atc(Request $request)
    {
        return $this->handleEvent('AddToCart', $request, function () use ($request) {
            // новий формат: contents[] + (опційний) value
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

            // бек-сов сумісність: id/quantity/price
            $pid      = (string)($request->input('id') ?? $request->input('sku') ?? '');
            $qty      = (int)$request->input('quantity', 1);
            $price    = $this->num($request->input('price', $request->input('item_price', 0)));
            $currency = $request->input('currency', $this->currency());

            $value = $this->num($qty * $price);

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
        }, flag: 'send_add_to_cart');
    }

    // InitiateCheckout
    public function ic(Request $request)
    {
        return $this->handleEvent('InitiateCheckout', $request, function () use ($request) {
            // підтримуємо і contents[], і наш попередній items[]
            $contents = $this->contentsFromRequest($request);
            if (empty($contents)) {
                $items = (array)$request->input('items', []);
                foreach ($items as $i) {
                    $id  = (string)($i['sku'] ?? $i['id'] ?? '');
                    if ($id === '') continue;
                    $qty = (int)($i['quantity'] ?? 1);
                    $pr  = $this->num($i['price'] ?? $i['item_price'] ?? 0);
                    $contents[] = ['id' => $id, 'quantity' => $qty, 'item_price' => $pr];
                }
            }

            $value = $request->filled('value')
                ? $this->num($request->input('value'))
                : $this->calcValue($contents);

            return [
                'content_type' => 'product',
                'contents'     => $contents,
                'num_items'    => array_reduce($contents, fn($s, $c) => $s + (int)$c['quantity'], 0),
                'value'        => $value,
                'currency'     => $request->input('currency', $this->currency()),
            ];
        }, flag: 'send_initiate_checkout');
    }

    // Lead
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

    /* ===================== CORE HANDLER ===================== */

    private function handleEvent(string $name, Request $req, \Closure $buildCustomData, string $flag)
    {
        $s = $this->settings();
    
        if (!$s || (int)($s->capi_enabled ?? 0) !== 1) {
            return response()->json(['ok' => true, 'skipped' => 'capi_disabled'], 202);
        }
        if (!$this->flagEnabled($s, $flag)) {
            return response()->json(['ok' => true, 'skipped' => "flag_{$flag}_disabled"], 202);
        }
    
        if ((int)($s->exclude_admin ?? 1) === 1) {
            $url = $this->eventSourceUrl($req);
            if ($this->looksLikeAdmin($url)) {
                return response()->json(['ok' => true, 'skipped' => 'admin_excluded'], 202);
            }
        }
    
        $pixelId = (string)($s->pixel_id ?? '');
        $token   = (string)($s->capi_token ?? '');
        if ($pixelId === '' || $token === '') {
            return response()->json(['ok' => false, 'error' => 'missing_pixel_or_token'], 422);
        }
    
        $custom = $buildCustomData();
    
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
    
        $capi = new MetaCapi($pixelId, $token, (string)($s->capi_api_version ?? 'v20.0'));
        $resp = $capi->send([$event], $s->capi_test_code ?? null);
    
        if (!$resp->ok()) {
            return response()->json([
                'ok'     => false,
                'error'  => 'capi_request_failed',
                'status' => $resp->status(),
                'body'   => $resp->json(),
            ], 502);
        }
    
        return response()->json(['ok' => true, 'event' => $name], 200);
    }
    

    /* ===================== HELPERS ===================== */

    private function settings(): ?object
    {
        return DB::table('tracking_settings')->first();
    }

    private function currency(): string
    {
        $s = $this->settings();
        return $s && !empty($s->default_currency) ? (string)$s->default_currency : 'UAH';
    }

    private function flagEnabled(object $s, string $flag): bool
    {
        return isset($s->{$flag}) ? (int)$s->{$flag} === 1 : false;
    }

    private function eventSourceUrl(Request $req): string
    {
        if ($req->filled('event_source_url')) return (string)$req->input('event_source_url');
        if ($req->filled('url'))              return (string)$req->input('url');

        $ref = (string)$req->headers->get('referer', '');
        return $ref !== '' ? $ref : url()->current();
    }

    private function looksLikeAdmin(string $url): bool
    {
        return str_contains($url, '/admin') || str_contains($url, '/dashboard');
    }

    private function num($v): float
    {
        // спочатку коми -> крапки, потім чистимо
        $s = str_replace(',', '.', (string)$v);
        $clean = preg_replace('/[^\d\.\-]/', '', $s);
        $n = (float)$clean;
        return round($n, 2);
    }

    private function sha256(?string $v): ?string
    {
        if (!$v) return null;
        $v = trim(mb_strtolower($v));
        return $v === '' ? null : hash('sha256', $v);
    }

    private function normPhone(?string $p): ?string
    {
        if (!$p) return null;
        $digits = preg_replace('/\D+/', '', $p);
        return $digits === '' ? null : $digits; // E.164 без "+"
    }

    private function parseFbclid(?string $url): ?string
    {
        if (!$url) return null;
        $parts = parse_url($url);
        if (empty($parts['query'])) return null;
        parse_str($parts['query'], $q);
        return $q['fbclid'] ?? null;
    }

    private function userData(Request $req): array
    {
        $email = $req->input('email');
        $phone = $req->input('phone');
        $fn    = $req->input('first_name') ?? $req->input('fn');
        $ln    = $req->input('last_name')  ?? $req->input('ln');

        // з фронта мають пріоритет
        $fbp = $req->input('fbp') ?? $req->cookie('_fbp');
        $fbc = $req->input('fbc') ?? $req->cookie('_fbc');

        if (!$fbc) {
            $fbclid = $this->parseFbclid($this->eventSourceUrl($req));
            if ($fbclid) $fbc = 'fb.1.' . time() . '.' . $fbclid;
        }

        $data = [
            'client_ip_address' => $this->clientIp($req),
            'client_user_agent' => (string)$req->userAgent(),
        ];

        if ($this->sha256($email))          $data['em']  = $this->sha256($email);
        if ($this->normPhone($phone))       $data['ph']  = $this->sha256($this->normPhone($phone));
        if ($this->sha256($fn))             $data['fn']  = $this->sha256($fn);
        if ($this->sha256($ln))             $data['ln']  = $this->sha256($ln);
        if ($fbc)                           $data['fbc'] = $fbc;
        if ($fbp)                           $data['fbp'] = $fbp;
        if ($req->filled('external_id'))    $data['external_id'] = $this->sha256((string)$req->input('external_id'));

        return $data;
    }

    private function makeEventId(string $name): string
    {
        return $name . '-' . bin2hex(random_bytes(6)) . '-' . time();
    }

    /** Приймає contents[] з тіла (і нормалізує), або порожній масив */
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

    /** Підсумкова вартість по contents[] */
    private function calcValue(array $contents): float
    {
        $sum = 0.0;
        foreach ($contents as $c) {
            $sum += (int)$c['quantity'] * (float)$c['item_price'];
        }
        return $this->num($sum);
    }

    private function clientIp(Request $req): string
    {
        if ($v = $req->headers->get('CF-Connecting-IP')) return $v; // Cloudflare
        if ($v = $req->headers->get('X-Real-IP'))        return $v; // Nginx
        if ($v = $req->headers->get('X-Forwarded-For'))  return trim(explode(',', $v)[0]); // перший клієнтський
        return $req->ip();
    }

}
