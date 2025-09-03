@php
  use Illuminate\Support\Facades\DB;

  $t = DB::table('tracking_settings')->first();

  $pixelOk = $t
    && (int)($t->pixel_enabled ?? 0) === 1
    && !empty($t->pixel_id)
    && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  // окремий тумблер для IC (додай поле send_initiate_checkout в tracking_settings, якщо ще нема)
  $allowIC = $pixelOk && (int)($t->send_initiate_checkout ?? 1) === 1;
@endphp

@if ($allowIC)
<script>
(function(){
  if (window.mpTrackIC) return;
  if (!window._mpFlags || window._mpFlags.ic === false) return;

  /* ===== утиліти (як у ATC) ===== */
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

  /**
   * window.mpTrackIC({
   *   items: [{ variant_sku, price, quantity, name? }, ...],
   *   currency?: 'UAH'
   * })
   */
  window.mpTrackIC = function(opts){
    try{
      if (!opts || !Array.isArray(opts.items) || !opts.items.length) {
        console.warn('[IC] no items passed');
        return;
      }

      // будуємо contents і content_ids ТІЛЬКИ з variant_sku
      var contents = [];
      var content_ids = [];
      var total = 0;

      for (var i=0; i<opts.items.length; i++){
        var it = opts.items[i] || {};
        var id = (it.variant_sku ?? '').toString().trim();
        if (!id) {
          console.warn('[IC] skip item without variant_sku', it);
          continue;
        }
        var qty = Number(it.quantity || 1); if (!Number.isFinite(qty) || qty <= 0) qty = 1;
        var price = num(it.price);

        contents.push({ id: id, quantity: qty, item_price: price });
        content_ids.push(id);
        total += qty * price;
      }

      if (!contents.length) {
        console.warn('[IC] nothing to send (no valid variant_sku)');
        return;
      }

      var currency = opts.currency || (window.metaPixelCurrency || 'UAH');
      var value = num(total);
      var icId  = genEventId('ic'); // event_id

      // ---- Pixel: InitiateCheckout (з коротким ретраєм очікування fbq)
      (function sendPixel(attempt){
        attempt = attempt || 0;
        if (typeof window.fbq !== 'function') {
          if (attempt > 120) return; // ~10 сек
          return setTimeout(function(){ sendPixel(attempt+1) }, 80);
        }
        fbq('track', 'InitiateCheckout', {
          content_ids: content_ids,
          content_type: 'product',
          contents: contents,
          num_items: contents.reduce(function(s,c){ return s + (Number(c.quantity)||0) }, 0),
          value: value,
          currency: currency
        }, { eventID: icId });
      })();

      // ---- CAPI дубль з тим самим event_id
      var fbp = safeDecode(getCookie('_fbp'));
      var fbc = safeDecode(getCookie('_fbc'));
      var bodyObj = {
        event_id: icId,
        event_time: Math.floor(Date.now()/1000),
        event_source_url: window.location.href,
        content_type: 'product',
        content_ids: content_ids,
        contents: contents,
        num_items: contents.reduce(function(s,c){ return s + (Number(c.quantity)||0) }, 0),
        value: value,
        currency: currency,
        fbp: fbp, fbc: fbc
      };
      if (window._mpTestCode) bodyObj.test_event_code = window._mpTestCode;

      var body = JSON.stringify(bodyObj);

      if (navigator.sendBeacon) {
        navigator.sendBeacon('/api/track/ic', new Blob([body], {type:'application/json'}));
      } else {
        fetch('/api/track/ic', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'same-origin',
          keepalive: true,
          body
        });
      }
    } catch(e){
      console.warn('[IC] mpTrackIC exception', e);
    }
  };
})();
</script>
@endif
