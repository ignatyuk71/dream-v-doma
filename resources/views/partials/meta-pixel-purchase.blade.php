@php
  use Illuminate\Support\Facades\DB;

  // 1) Читаємо налаштування трекінгу з БД
  $t = DB::table('tracking_settings')->first();

  // 2) Перевірка базових умов для Pixel (увімкнено, є pixel_id, не адмінка)
  $pixelOk = $t
    && (int)($t->pixel_enabled ?? 0) === 1
    && !empty($t->pixel_id)
    && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  // 3) Окремий тумблер для Purchase
  $allowPurchase = $pixelOk && (int)($t->send_purchase ?? 1) === 1;

  // 4) Валюта за замовчуванням з БД (нормалізуємо)
  $defaultCurrency = strtoupper(trim($t->default_currency ?? 'UAH'));
@endphp

@if ($allowPurchase)
  {{-- Проброс валюти з БД у глобалку ДО основного скрипта --}}
  <script>window.metaPixelCurrency = "{{ $defaultCurrency }}";</script>

  <script>
  (function(){
    // Не перевизначати глобалку при повторних монтаннях SPA
    if (window.mpTrackPurchase) return;

    // Додатковий фронтовий прапорець — можна вимкнути подію Purchase
    if (window._mpFlags && window._mpFlags.purchase === false) return;

    /* ========================== УТИЛІТИ ========================== */

    // Приведення значення до числа з 2-ма знаками
    function num(v){
      var s = String(v ?? 0).replace(',', '.').replace(/[^\d.\-]/g,'');
      var n = parseFloat(s);
      return Number.isFinite(n) ? Number(n.toFixed(2)) : 0;
    }

    // Читання параметра з URL
    function getParam(name){
      var m = location.search.match(new RegExp('[?&]'+name+'=([^&]+)'));
      return m ? m[1] : null;
    }

    // Зчитування cookie за ім'ям (нічого не створюємо)
    function getCookie(n){
      var m = document.cookie.match(new RegExp('(?:^|;\\s*)' + n + '=([^;]+)'));
      return m ? decodeURIComponent(m[1]) : null;
    }

    // ❗️Лише платний FB/IG-трафік: _fbc cookie або fbclid у URL
    function isFacebookTraffic(){
      return !!(getCookie('_fbc') || getParam('fbclid'));
    }

    // Випадковий event_id (ідентичний для Pixel і CAPI → дедуп)
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

    // Будуємо contents[] ТІЛЬКИ з variant_sku як id
    function buildContents(items){
      var out = [];
      for (var i=0; i<(items||[]).length; i++){
        var it = items[i] || {};
        var id = (it.variant_sku ?? '').toString().trim();
        if (!id) continue; // пропускаємо без SKU варіанта
        var qty   = Number(it.quantity || 1); if (!Number.isFinite(qty) || qty <= 0) qty = 1;
        var price = num(it.price ?? it.item_price ?? 0);
        out.push({ id:id, quantity:qty, item_price:price });
      }
      return out;
    }

    /**
     * Викликати після успішної оплати/створення замовлення:
     *
     * window.mpTrackPurchase({
     *   order_number?: 'A12345',
     *   items: [{ variant_sku, price, quantity, name? }, ...],
     *   value?: number, currency?: 'UAH', shipping?: number, tax?: number,
     *   // нижче — опціонально; якщо передати, бек їх ЗАХЕШУЄ у user_data
     *   email?: string, phone?: string, first_name?: string, last_name?: string, external_id?: string,
     *   test_event_code?: string
     * })
     */
    window.mpTrackPurchase = function(opts){
      try{
        if (!opts || !Array.isArray(opts.items) || !opts.items.length) return;
        if (!isFacebookTraffic()) return; // шлемо лише рекламний FB-трафік

        var contents = buildContents(opts.items);
        if (!contents.length) return;

        var ids      = contents.map(function(c){ return c.id });
        var qtySum   = contents.reduce(function(s,c){ return s + (Number(c.quantity)||0) }, 0);
        var subtotal = contents.reduce(function(s,c){ return s + num(c.item_price) * (Number(c.quantity)||0) }, 0);

        var currency = (opts.currency || window.metaPixelCurrency || 'UAH').toString().trim().toUpperCase();
        var shipping = num(opts.shipping || 0);
        var tax      = num(opts.tax || 0);
        var value = subtotal;

        var orderNo  = opts.order_number ? String(opts.order_number) : null;
        var eventId  = genEventId('purchase');

        // Локальний guard від дублю (за order_number у цьому браузері)
        if (orderNo) {
          var guardKey = 'purchase_sent_' + orderNo;
          if (localStorage.getItem(guardKey) === '1') return;
          localStorage.setItem(guardKey, '1');
        }

        /* ====================== 1) Pixel (браузер) ======================
           Якщо fbq ще не підвантажився — ретраїмо до ~5 секунд.
        ================================================================== */
        (function sendPixel(attempt){
          attempt = attempt || 0;
          if (typeof window.fbq !== 'function') {
            if (attempt > 60) return; // ~5 сек @ 80мс
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

        /* ========================= 2) CAPI (сервер) =========================
           Надсилаємо ті ж custom_data на бек:
           – бек додасть user_data (IP/UA, fbc/fbp з cookies, PII-хеші за потреби),
           – використає той самий event_id для дедупу з Pixel.
           ⚠️ fbp/fbc з фронта НЕ передаємо.
        ==================================================================== */
        var capiBody = {
          event_id: eventId,
          event_time: Math.floor(Date.now()/1000),
          event_source_url: window.location.href,

          // custom_data
          content_type: 'product',
          content_ids: ids,
          contents: contents,
          num_items: qtySum,
          value: value,
          currency: currency,
          shipping: shipping,
          tax: tax,
          order_number: orderNo,

          // PII (опціонально): бек їх захешує у user_data
          email: opts.email || null,
          phone: opts.phone || null,
          first_name: opts.first_name || null,
          last_name: opts.last_name || null,
          external_id: opts.external_id || null
        };
        if (opts.test_event_code || window._mpTestCode) {
          capiBody.test_event_code = String(opts.test_event_code || window._mpTestCode);
        }

        var body = JSON.stringify(capiBody);

        if (navigator.sendBeacon) {
          navigator.sendBeacon('/api/track/purchase', new Blob([body], {type:'application/json'}));
        } else {
          fetch('/api/track/purchase', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            keepalive: true,
            body
          });
        }
      } catch(_){}
    };
  })();
  </script>
@endif
