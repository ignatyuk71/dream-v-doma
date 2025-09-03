@php
  use Illuminate\Support\Facades\DB;

  // Налаштування трекінгу (може бути null)
  $t = DB::table('tracking_settings')->first();

  // Pixel має бути увімкнено, заданий pixel_id і не адмін-URL
  $pixelOk = $t
    && (int)($t?->pixel_enabled ?? 0) === 1
    && !empty($t?->pixel_id)
    && !((int)($t?->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  // Окремий тумблер для ViewContent
  $allowVC = $pixelOk && (int)($t?->send_view_content ?? 1) === 1;

  // Локаль і перекладена назва товару
  $locale = app()->getLocale() ?: 'uk';
  $tr = ($product->translations ?? collect())
        ->firstWhere('locale', $locale)
        ?? ($product->translations ?? collect())->firstWhere('locale', 'uk')
        ?? ($product->translations ?? collect())->firstWhere('locale', 'ru')
        ?? null;

  $translatedName = $tr->name ?? '';
@endphp

@if ($allowVC && isset($product))
<script>
(function () {
  // Подія вимкнена через глобальні прапорці?
  if (window._mpFlags && window._mpFlags.vc === false) return;

  // content_id: на сторінці продукту — SKU (або fallback на ID)
  var pid = String(@json($product->sku ?? $product->id ?? ''));
  if (!pid) return;

  // Назва для content_name (та сама піде і в CAPI, і в Pixel)
  var name     = @json($translatedName);
  var rawPrice = @json($product->price ?? 0);
  var currency = window.metaPixelCurrency || 'UAH';

  // Нормалізація ціни → число з 2-ма знаками
  var price = (function (p) {
    var s = String(p).replace(',', '.').replace(/[^\d.]/g, '');
    var n = parseFloat(s);
    return Number.isFinite(n) ? Number(n.toFixed(2)) : 0;
  })(rawPrice);

  // Новий формат contents[] (Meta рекомендує): [{id, quantity, item_price}]
  var contents = [{ id: pid, quantity: 1, item_price: price }];

  // Спільний event_id для дедуплікації між Pixel і CAPI
  var vcId = window._mpVCId || (window._mpVCId = (
    typeof window._mpGenEventId === 'function'
      ? window._mpGenEventId('vc')
      : ('vc-' + Math.random().toString(16).slice(2) + '-' + Date.now())
  ));

  /* ==================== 1) БРАУЗЕРНИЙ PIXEL ==================== */
  (function sendPixel(attempt){
    attempt = attempt || 0;
    if (typeof window.fbq !== 'function') {
      if (attempt > 120) return; // ~10 секунд очікування fbq
      return setTimeout(function(){ sendPixel(attempt+1) }, 80);
    }
    window.fbq('track', 'ViewContent', {
      content_ids: [pid],
      content_type: 'product',
      contents: contents,
      content_name: name,
      value: price,
      currency: currency
    }, { eventID: vcId });
  })();

  /* ==================== 2) СЕРВЕРНИЙ CAPI ==================== */
  try {
    // Хелпери cookie/декодування (fbp/fbc часто URL-encoded)
    var getCookie = window._mpGetCookie || function(n){
      return document.cookie.split('; ')
        .find(function(r){ return r.indexOf(n + '=') === 0 })?.split('=')[1] || null;
    };
    var safeDecode = function(c){ try { return c ? decodeURIComponent(c) : null } catch(_) { return c } };

    var fbp = safeDecode(getCookie('_fbp'));
    var fbc = safeDecode(getCookie('_fbc'));

    // Те саме custom_data, що й у браузерній події
    var bodyObj = {
      event_id: vcId,
      event_time: Math.floor(Date.now()/1000),
      event_source_url: window.location.href,

      content_name: name,
      content_type: 'product',
      content_ids: [pid],
      contents: contents,
      value: price,
      currency: currency,

      // user_data (IP/UA + хешовані PII) додається у бек-контролері
      fbp: fbp,
      fbc: fbc
    };
    if (window._mpTestCode) bodyObj.test_event_code = window._mpTestCode;

    var body = JSON.stringify(bodyObj);

    // iOS → fetch keepalive; інше → sendBeacon або fetch
    var ua = navigator.userAgent || '';
    var isiOS = /iPad|iPhone|iPod/i.test(ua) || (/Macintosh/i.test(ua) && 'ontouchend' in document);

    if (isiOS) {
      fetch('/api/track/vc', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        keepalive: true,
        body
      });
    } else if (navigator.sendBeacon) {
      navigator.sendBeacon('/api/track/vc', new Blob([body], {type:'application/json'}));
    } else {
      fetch('/api/track/vc', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        keepalive: true,
        body
      });
    }
  } catch (_) {}
})();
</script>
@endif
