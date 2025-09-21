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
  // Глобальне вимкнення події?
  if (window._mpFlags && window._mpFlags.vc === false) return;

  // --- helpers (READ-ONLY) ---
  function getCookie(name){
    var m = document.cookie.match(new RegExp('(?:^|;\\s*)' + name + '=([^;]+)'));
    return m ? decodeURIComponent(m[1]) : null;
  }
  function getParam(name){
    var m = location.search.match(new RegExp('[?&]'+name+'=([^&]+)'));
    return m ? m[1] : null;
  }
  function isFacebookTraffic(){
    return !!(getCookie('_fbc') || getCookie('_fbp') || getParam('fbclid'));
  }
  function genEventId(name){
    try {
      var a = new Uint8Array(6);
      (window.crypto||window.msCrypto).getRandomValues(a);
      var hex = Array.from(a).map(function(b){return b.toString(16).padStart(2,'0')}).join('');
      return (name||'ev') + '-' + hex + '-' + Math.floor(Date.now()/1000);
    } catch(_){
      return (name||'ev') + '-' + Math.random().toString(16).slice(2) + '-' + Math.floor(Date.now()/1000);
    }
  }

  // FB-трафік обов'язковий
  if (!isFacebookTraffic()) return;

  // content_id: SKU або ID
  var pid = String(@json($product->sku ?? $product->id ?? ''));
  if (!pid) return;

  var name     = @json($translatedName);
  var rawPrice = @json($product->price ?? 0);
  var currency = window.metaPixelCurrency || 'UAH';

  // нормалізація ціни
  var price = (function (p) {
    var s = String(p).replace(',', '.').replace(/[^\d.]/g, '');
    var n = parseFloat(s);
    return Number.isFinite(n) ? Number(n.toFixed(2)) : 0;
  })(rawPrice);

  var contents = [{ id: pid, quantity: 1, item_price: price }];

  // спільний event_id
  var vcId = window._mpVCId || (window._mpVCId = (
    typeof window._mpGenEventId === 'function' ? window._mpGenEventId('vc') : genEventId('vc')
  ));

  /* ==================== 1) БРАУЗЕРНИЙ PIXEL (ЛИШЕ FB-трафік) ==================== */
  (function sendPixel(attempt){
    attempt = attempt || 0;
    if (typeof window.fbq !== 'function') {
      if (attempt > 120) return; // ~10 секунд
      return setTimeout(function(){ sendPixel(attempt+1) }, 80);
    }
    try {
      window.fbq('track', 'ViewContent', {
        content_ids: [pid],
        content_type: 'product',
        contents: contents,
        content_name: name,
        value: price,
        currency: currency
      }, { eventID: vcId });
    } catch(_) {}
  })();

  /* ==================== 2) СЕРВЕРНИЙ CAPI (ЛИШЕ FB-трафік) ==================== */
  try {
    var fbp = getCookie('_fbp') || null; // лише читаємо
    var fbc = getCookie('_fbc') || null; // лише читаємо

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

      fbp: fbp,   // як є або null
      fbc: fbc    // як є або null
      // PII (email/phone ...) хешується на бекенді, як у тебе
    };
    if (window._mpTestCode) bodyObj.test_event_code = window._mpTestCode;

    var body = JSON.stringify(bodyObj);

    if (navigator.sendBeacon) {
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
