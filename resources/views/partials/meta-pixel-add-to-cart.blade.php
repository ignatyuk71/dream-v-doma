@php
  use Illuminate\Support\Facades\DB;

  // 1) Читаємо налаштування трекінгу з БД
  $t = DB::table('tracking_settings')->first();

  // 2) Перевіряємо, що Pixel увімкнений, є pixel_id і ми не на адмін-URL
  $pixelOk = $t
    && (int)($t->pixel_enabled ?? 0) === 1
    && !empty($t->pixel_id)
    && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  // 3) Окремий тумблер для AddToCart (send_add_to_cart TINYINT(1))
  $allowATC = $pixelOk && (int)($t->send_add_to_cart ?? 1) === 1;
@endphp

@if ($allowATC)
<script>
(function(){
  if (window.mpTrackATC) return; // захист від повторного оголошення
  var atcEnabled = !(window._mpFlags && window._mpFlags.atc === false);

  /* ========================== УТИЛІТИ ========================== */

  // Приведення до числа з двома знаками після коми
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

  // ❗️Визначаємо трафік з реклами FB/IG:
  // або є cookie _fbc, або параметр fbclid у URL
  function isFacebookTraffic(){
    return !!(getCookie('_fbc') || getParam('fbclid'));
  }

  // Генератор event_id (спільний для Pixel і CAPI — для дедуплікації)
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

  /* ====================== ГОЛОВНА ФУНКЦІЯ ======================
     Виклик з Vue після успішного додавання в кошик:

     window.mpTrackATC({
       variant_sku: 'PRD77-1234', // ⚠️ обов’язковий content_id (SKU варіанта)
       price: 799.00,              // ціна за одиницю
       quantity?: 1,               // кількість (за замовчуванням 1)
       name?: 'Назва товару',      // опціонально
       currency?: 'UAH'            // опціонально (дефолт з налаштувань)
     })
  =============================================================== */
  window.mpTrackATC = function(opts){
    try{
      if (!opts) return;

            // 0) GA4 — шлемо ЗАВЖДИ (до будь-яких перевірок)
      if (typeof window.ga4AddToCart === 'function') {
        window.ga4AddToCart(opts);
      }

      // ❗️ШЛЕМО ТІЛЬКИ ДЛЯ FB-ТРАФІКУ
      if (!(atcEnabled && isFacebookTraffic())) return;

      /* --- Валідація: variant_sku обов’язковий --- */
      var pid = (opts.variant_sku ?? '').toString().trim();
      if (!pid) {
        console.warn('[ATC] Подія пропущена — немає variant_sku');
        return;
      }

      /* --- Підготовка значень --- */
      var qty      = Number(opts.quantity || 1);
      if (!Number.isFinite(qty) || qty <= 0) qty = 1;

      var price    = num(opts.price);
      var name     = typeof opts.name === 'string' ? opts.name : '';
      var currency = opts.currency || (window.metaPixelCurrency || 'UAH');

      // Структура contents[] за вимогами Meta
      var contents = [{ id: pid, quantity: qty, item_price: price }];
      var value    = num(qty * price);

      // Єдиний event_id → піде і в Pixel, і в CAPI (дедуплікація)
      var atcId    = genEventId('atc');

      /* ====================== 1) БРАУЗЕРНИЙ PIXEL ======================
         Використовує fbq('track', 'AddToCart').
         Якщо Pixel ще не підвантажився, ретраїмо до 5 секунд.
      ================================================================== */
      (function sendPixel(attempt){
        attempt = attempt || 0;
        if (typeof window.fbq !== 'function') {
          if (attempt > 60) return; // ~5 секунд
          return setTimeout(function(){ sendPixel(attempt+1) }, 80);
        }
        try {
          fbq('track', 'AddToCart', {
            content_ids: [pid],
            content_type: 'product',
            contents: contents,
            content_name: name,
            value: value,
            currency: currency
          }, { eventID: atcId });
        } catch(_) {}
      })();

      /* ========================= 2) SERVER CAPI =========================
         Відправляємо ті ж дані на бек: /api/track/atc (TrackController@atc).
         На бекенді:
           - додаються user_data (IP, UA; PII якщо є — хешуються SHA-256),
           - використовується той самий event_id для дедуплікації.
      =================================================================== */
      var bodyObj = {
        event_id: atcId,
        event_time: Math.floor(Date.now()/1000),
        event_source_url: window.location.href,

        // custom_data (синхронізовано з Pixel)
        content_type: 'product',
        content_ids: [pid],
        contents: contents,
        content_name: name,
        value: value,
        currency: currency
        // ❗️user_data (email, phone, fbp/fbc) підтягується на бекенді
      };

      if (window._mpTestCode) bodyObj.test_event_code = window._mpTestCode;

      var body = JSON.stringify(bodyObj);

      if (navigator.sendBeacon) {
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
      // Failsafe: не ламаємо сторінку, лише лог у консоль
      console.warn('[ATC] mpTrackATC exception', e);
    }
  };
})();
</script>
@endif

