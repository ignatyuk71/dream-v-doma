@php
  use Illuminate\Support\Facades\DB;

  $t = DB::table('tracking_settings')->first();

  $pixelOk = $t
    && (int)($t->pixel_enabled ?? 0) === 1
    && !empty($t->pixel_id)
    && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  $allowIC = $pixelOk && (int)($t->send_initiate_checkout ?? 1) === 1;

  // CAPI лише якщо ввімкнено і є токен
  $capiOn = $allowIC && $t && (int)($t->capi_enabled ?? 0) === 1 && !empty($t->capi_token);
@endphp

@if ($allowIC)
<script>
(function(){
  if (window.mpTrackIC) return;              // не переоголошувати
  if (window._mpFlags && window._mpFlags.ic === false) return;

  // ── helpers ─────────────────────────────────────────
  function num(v){
    var s = String(v ?? 0).replace(',', '.').replace(/[^\d.\-]/g,'');
    var n = parseFloat(s);
    return Number.isFinite(n) ? Number(n.toFixed(2)) : 0;
  }
  // анти дабл-клік для IC
  var _lastIC = 0;
  function notTooSoon(ms){
    var now = Date.now();
    if (now - _lastIC < ms) return false;
    _lastIC = now; return true;
  }
  function genEventId(name){
    try {
      var a = new Uint8Array(6);
      (window.crypto || window.msCrypto).getRandomValues(a);
      var hex = Array.from(a).map(function(b){ return b.toString(16).padStart(2,'0') }).join('');
      return name + '-' + hex + '-' + Math.floor(Date.now()/1000);
    } catch {
      return name + '-' + Math.random().toString(16).slice(2) + '-' + Math.floor(Date.now()/1000);
    }
  }

  /**
   * Викликати, коли юзер реально заходить у чекаут:
   * window.mpTrackIC({
   *   items: [
   *     { variant_sku:'PRD-001', price:799.00, quantity:1, name:'Назва 1' },
   *     { variant_sku:'PRD-002', price:399.00, quantity:2, name:'Назва 2' }
   *   ],
   *   currency: 'UAH' // опц.
   * })
   */
  window.mpTrackIC = function(opts){
    try{
      if (!opts || !Array.isArray(opts.items) || !opts.items.length) return;
      if (!notTooSoon(800)) return; // 0.8s анти-дубль

      // build contents / ids / totals
      var contents = [], content_ids = [], total = 0, firstName = '';
      for (var i=0; i<opts.items.length; i++){
        var it = opts.items[i] || {};
        var id = (it.variant_sku ?? it.sku ?? it.id ?? '').toString().trim();
        if (!id) continue;

        var qty = Number(it.quantity || 1); if (!Number.isFinite(qty) || qty <= 0) qty = 1;
        var price = num(it.price);

        contents.push({ id:id, quantity:qty, item_price:price });
        content_ids.push(id);
        total += qty * price;

        if (!firstName && typeof it.name === 'string' && it.name.trim()) {
          firstName = it.name.trim();
        }
      }
      if (!contents.length) return;

      var currency = (opts.currency || window.metaPixelCurrency || 'UAH').toString().trim().toUpperCase();
      var value    = num(total);
      var numItems = contents.reduce(function(s,c){ return s + (Number(c.quantity)||0) }, 0);
      var icId     = genEventId('ic');

      // 1) Pixel — БЕЗ затримки (чекаємо fbq максимум ~1s)
      (function sendPixel(i){
        i = i || 0;
        if (typeof window.fbq !== 'function') {
          if (i >= 15) return; // ~1s (12 * 80ms)
          return setTimeout(function(){ sendPixel(i+1); }, 80);
        }
        try {
          var payload = {
            content_ids: content_ids,
            content_type: 'product',
            contents: contents,
            num_items: numItems,
            value: value,
            currency: currency
          };
          if (firstName) payload.content_name = firstName;

          fbq('track', 'InitiateCheckout', payload, { eventID: icId });
        } catch(_) {}
      })();

      // 2) CAPI — ЗАТРИМКА 1.0–1.5s (той самий event_id)
      @if ($capiOn)
      (function(){
        var DELAY_MS = 1000; // 1000–1500 мс
        setTimeout(function(){
          var body = JSON.stringify({
            event_id: icId,
            page_url: location.href,   // консистентно з pv/vc/atc
            currency: currency,
            contents: contents,
            num_items: numItems,
            name: firstName || null,
            value: value
          });

          var sent = false;
          if (navigator.sendBeacon) {
            try { sent = navigator.sendBeacon('/api/track/ic', new Blob([body], {type:'application/json'})); } catch(_){}
          }
          if (!sent) {
            try {
              fetch('/api/track/ic', {
                method: 'POST',
                keepalive: true,
                headers: { 'Content-Type': 'application/json' },
                body
              }).catch(function(){});
            } catch(_){}
          }
        }, DELAY_MS);
      })();
      @endif

      // (опц.) GA4 begin_checkout:
      // if (typeof window.ga4BeginCheckout === 'function') {
      //   window.ga4BeginCheckout({ contents, currency, value, num_items: numItems, name: firstName || '' });
      // }

    } catch(e){
      console.warn('[IC] mpTrackIC exception', e);
    }
  };
})();
</script>
@endif
