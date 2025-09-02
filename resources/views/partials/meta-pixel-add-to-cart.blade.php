@php
  use Illuminate\Support\Facades\DB;

  $t = DB::table('tracking_settings')->first();

  $pixelOk = $t
    && (int)($t->pixel_enabled ?? 0) === 1
    && !empty($t->pixel_id)
    && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  $allowATC = $pixelOk && (int)($t->send_add_to_cart ?? 1) === 1;
@endphp

@if ($allowATC)
<script>
(function(){
  // не перевизначати при повторних завантаженнях
  if (window.mpTrackATC) return;
  if (!window._mpFlags || window._mpFlags.atc === false) return;

  /* ===== утиліти ===== */
  function num(v){
    var s = String(v ?? 0).replace(',', '.').replace(/[^\d.\-]/g,'');
    var n = parseFloat(s);
    return Number.isFinite(n) ? Number(n.toFixed(2)) : 0;
  }
  function getCookie(n){
    return document.cookie.split('; ')
      .find(function(r){ return r.indexOf(n + '=') === 0 })?.split('=')[1] || null;
  }
  function safeDecode(c){ try { return c ? decodeURIComponent(c) : null } catch(_) { return c } }
  function genEventId(name){
    if (typeof window._mpGenEventId === 'function') return window._mpGenEventId(name);
    try {
      var a = new Uint8Array(6);
      (window.crypto || window.msCrypto).getRandomValues(a);
      var hex = Array.from(a).map(function(b){ return b.toString(16).padStart(2,'0') }).join('');
      return name + '-' + hex + '-' + Math.floor(Date.now()/1000);
    } catch (_e) {
      return name + '-' + Math.random().toString(16).slice(2) + '-' + Math.floor(Date.now()/1000);
    }
  }

  /* ===== головна функція ===== */
  // window.mpTrackATC({ variant_sku, price, quantity?, name?, currency? })
  window.mpTrackATC = function(opts){
    try{
      if (!opts) return;

      // тільки variant_sku
      var pidRaw = (opts.variant_sku ?? '').toString().trim();
      if (!pidRaw) {
        console.warn('[ATC] variant_sku missing — tracking skipped!', opts);
        window.showGlobalToast?.('⚠️ Відсутній артикул варіанта (variant_sku). Подія трекінгу пропущена.', 'warning');
        return;
      }
      var pid = pidRaw;

      var qty = Number(opts.quantity || 1);
      if (!Number.isFinite(qty) || qty <= 0) qty = 1;

      var price = num(opts.price);
      var name  = typeof opts.name === 'string' ? opts.name : '';
      var currency = opts.currency || (window.metaPixelCurrency || 'UAH');

      var contents = [{ id: pid, quantity: qty, item_price: price }];
      var value    = num(qty * price);
      var atcId    = genEventId('atc');

      // ---- браузерний AddToCart (з обмеженим ретраєм)
      (function sendPixel(attempt){
        attempt = attempt || 0;
        if (typeof window.fbq !== 'function') {
          if (attempt > 120) return; // ~10 секунд @ ~80ms
          return setTimeout(function(){ sendPixel(attempt+1) }, 80);
        }
        fbq('track', 'AddToCart', {
          content_ids: [pid],
          content_type: 'product',
          contents: contents,
          content_name: name,
          value: value,
          currency: currency
        }, { eventID: atcId });
      })();

      // ---- CAPI AddToCart
      var fbp = safeDecode(getCookie('_fbp'));
      var fbc = safeDecode(getCookie('_fbc'));

      var bodyObj = {
        event_id: atcId,
        event_time: Math.floor(Date.now()/1000),
        event_source_url: window.location.href,
        content_type: 'product',
        content_ids: [pid],
        contents: contents,
        content_name: name,
        value: value,
        currency: currency,
        fbp: fbp, fbc: fbc
      };
      if (window._mpTestCode) bodyObj.test_event_code = window._mpTestCode;

      var body = JSON.stringify(bodyObj);

      var ua = navigator.userAgent || '';
      var isiOS = /iPad|iPhone|iPod/i.test(ua) || (/Macintosh/i.test(ua) && 'ontouchend' in document);

      if (isiOS) {
        fetch('/api/track/atc', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'same-origin',
          keepalive: true,
          body
        }).catch(function(){
          setTimeout(function(){
            fetch('/api/track/atc', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              credentials: 'same-origin',
              keepalive: true,
              body
            });
          }, 250);
        });
      } else if (navigator.sendBeacon) {
        navigator.sendBeacon('/api/track/atc', new Blob([body], {type:'application/json'}));
      } else {
        fetch('/api/track/atc', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'same-origin',
          keepalive: true,
          body
        });
      }
    } catch(e){
      console.warn('[ATC] mpTrackATC exception', e);
    }
  };
})();
</script>
@endif
