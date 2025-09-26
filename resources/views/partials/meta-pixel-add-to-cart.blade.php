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
  // ▸ Захист від повторного оголошення у SPA (щоб не перезаписувати функцію при навігації)
  if (window.mpTrackATC) return;

  // ▸ Локальний прапорець: дозволено, якщо глобалка _mpFlags.atc ≠ false
  var atcEnabled = !(window._mpFlags && window._mpFlags.atc === false);

  /* ========================== УТИЛІТИ ========================== */

  // ▸ Приведення довільного значення до числа з двома знаками після коми
  function num(v){
    var s = String(v ?? 0).replace(',', '.').replace(/[^\d.\-]/g,''); // нормалізуємо роздільник і прибираємо зайве
    var n = parseFloat(s);
    return Number.isFinite(n) ? Number(n.toFixed(2)) : 0;
  }

  // ▸ Взяти значення параметра з URL (наприклад fbclid)
  function getParam(name){
    var m = location.search.match(new RegExp('[?&]'+name+'=([^&]+)'));
    return m ? m[1] : null;
  }

  // ▸ Прочитати cookie за ім’ям (нічого не створюємо)
  function getCookie(n){
    var m = document.cookie.match(new RegExp('(?:^|;\\s*)' + n + '=([^;]+)'));
    return m ? decodeURIComponent(m[1]) : null;
  }

  // ▸ Визначити, що це FB/IG-трафік: є _fbc cookie або fbclid у URL
  function isFacebookTraffic(){
    return !!(getCookie('_fbc') || getParam('fbclid'));
  }

  // ▸ Згенерувати event_id (спільний формат для Pixel і CAPI → дедуплікація)
  function genEventId(name){
    // якщо є глобальний генератор (із базового паршалу) — використовуємо його
    if (typeof window._mpGenEventId === 'function') return window._mpGenEventId(name);
    // інакше генеруємо тут
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
     Викликайте її у момент фактичного додавання товару в кошик:

     window.mpTrackATC({
       variant_sku: 'PRD77-1234', // ⚠️ обов’язково: ID варіанта (використовується як content_id)
       price: 799.00,              // ціна за одиницю
       quantity?: 1,               // (опц.) кількість, дефолт 1
       name?: 'Назва товару',      // (опц.) ім’я товару (підтягнеться в Pixel/CAPI)
       currency?: 'UAH'            // (опц.) валюта; інакше береться з window.metaPixelCurrency
     })
  =============================================================== */
  window.mpTrackATC = function(opts){
    try{
      if (!opts) return;

      /* ---------- GA4 (не блокує FB, просто тригеримо паралельно) ---------- */
      if (typeof window.ga4AddToCart === 'function') {
        window.ga4AddToCart(opts); // передаємо ті ж поля
      }

      /* ---------- FB Pixel / CAPI: включено + це FB-трафік ---------- */
      if (!(atcEnabled && isFacebookTraffic())) return;

      /* ---------- Валідація обов’язкових даних ---------- */
      var pid = (opts.variant_sku ?? '').toString().trim(); // content_id = variant_sku
      if (!pid) {
        console.warn('[ATC] Подія пропущена — немає variant_sku');
        return;
      }

      /* ---------- Підготовка значень ---------- */
      var qty = Number(opts.quantity || 1);
      if (!Number.isFinite(qty) || qty <= 0) qty = 1;

      var price    = num(opts.price);
      var name     = typeof opts.name === 'string' ? opts.name : '';
      var currency = (opts.currency || window.metaPixelCurrency || 'UAH').toString().trim().toUpperCase();

      // contents[] у форматі Meta: [{ id, quantity, item_price }]
      var contents = [{ id: pid, quantity: qty, item_price: price }];
      var value    = num(qty * price); // value = qty * price (без доставки/податків)

      // Єдиний event_id → піде в обидва канали (Pixel/CAPI) для дедуплікації
      var atcId = genEventId('atc');

      /* ====================== 1) БРАУЗЕРНИЙ PIXEL ======================
         - fbq('track', 'AddToCart', custom_data, {eventID})
         - Якщо fbevents.js ще не підвантажився — чекаємо до ~5с
      ================================================================== */
      (function sendPixel(attempt){
        attempt = attempt || 0;
        if (typeof window.fbq !== 'function') {
          if (attempt > 60) return; // ~5 сек @ 80мс
          return setTimeout(function(){ sendPixel(attempt+1) }, 80);
        }
        try {
          fbq('track', 'AddToCart', {
            content_ids: [pid],          // масив ID для сумісності з Meta
            content_type: 'product',     // обов’язково для ecommerce
            contents: contents,          // масив об’єктів із qty/price
            content_name: name,          // (опц.) назва товару
            value: value,                // сума по позиції(ях)
            currency: currency           // валюта (UPPERCASE)
          }, { eventID: atcId });        // спільний event_id (дедуп з CAPI)
        } catch(_) {}
      })();

      /* ========================= 2) SERVER CAPI =========================
         - надсилаємо майже той самий custom_data на бек:
           /api/track/atc (TrackController@atc)
         - бек додасть user_data (IP/UA + fbc/fbp із cookies, PII → SHA-256)
         - використовується той самий event_id (atcId) для дедуплікації
      =================================================================== */
      var bodyObj = {
        event_id: atcId,
        event_time: Math.floor(Date.now()/1000),
        event_source_url: window.location.href,

        // custom_data (узгоджено з контролером)
        content_type: 'product',
        content_ids: [pid],
        contents: contents,
        content_name: name,
        value: value,
        currency: currency
        // user_data НЕ передаємо з фронта — бек підставить із cookies (fbc/fbp/extid) та PII-хеші
      };

      // ▸ Якщо тест-код заданий у глобалці — прокидуємо його
      if (window._mpTestCode) bodyObj.test_event_code = window._mpTestCode;

      // ▸ Відправка на бек (sendBeacon → краще для розвантаження, fallback на fetch)
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
      // ▸ Failsafe: будь-яка помилка не ламає сторінку; маємо лише консольний лог
      console.warn('[ATC] mpTrackATC exception', e);
    }
  };
})();
</script>
@endif
