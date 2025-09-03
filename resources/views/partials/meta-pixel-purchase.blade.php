@php
  use Illuminate\Support\Facades\DB;

  // 1) Читаємо налаштування трекінгу з БД
  $t = DB::table('tracking_settings')->first();

  // 2) Перевірка базових умов для Pixel (увімкнено, є pixel_id, не адмінка)
  $pixelOk = $t
    && (int)($t->pixel_enabled ?? 0) === 1
    && !empty($t->pixel_id)
    && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  // 3) Окремий тумблер для Purchase (send_purchase TINYINT(1))
  $allowPurchase = $pixelOk && (int)($t->send_purchase ?? 1) === 1;
@endphp

@if ($allowPurchase)
<script>
(function(){
  // Не перевизначати глобалку при повторних монтаннях SPA
  if (window.mpTrackPurchase) return;

  // Додатковий фронтовий прапорець — можна вимкнути подію Purchase
  if (window._mpFlags && window._mpFlags.purchase === false) return;

  /* ========================== УТИЛІТИ ========================== */

  // Приведення значення до числа з 2-ма знаками після коми
  function num(v){
    var s = String(v ?? 0).replace(',', '.').replace(/[^\d.\-]/g,'');
    var n = parseFloat(s);
    return Number.isFinite(n) ? Number(n.toFixed(2)) : 0;
  }

  // Зчитування cookie за ім'ям
  function getCookie(n){
    return document.cookie.split('; ')
      .find(function(r){ return r.indexOf(n + '=') === 0 })?.split('=')[1] || null;
  }

  // Безпечний decodeURIComponent
  function safeDecode(c){ try { return c ? decodeURIComponent(c) : null } catch(_) { return c } }

  // Переконатися, що є _fbp (для CAPI); якщо немає — згенерувати і зберегти
  function ensureFbp(){
    var fbp = getCookie('_fbp') || localStorage.getItem('fbp_generated');
    if (!fbp) {
      fbp = 'fb.1.' + Math.floor(Date.now()/1000) + '.' + Math.floor(Math.random()*1e10);
      localStorage.setItem('fbp_generated', fbp);
    }
    return fbp;
  }

  // Генеруємо випадковий event_id (НЕ використовуємо номер замовлення)
  // Формат: <name>-<12hex>-<unix>
  // Один і той самий event_id піде у Pixel і CAPI → Meta їх дедуплікує
  function genEventId(name){
    try {
      var a = new Uint8Array(6);
      (window.crypto || window.msCrypto).getRandomValues(a);
      var hex = Array.from(a).map(function(b){ return b.toString(16).padStart(2,'0') }).join('');
      return name + '-' + hex + '-' + Math.floor(Date.now()/1000);
    } catch (_e) {
      return name + '-' + Math.random().toString(16).slice(2) + '-' + Math.floor(Date.now()/1000);
    }
  }

  // Будуємо contents[] ТІЛЬКИ з variant_sku як id
  function buildContents(items){
    var out = [];
    for (var i=0; i<(items||[]).length; i++){
      var it = items[i] || {};
      var id = (it.variant_sku ?? '').toString().trim();
      if (!id) { continue; } // пропускаємо позиції без variant_sku
      var qty = Number(it.quantity || 1); if (!Number.isFinite(qty) || qty <= 0) qty = 1;
      var price = num(it.price ?? it.item_price ?? 0);
      out.push({ id: id, quantity: qty, item_price: price });
    }
    return out;
  }

  /**
   * Головна глобальна функція:
   *
   * window.mpTrackPurchase({
   *   order_number?: string, // для локального guard'а від дублю у браузері
   *   items: [{ variant_sku: string, price: number, quantity: number, name?: string }, ...],
   *   value?: number, currency?: 'UAH', shipping?: number, tax?: number,
   *
   *   // PII — ЛИШЕ для CAPI (Pixel цього не отримує):
   *   email?: string, phone?: string, first_name?: string, last_name?: string, external_id?: string,
   *
   *   // Для Test Events у Events Manager:
   *   test_event_code?: string
   * })
   */
  window.mpTrackPurchase = function(opts){
    try{
      // Мінімальна валідація
      if (!opts || !Array.isArray(opts.items) || !opts.items.length) return;

      // Формуємо contents з позицій де є variant_sku
      var contents = buildContents(opts.items);
      if (!contents.length) return;

      // Похідні значення
      var ids      = contents.map(function(c){ return c.id });
      var qtySum   = contents.reduce(function(s,c){ return s + (Number(c.quantity)||0) }, 0);
      var subtotal = contents.reduce(function(s,c){ return s + num(c.item_price) * (Number(c.quantity)||0) }, 0);

      // Валюта/суми (value: якщо не передано — subtotal + shipping + tax)
      var currency = opts.currency || (window.metaPixelCurrency || 'UAH');
      var shipping = num(opts.shipping || 0);
      var tax      = num(opts.tax || 0);
      var value    = num(opts.value != null ? opts.value : (subtotal + shipping + tax));

      // Номер замовлення — лише для локального guard'а (не для event_id)
      var orderNo  = opts.order_number ? String(opts.order_number) : null;

      // ЄДИНИЙ випадковий event_id для Pixel і CAPI
      var eventId  = genEventId('purchase');

      // Локальний guard від дублю відправки Purchase у цьому браузері
      if (orderNo) {
        var guardKey = 'purchase_sent_' + orderNo;
        if (localStorage.getItem(guardKey) === '1') return;
        localStorage.setItem(guardKey, '1');
      }

      /* ====================== 1) БРАУЗЕРНИЙ PIXEL ======================
         Pixel НЕ отримує PII (email/phone/ім'я тощо) — лише товарні дані.
         Передаємо { eventID: eventId } для дедуплікації із CAPI.
      ================================================================== */
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
        } catch (_) {}
      })();

      /* ======================== 2) SERVER CAPI =========================
         Відправляємо ті ж дані на бек: /api/track/purchase (TrackController@purchase).
         На бекенді:
           - user_data (em/ph/fn/ln) хешуються SHA-256;
           - використовується той самий event_id для дедупу;
           - якщо email/phone немає — ці поля просто не включаються.
      ================================================================== */
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

        // user_data — бек захешує (SHA-256) і відправить у Meta
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

      // Надсилання на бек як beacon (або fetch з keepalive, якщо beacon недоступний)
      if (navigator.sendBeacon) {
        navigator.sendBeacon('/api/track/purchase', new Blob([body], {type:'application/json'}));
      } else {
        fetch('/api/track/purchase', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'same-origin',
          keepalive: true,
          body
        }).catch(function(){});
      }
    } catch(_){}
  };
})();
</script>
@endif
