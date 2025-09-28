@php
  use Illuminate\Support\Facades\DB;

  $t = DB::table('tracking_settings')->first();

  $pixelOk = $t
    && (int)($t->pixel_enabled ?? 0) === 1
    && !empty($t->pixel_id)
    && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  // тільки якщо дозволено AddToCart
  $allowATC = $pixelOk && (int)($t->send_add_to_cart ?? 1) === 1;
@endphp

@if ($allowATC)
<script>
(function(){
  // не переоголошувати
  if (window.mpTrackATC) return;

  // глобальний вимикач
  var atcEnabled = !(window._mpFlags && window._mpFlags.atc === false);

  // простий числовий парсер
  function num(v){
    var s = String(v ?? 0).replace(',', '.').replace(/[^\d.\-]/g,'');
    var n = parseFloat(s);
    return Number.isFinite(n) ? Number(n.toFixed(2)) : 0;
  }

  // анти-дубль на короткому вікні (наприклад, подвійний клік)
  var _lastSendAt = 0;
  function notTooSoon(ms){
    var now = Date.now();
    if (now - _lastSendAt < ms) return false;
    _lastSendAt = now; return true;
  }

  /* ===================== ПУБЛІЧНА ФУНКЦІЯ =====================
     Викликати у момент реального додавання в кошик:

     window.mpTrackATC({
       variant_sku: 'PRD77-1234',  // обов'язково
       price: 799.00,              // ціна за одиницю
       quantity: 1,                // (опц.) кількість
       name: 'Назва товару',       // (опц.)
       currency: 'UAH'             // (опц.) ISO, дефолт — window.metaPixelCurrency або 'UAH'
     })
  ============================================================= */
  window.mpTrackATC = function(opts){
    try{
      if (!opts || !atcEnabled) return;

      // анти-дубль за 400мс
      if (!notTooSoon(400)) return;

      // → GA4 (залишено як просив)
      if (typeof window.ga4AddToCart === 'function') {
        try { window.ga4AddToCart(opts); } catch(_) {}
      }

      // валідація
      var pid = (opts.variant_sku ?? '').toString().trim();
      if (!pid) { console.warn('[ATC] Пропущено — немає variant_sku'); return; }

      var qty = Number(opts.quantity || 1);
      if (!Number.isFinite(qty) || qty <= 0) qty = 1;

      var price    = num(opts.price);
      var name     = typeof opts.name === 'string' ? opts.name : '';
      var currency = (opts.currency || window.metaPixelCurrency || 'UAH').toString().trim().toUpperCase();

      var contents = [{ id: pid, quantity: qty, item_price: price }];
      var value    = num(qty * price);

      // лише браузерний Pixel
      (function sendPixel(attempt){
        attempt = attempt || 0;
        if (typeof window.fbq !== 'function') {
          if (attempt > 60) return;           // ~5с чекаємо fbevents.js
          return setTimeout(function(){ sendPixel(attempt+1); }, 80);
        }
        try {
          // опц.: eventID, якщо потім захочеш дедуп з CAPI
          // var eid = 'atc-' + Date.now().toString(36) + Math.random().toString(36).slice(2,8);

          fbq('track', 'AddToCart', {
            content_ids: [pid],
            content_type: 'product',
            contents: contents,
            content_name: name,
            value: value,
            currency: currency
          }/* , {eventID: eid} */);
        } catch(e) { console.warn('[ATC] fbq error', e); }
      })();
    } catch(e){
      console.warn('[ATC] exception', e);
    }
  };
})();
</script>
@endif
