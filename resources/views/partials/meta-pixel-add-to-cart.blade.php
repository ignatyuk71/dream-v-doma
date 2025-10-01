@php
  use Illuminate\Support\Facades\DB;

  $t = DB::table('tracking_settings')->first();

  $pixelOk  = $t && (int)($t->pixel_enabled ?? 0) === 1 && !empty($t->pixel_id)
              && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));
  $allowATC = $pixelOk && (int)($t->send_add_to_cart ?? 1) === 1;

  // CAPI вмикаймо лише якщо є токен
  $capiOn = $allowATC && $t && (int)($t->capi_enabled ?? 0) === 1 && !empty($t->capi_token);
@endphp

@if ($allowATC)
<script>
(function(){
  if (window._mpATCReady) return; window._mpATCReady = true;

  // допоміжне: привести ціну до числа
  function num(v){
    var s = String(v ?? 0).replace(',', '.').replace(/[^\d.\-]/g,'');
    var n = parseFloat(s);
    return Number.isFinite(n) ? Number(n.toFixed(2)) : 0;
  }

  // простий тротлінг, щоб не зловити дабл-клік
  var _lastSendAt = 0;
  function notTooSoon(ms){
    var now = Date.now();
    if (now - _lastSendAt < ms) return false;
    _lastSendAt = now; return true;
  }

  /**
   * Викликаєш із Vue: window.mpTrackATC({ variant_sku|sku|id, price, quantity, name?, currency? })
   */
  function mpTrackATC(opts){
    try{
      if (!opts) return;
      if (!notTooSoon(500)) return; // 0.5s анти дабл-клік

      // зібрати contents/value
      var contents = [], sum = 0;

      if (Array.isArray(opts.contents) && opts.contents.length){
        for (var i=0;i<opts.contents.length;i++){
          var r = opts.contents[i] || {};
          var iid = String(r.id ?? '').trim();
          if (!iid) continue;
          var qty = Number(r.quantity ?? 1); if (!Number.isFinite(qty) || qty <= 0) qty = 1;
          var ip  = num(r.item_price ?? r.price ?? 0);
          contents.push({ id: iid, quantity: qty, item_price: ip });
          sum += qty * ip;
        }
      } else {
        var pid = String((opts.variant_sku ?? opts.sku ?? opts.id ?? '')).trim();
        if (!pid) { console.warn('[ATC] missing id/sku'); return; }
        var qty = Number(opts.quantity ?? 1); if (!Number.isFinite(qty) || qty <= 0) qty = 1;
        var ip  = num(opts.price);
        contents.push({ id: pid, quantity: qty, item_price: ip });
        sum += qty * ip;
      }

      if (!contents.length) return;

      var value    = Number(sum.toFixed(2));
      var currency = String((opts.currency || window.metaPixelCurrency || 'UAH')).trim().toUpperCase();
      var name     = (typeof opts.name === 'string') ? opts.name : undefined;

      /* ── GA4: відправляємо add_to_cart у dataLayer (якщо підключений partial) ── */
      if (typeof window.ga4AddToCart === 'function') {
        try {
          var first = contents[0] || {};
          window.ga4AddToCart({
            contents:   contents,              // [{id, quantity, item_price}, ...]
            currency:   currency,
            name:       name || '',
            // фолбек на одиночний товар
            variant_sku: String(first.id || ''),
            price:       Number(first.item_price || 0),
            quantity:    Number(first.quantity || 1)
          });
        } catch(_) {}
      }
      /* ─────────────────────────────────────────────────────────────────────── */

      // один eventID для Browser + Server (дедуп)
      var eventId = 'atc-' + Math.random().toString(16).slice(2) + '-' + Date.now();

      // Browser Pixel — чекаємо fbq до ~2с
      (function wait(i){
        i = i || 0;
        if (typeof window.fbq === 'function') {
          try {
            var payload = {
              content_type: 'product',
              content_ids: contents.map(function(c){ return c.id }),
              contents: contents,
              value: value,
              currency: currency
            };
            if (name) payload.content_name = name;
            fbq('track', 'AddToCart', payload, { eventID: eventId });
          } catch(e){}
          return;
        }
        if (i >= 25) return; // ~2s
        setTimeout(function(){ wait(i+1); }, 80);
      })();

      // Server (CAPI) — тим самим event_id
      @if ($capiOn)
      (function(){
        var body = JSON.stringify({
          event_id: eventId,
          page_url: location.href,
          currency: currency,
          contents: contents,
          name: name || null
        });
        var sent = false;
        if (navigator.sendBeacon) {
          try { sent = navigator.sendBeacon('/api/track/atc', new Blob([body], {type:'application/json'})); } catch(_){}
        }
        if (!sent) {
          try {
            fetch('/api/track/atc', {
              method: 'POST', keepalive: true,
              headers: { 'Content-Type': 'application/json' },
              body
            }).catch(function(){});
          } catch(_){}
        }
      })();
      @endif

    } catch(e){ console.warn('[ATC] exception', e); }
  }

  // експорт тільки функції (без делегатів і аліасів)
  window.mpTrackATC = mpTrackATC;
})();
</script>
@endif
