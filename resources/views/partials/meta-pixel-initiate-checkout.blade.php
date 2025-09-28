@php
  use Illuminate\Support\Facades\DB;

  $t = DB::table('tracking_settings')->first();

  $pixelOk = $t
    && (int)($t->pixel_enabled ?? 0) === 1
    && !empty($t->pixel_id)
    && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  $allowIC = $pixelOk && (int)($t->send_initiate_checkout ?? 1) === 1;
@endphp

@if ($allowIC)
<script>
(function(){
  if (window.mpTrackIC) return; // не переоголошувати
  if (window._mpFlags && window._mpFlags.ic === false) return; // вимикач, якщо явно false

  // ---- helpers ----
  function num(v){
    var s = String(v ?? 0).replace(',', '.').replace(/[^\d.\-]/g,'');
    var n = parseFloat(s);
    return Number.isFinite(n) ? Number(n.toFixed(2)) : 0;
  }
  function genEventId(name){
    if (typeof window._mpGenEventId === 'function') return window._mpGenEventId(name);
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
   * Викликати коли юзер починає оформлення:
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

      // build contents / content_ids / total
      var contents = [], content_ids = [], total = 0, firstName = '';
      for (var i=0; i<opts.items.length; i++){
        var it = opts.items[i] || {};
        var id = (it.variant_sku ?? '').toString().trim();
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

      // ---- 1) Pixel (browser) ----
      (function sendPixel(attempt){
        attempt = attempt || 0;
        if (typeof window.fbq !== 'function') {
          if (attempt > 60) return;            // ~5s
          return setTimeout(function(){ sendPixel(attempt+1); }, 80);
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

      // ---- 2) CAPI (server) ----
      var bodyObj = {
        event_id: icId,
        event_time: Math.floor(Date.now()/1000),
        event_source_url: window.location.href,
        content_type: 'product',
        content_ids: content_ids,
        contents: contents,
        num_items: numItems,
        value: value,
        currency: currency
      };
      if (firstName) bodyObj.content_name = firstName;
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
