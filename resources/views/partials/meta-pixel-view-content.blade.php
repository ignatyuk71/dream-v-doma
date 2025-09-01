@php
  use Illuminate\Support\Facades\DB;

  $t = DB::table('tracking_settings')->first();

  $pixelOk = $t
    && (int)($t->pixel_enabled ?? 0) === 1
    && !empty($t->pixel_id)
    && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  $allowVC = $pixelOk && (int)($t->send_view_content ?? 1) === 1;

  // 🔤 Витягуємо назву з перекладів
  $locale = app()->getLocale() ?: 'uk';
  $tr = ($product->translations ?? collect())
        ->firstWhere('locale', $locale)
        ?? ($product->translations ?? collect())->firstWhere('locale', 'uk')
        ?? ($product->translations ?? collect())->firstWhere('locale', 'ru')
        ?? null;

  $translatedName = $tr->name ?? '';
  // якщо треба буде слаг: $translatedSlug = $tr->slug ?? '';
@endphp

@if ($allowVC && isset($product))
<script>
(function () {
  if (window._mpFlags && window._mpFlags.vc === false) return;

  var pid = String(@json($product->sku ?? $product->id ?? ''));
  if (!pid) return;

  var name     = @json($translatedName);    // ✅ назва з product_translations
  var rawPrice = @json($product->price ?? 0);
  var currency = window.metaPixelCurrency || 'UAH';

  var price = (function (p) {
    var s = String(p).replace(',', '.').replace(/[^\d.]/g, '');
    var n = parseFloat(s);
    return Number.isFinite(n) ? Number(n.toFixed(2)) : 0;
  })(rawPrice);

  var contents = [{ id: pid, quantity: 1, item_price: price }];

  // один і той самий ID для дедупу
  var vcId = window._mpVCId || (window._mpVCId = (typeof window._mpGenEventId === 'function'
              ? window._mpGenEventId('vc')
              : ('vc-' + Math.random().toString(16).slice(2) + '-' + Date.now())));

  // ---- БРАУЗЕР ----
  (function sendPixel() {
    if (typeof window.fbq !== 'function') { setTimeout(sendPixel, 80); return; }
    window.fbq('track', 'ViewContent', {
      content_ids: [pid],
      content_type: 'product',
      contents: contents,
      content_name: name,     // ✅ така сама назва, як у серверній
      value: price,
      currency: currency
    }, { eventID: vcId });
  })();

  // ---- CAPI ----
  try {
    var getCookie = window._mpGetCookie || function(n){
      return document.cookie.split('; ').find(function(r){ return r.indexOf(n + '=') === 0 })?.split('=')[1] || null;
    };
    var safeDecode = function(c){ try { return c ? decodeURIComponent(c) : null } catch(_) { return c } };

    var fbp = safeDecode(getCookie('_fbp'));
    var fbc = safeDecode(getCookie('_fbc'));

    var bodyObj = {
      event_id: vcId,
      event_time: Math.floor(Date.now()/1000),
      event_source_url: window.location.href,
      content_name: name,     // ✅ синхронізовано з браузером
      content_type: 'product',
      content_ids: [pid],
      contents: contents,
      value: price,
      currency: currency,
      fbp: fbp,
      fbc: fbc
    };
    if (window._mpTestCode) bodyObj.test_event_code = window._mpTestCode;

    var body = JSON.stringify(bodyObj);

    // iOS → fetch keepalive; інше → beacon або fetch
    var ua = navigator.userAgent || '';
    var isiOS = /iPad|iPhone|iPod/i.test(ua) || (/Macintosh/i.test(ua) && 'ontouchend' in document);

    if (isiOS) {
      fetch('/api/track/vc', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'same-origin', keepalive:true, body });
    } else if (navigator.sendBeacon) {
      navigator.sendBeacon('/api/track/vc', new Blob([body], {type:'application/json'}));
    } else {
      fetch('/api/track/vc', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'same-origin', keepalive:true, body });
    }
  } catch (_) {}
})();
</script>
@endif
