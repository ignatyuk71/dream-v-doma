@php
  use Illuminate\Support\Facades\DB;

  $t = DB::table('tracking_settings')->first();

  $pixelOk = $t
    && (int)($t->pixel_enabled ?? 0) === 1
    && !empty($t->pixel_id)
    && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  // лише якщо дозволено AddToCart у налаштуваннях
  $allowATC = $pixelOk && (int)($t->send_add_to_cart ?? 1) === 1;
@endphp

@if ($allowATC)
<script>
(function(){
  // не переоголошувати
  if (window._mpATCReady) return; window._mpATCReady = true;

  // утиліта числа
  function num(v){
    var s = String(v ?? 0).replace(',', '.').replace(/[^\d.\-]/g,'');
    var n = parseFloat(s);
    return Number.isFinite(n) ? Number(n.toFixed(2)) : 0;
  }

  // дуже короткий анти-дубль
  var _lastSendAt = 0;
  function notTooSoon(ms){
    var now = Date.now();
    if (now - _lastSendAt < ms) return false;
    _lastSendAt = now; return true;
  }

  /** ===== ОСНОВНА ФУНКЦІЯ: тільки Pixel Browser + твій GA4 ===== */
  function mpTrackATC(opts){
    try{
      if (!opts) return;
      if (!notTooSoon(150)) return;

      // GA4 — залишаємо як є
      if (typeof window.ga4AddToCart === 'function') {
        try { window.ga4AddToCart(opts); } catch(_) {}
      }

      // валідація
      var pid = (opts.variant_sku ?? opts.sku ?? opts.id ?? '').toString().trim();
      if (!pid) { console.warn('[ATC] no variant_sku/sku/id'); return; }

      var qty      = Number(opts.quantity || 1); if (!Number.isFinite(qty) || qty <= 0) qty = 1;
      var price    = num(opts.price);
      var name     = typeof opts.name === 'string' ? opts.name : '';
      var currency = (opts.currency || window.metaPixelCurrency || 'UAH').toString().trim().toUpperCase();

      var contents = [{ id: pid, quantity: qty, item_price: price }];
      var value    = num(qty * price);

      // лише БРАУЗЕРНИЙ PIXEL (без eventID, без будь-яких перевірок трафіку)
      (function sendPixel(i){
        i = i||0;
        if (typeof window.fbq !== 'function') {
          if (i > 60) return;                // ~5с чекаємо fbevents.js
          return setTimeout(function(){ sendPixel(i+1); }, 80);
        }
        try {
          fbq('track', 'AddToCart', {
            content_ids: [pid],
            content_type: 'product',
            contents: contents,
            content_name: name,
            value: value,
            currency: currency
          });
        } catch(e){ console.warn('[ATC] fbq error', e); }
      })();
    } catch(e){
      console.warn('[ATC] exception', e);
    }
  }

  // експорт + аліаси (щоб старі виклики не поламались)
  window.mpTrackATC     = window.mpTrackATC     || mpTrackATC;
  window.mpAddToCart    = window.mpAddToCart    || mpTrackATC;
  window.trackAddToCart = window.trackAddToCart || mpTrackATC;
  window.sendAddToCart  = window.sendAddToCart  || mpTrackATC;
  window.fbAddToCart    = window.fbAddToCart    || mpTrackATC;

  // ДОДАТКОВО: делегація на кнопки з data-атрибутами (якщо десь потрібно без JS)
  // 1) data-mp-atc='{"variant_sku":"PRD-123","price":375,"quantity":1,"name":"Назва","currency":"UAH"}'
  // 2) або: data-variant-sku / data-price / data-qty / data-name / data-currency
  document.addEventListener('click', function(e){
    var el = e.target.closest('[data-mp-atc],[data-variant-sku]');
    if (!el) return;

    var payload = null;
    var raw = el.getAttribute('data-mp-atc');
    if (raw) { try { payload = JSON.parse(raw); } catch(_) {} }

    if (!payload) {
      payload = {
        variant_sku: el.getAttribute('data-variant-sku') || el.getAttribute('data-sku') || '',
        price:       el.getAttribute('data-price'),
        quantity:    el.getAttribute('data-qty') || el.getAttribute('data-quantity') || 1,
        name:        el.getAttribute('data-name') || '',
        currency:    el.getAttribute('data-currency') || undefined
      };
    }
    if (payload) mpTrackATC(payload);
  });
})();
</script>
@endif
