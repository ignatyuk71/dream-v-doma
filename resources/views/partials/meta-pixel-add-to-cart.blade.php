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
  // Не перевизначати глобалку при повторних монтаннях SPA/турбо
  if (window.mpTrackATC) return;

  // Додатковий фронтовий прапорець: якщо _mpFlags.atc === false → взагалі не оголошуємо
  if (!window._mpFlags || window._mpFlags.atc === false) return;

  /* ========================== УТИЛІТИ ========================== */

  // Приведення до числа з двома знаками після коми
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

  // Генератор event_id (спільний для Pixel і CAPI — для дедуплікації на стороні Meta)
  // Формат: <name>-<12hex>-<unix>
  // Якщо на сайті є глобальний window._mpGenEventId — використовуємо його
  function genEventId(name){
    if (typeof window._mpGenEventId === 'function') return window._mpGenEventId(name);
    try {
      var a = new Uint8Array(6);
      (window.crypto || window.msCrypto).getRandomValues(a);
      var hex = Array.from(a).map(function(b){ return b.toString(16).padStart(2,'0') }).join('');
      return name + '-' + hex + '-' + Math.floor(Date.now()/1000);
    } catch (_e) {
      return name + '-' + Math.random().toString(16).slice(2) + '-' + Math.floor(Date.now()/1000);
    }
  }

  /* ====================== ГОЛОВНА ФУНКЦІЯ ======================
     Виклик з Vue після успішного додавання в кошик:
     window.mpTrackATC({
       variant_sku: 'PRD77-1234', // ⚠️ контент-ID — ТІЛЬКИ variant_sku
       price: 799.00,
       quantity?: 1,
       name?: 'Назва товару',
       currency?: 'UAH'
     })
  =============================================================== */
  window.mpTrackATC = function(opts){
    try{
      if (!opts) return;

      /* --- Валідація: маємо відправляти тільки variant_sku як content_id --- */
      var pidRaw = (opts.variant_sku ?? '').toString().trim();
      if (!pidRaw) {
        // Без variant_sku — навмисно НЕ відправляємо, щоб не засмічувати події
        window.showGlobalToast?.('⚠️ Відсутній артикул варіанта (variant_sku). Подія трекінгу пропущена.', 'warning');
        return;
      }
      var pid = pidRaw;

      /* --- Підготовка значень --- */
      var qty = Number(opts.quantity || 1);
      if (!Number.isFinite(qty) || qty <= 0) qty = 1;

      var price    = num(opts.price);
      var name     = typeof opts.name === 'string' ? opts.name : '';
      var currency = opts.currency || (window.metaPixelCurrency || 'UAH');

      // Структура contents за вимогами Meta
      var contents = [{ id: pid, quantity: qty, item_price: price }];
      var value    = num(qty * price);

      // Єдиний event_id → піде і в Pixel, і в CAPI (дедуп)
      var atcId    = genEventId('atc');

      /* ====================== 1) БРАУЗЕРНИЙ PIXEL ======================
         Ретраїмо до появи fbq (короткий backoff ~10с).
         PII тут НЕ передаємо — лише товарні дані.
      ================================================================== */
      (function sendPixel(attempt){
        attempt = attempt || 0;
        if (typeof window.fbq !== 'function') {
          if (attempt > 120) return; // ~10 секунд @ ~80мс
          return setTimeout(function(){ sendPixel(attempt+1) }, 80);
        }
        fbq('track', 'AddToCart', {
          content_ids: [pid],
          content_type: 'product',
          contents: contents,
          content_name: name,
          value: value,
          currency: currency
        }, { eventID: atcId });
      })();

      /* ========================= 2) SERVER CAPI =========================
         Відправляємо ті ж дані на бек: /api/track/atc (TrackController@atc).
         На бекенді:
           - додаються user_data (IP, UA; PII якщо є — хешується SHA-256);
           - використовується той самий event_id для дедупу з Pixel.
      =================================================================== */
      var fbp = safeDecode(getCookie('_fbp'));
      var fbc = safeDecode(getCookie('_fbc'));

      // Тіло, яке піде в бек-ендпоінт (бек збере user_data + custom_data)
      var bodyObj = {
        event_id: atcId,
        event_time: Math.floor(Date.now()/1000),
        event_source_url: window.location.href,

        // custom_data
        content_type: 'product',
        content_ids: [pid],
        contents: contents,
        content_name: name,
        value: value,
        currency: currency,

        // маркери для CAPI (можуть бути відсутні — бек це врахує)
        fbp: fbp,
        fbc: fbc
      };

      // Тестовий код для Events Manager (якщо десь задаєш глобально)
      if (window._mpTestCode) bodyObj.test_event_code = window._mpTestCode;

      var body = JSON.stringify(bodyObj);

      // iOS/in-app → fetch keepalive; решта → sendBeacon (де можливо)
      var ua    = navigator.userAgent || '';
      var isiOS = /iPad|iPhone|iPod/i.test(ua) || (/Macintosh/i.test(ua) && 'ontouchend' in document);

      if (isiOS) {
        // iOS Safari іноді ігнорує sendBeacon, тому відправляємо fetch'ем з keepalive
        fetch('/api/track/atc', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'same-origin',
          keepalive: true,
          body
        }).catch(function(){
          // одноразовий повтор на випадок моментального переходу зі сторінки
          setTimeout(function(){
            fetch('/api/track/atc', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              credentials: 'same-origin',
              keepalive: true,
              body
            });
          }, 250);
        });
      } else if (navigator.sendBeacon) {
        // Найбезпечніший спосіб відправити дані під час навігації
        navigator.sendBeacon('/api/track/atc', new Blob([body], {type:'application/json'}));
      } else {
        // Фолбек — звичайний fetch
        fetch('/api/track/atc', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'same-origin',
          keepalive: true,
          body
        });
      }

      // Примітка: тут навмисно НЕ робимо guard від дублю:
      // ATC може відправлятися багаторазово в реальних сценаріях (клік по тому самому товару).
      // Якщо потрібен guard — додай локальний ключ у localStorage з тайм-аутом.
    } catch(e){
      // Failsafe: не ламаємо сторінку, лише попереджаємо в консолі
      console.warn('[ATC] mpTrackATC exception', e);
    }
  };
})();
</script>
@endif
