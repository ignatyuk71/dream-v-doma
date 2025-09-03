@php
  use Illuminate\Support\Facades\DB;

  $t = DB::table('tracking_settings')->first();

  $pixelOk = $t
    && (int)($t->pixel_enabled ?? 0) === 1
    && !empty($t->pixel_id)
    && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  // окремий тумблер для Purchase (send_purchase TINYINT(1) у tracking_settings)
  $allowPurchase = $pixelOk && (int)($t->send_purchase ?? 1) === 1;
@endphp

@if ($allowPurchase)
<script>
(function(){
  // не перевизначати при повторних завантаженнях SPA
  if (window.mpTrackPurchase) return;

  // глобальним прапорцем можна вимкнути purchase повністю
  if (window._mpFlags && window._mpFlags.purchase === false) return;

  /* ================== утиліти ================== */
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
  function ensureFbp(){
    var fbp = getCookie('_fbp') || localStorage.getItem('fbp_generated');
    if (!fbp) {
      fbp = 'fb.1.' + Math.floor(Date.now()/1000) + '.' + Math.floor(Math.random()*1e10);
      localStorage.setItem('fbp_generated', fbp);
    }
    return fbp;
  }
  function genEventId(name, orderNo){
    if (orderNo) return name + '-' + String(orderNo);
    try {
      var a = new Uint8Array(6);
      (window.crypto || window.msCrypto).getRandomValues(a);
      var hex = Array.from(a).map(function(b){ return b.toString(16).padStart(2,'0') }).join('');
      return name + '-' + hex + '-' + Math.floor(Date.now()/1000);
    } catch (_e) {
      return name + '-' + Math.random().toString(16).slice(2) + '-' + Math.floor(Date.now()/1000);
    }
  }
  // будуємо contents ТІЛЬКИ з variant_sku
  function buildContents(items){
    var out = [];
    for (var i=0; i<(items||[]).length; i++){
      var it = items[i] || {};
      var id = (it.variant_sku ?? '').toString().trim();
      if (!id) { console.warn('[Purchase] skip item without variant_sku', it); continue; }
      var qty = Number(it.quantity || 1); if (!Number.isFinite(qty) || qty <= 0) qty = 1;
      var price = num(it.price ?? it.item_price ?? 0);
      out.push({ id: id, quantity: qty, item_price: price });
    }
    return out;
  }

  /**
   * window.mpTrackPurchase({
   *   order_number?: string,
   *   items: [{ variant_sku: string, price: number, quantity: number, name?: string }, ...],
   *   value?: number, currency?: 'UAH', shipping?: number, tax?: number,
   *   // PII — тільки для CAPI (не йде в Pixel):
   *   email?: string, phone?: string, first_name?: string, last_name?: string, external_id?: string,
   *   test_event_code?: string
   * })
   */
  window.mpTrackPurchase = function(opts){
    try{
      if (!opts || !Array.isArray(opts.items) || !opts.items.length) {
        console.warn('[Purchase] no items provided'); return;
      }

      var contents = buildContents(opts.items);
      if (!contents.length) { console.warn('[Purchase] nothing to send (no variant_sku)'); return; }

      var ids      = contents.map(function(c){ return c.id });
      var qtySum   = contents.reduce(function(s,c){ return s + (Number(c.quantity)||0) }, 0);
      var subtotal = contents.reduce(function(s,c){ return s + num(c.item_price) * (Number(c.quantity)||0) }, 0);

      var currency = opts.currency || (window.metaPixelCurrency || 'UAH');
      var shipping = num(opts.shipping || 0);
      var tax      = num(opts.tax || 0);
      var value    = num(opts.value != null ? opts.value : (subtotal + shipping + tax));

      var orderNo  = opts.order_number ? String(opts.order_number) : null;
      var eventId  = genEventId('purchase', orderNo);

      // Guard від дубляжу для того самого замовлення у браузері
      if (orderNo) {
        var guardKey = 'purchase_sent_' + orderNo;
        if (localStorage.getItem(guardKey) === '1') {
          console.info('[Purchase] already sent for order', orderNo);
          return; // ← важливо: не слати повторно
        }
        localStorage.setItem(guardKey, '1');
      }

      /* ============== 1) Браузерний Pixel (без PII) ============== */
      (function sendPixel(attempt){
        attempt = attempt || 0;
        if (typeof window.fbq !== 'function') {
          if (attempt > 120) return; // ~10 сек @ 80мс
          return setTimeout(function(){ sendPixel(attempt+1) }, 80);
        }
        try {
          window.fbq('track', 'Purchase', {
            content_ids: ids,
            content_type: 'product',
            contents: contents,
            num_items: qtySum,
            value: value,
            currency: currency
          }, { eventID: eventId });
          console.log('[Purchase][Pixel] sent', { eventId, value, currency, num_items: qtySum, items: contents });
        } catch (e) {
          console.warn('[Purchase][Pixel] error', e);
        }
      })();

      /* ============== 2) Серверний CAPI (з PII) ============== */
      var fbp = safeDecode(getCookie('_fbp')) || ensureFbp();
      var fbc = safeDecode(getCookie('_fbc')) || null;

      var capiBody = {
        event_id: eventId,
        event_time: Math.floor(Date.now()/1000),
        event_source_url: window.location.href,

        // custom_data
        content_type: 'product',
        contents: contents,
        content_ids: ids,
        num_items: qtySum,
        value: value,
        currency: currency,
        shipping: shipping,
        tax: tax,
        order_number: orderNo,

        // user_data (бек захешує em/ph/fn/ln сам у контроллері)
        email: opts.email || null,
        phone: opts.phone || null,
        first_name: opts.first_name || null,
        last_name: opts.last_name || null,
        external_id: opts.external_id || null,
        fbp: fbp,
        fbc: fbc
      };
      if (opts.test_event_code) capiBody.test_event_code = String(opts.test_event_code);

      var body = JSON.stringify(capiBody);

      // Надсилаємо на ваш бек — TrackController@purchase (POST /api/track/purchase)
      if (navigator.sendBeacon) {
        navigator.sendBeacon('/api/track/purchase', new Blob([body], {type:'application/json'}));
        console.log('[Purchase][CAPI] sendBeacon queued');
      } else {
        fetch('/api/track/purchase', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'same-origin',
          keepalive: true,
          body
        }).then(function(){ console.log('[Purchase][CAPI] fetch sent'); })
          .catch(function(e){ console.warn('[Purchase][CAPI] fetch error', e); });
      }
    } catch(e){
      console.warn('[Purchase] exception', e);
    }
  };
})();
</script>
@endif
