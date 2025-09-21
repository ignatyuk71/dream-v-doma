@php
  use Illuminate\Support\Facades\DB;

  // 1) Читаємо налаштування трекінгу з БД
  $t = DB::table('tracking_settings')->first();

  // 2) Перевіряємо, що Pixel увімкнений, є pixel_id і ми не на адмін-URL
  $pixelOk = $t
    && (int)($t->pixel_enabled ?? 0) === 1
    && !empty($t->pixel_id)
    && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  // 3) Окремий тумблер для InitiateCheckout (send_initiate_checkout TINYINT(1))
  $allowIC = $pixelOk && (int)($t->send_initiate_checkout ?? 1) === 1;
@endphp

@if ($allowIC)
<script>
(function(){
  // Не перевизначати глобалку при повторних монтаннях SPA/турбо
  if (window.mpTrackIC) return;

  // Додатковий фронтовий прапорець: якщо _mpFlags.ic === false → взагалі не оголошуємо
  if (!window._mpFlags || window._mpFlags.ic === false) return;

  /* ========================== УТИЛІТИ ========================== */

  // Приведення до числа з двома знаками після коми
  function num(v){
    var s = String(v ?? 0).replace(',', '.').replace(/[^\d.\-]/g,'');
    var n = parseFloat(s);
    return Number.isFinite(n) ? Number(n.toFixed(2)) : 0;
  }

  // Зчитування cookie за ім'ям (нічого не створюємо)
  function getCookie(n){
    var m = document.cookie.match(new RegExp('(?:^|;\\s*)' + n + '=([^;]+)'));
    return m ? decodeURIComponent(m[1]) : null;
  }

  // Безпечний decodeURIComponent
  function safeDecode(c){ try { return c ? decodeURIComponent(c) : null } catch(_) { return c } }

  // Читання параметра з URL
  function getParam(name){
    var m = location.search.match(new RegExp('[?&]'+name+'=([^&]+)'));
    return m ? m[1] : null;
  }

  // ❗️Визначаємо FB-трафік (нічого не генеруємо)
  function isFacebookTraffic(){
    return !!(getCookie('_fbc') || getCookie('_fbp') || getParam('fbclid'));
  }

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
     Виклик із Vue (наприклад, на сторінці checkout), коли користувач
     відкриває/починає оформлення:

       window.mpTrackIC({
         items: [
           { variant_sku: 'PRD77-1234', price: 799.00, quantity: 1, name?: 'Назва' },
           ...
         ],
         currency?: 'UAH'
       })

     ВАЖЛИВО:
     • Використовуємо ТІЛЬКИ variant_sku як content_id (жодних product_id).
     • PII (емейл/телефон тощо) тут НЕ передаємо — це лише сигнал старту чекауту.
     • Дедуп з CAPI відбувається за однаковим event_id.
  =============================================================== */
  window.mpTrackIC = function(opts){
    try{
      // Мінімальна валідація вхідних даних
      if (!opts || !Array.isArray(opts.items) || !opts.items.length) {
        console.warn('[IC] no items passed');
        return;
      }

      // ❗️ШЛЕМО ТІЛЬКИ ДЛЯ FB-ТРАФІКУ
      if (!isFacebookTraffic()) return;

      // Будуємо contents[] і content_ids[] ТІЛЬКИ з variant_sku
      var contents = [];
      var content_ids = [];
      var total = 0;

      for (var i=0; i<opts.items.length; i++){
        var it = opts.items[i] || {};
        var id = (it.variant_sku ?? '').toString().trim();
        if (!id) {
          // Пропускаємо позиції без variant_sku, щоб не смітити івент
          console.warn('[IC] skip item without variant_sku', it);
          continue;
        }
        var qty = Number(it.quantity || 1); if (!Number.isFinite(qty) || qty <= 0) qty = 1;
        var price = num(it.price);

        contents.push({ id: id, quantity: qty, item_price: price });
        content_ids.push(id);
        total += qty * price;
      }

      // Якщо після фільтрації не лишилось жодної позиції — не відправляємо
      if (!contents.length) {
        console.warn('[IC] nothing to send (no valid variant_sku)');
        return;
      }

      var currency = opts.currency || (window.metaPixelCurrency || 'UAH');
      var value = num(total);

      // Спільний event_id для Pixel та CAPI → дедуплікація у Meta
      var icId  = genEventId('ic');

      /* ====================== 1) БРАУЗЕРНИЙ PIXEL ======================
         Ретраїмо до появи fbq (короткий backoff ~10с).
         PII тут НЕ передаємо — лише товарні дані.
      ================================================================== */
      (function sendPixel(attempt){
        attempt = attempt || 0;
        if (typeof window.fbq !== 'function') {
          if (attempt > 120) return; // ~10 сек @ ~80мс
          return setTimeout(function(){ sendPixel(attempt+1) }, 80);
        }
        try {
          fbq('track', 'InitiateCheckout', {
            content_ids: content_ids,
            content_type: 'product',
            contents: contents,
            num_items: contents.reduce(function(s,c){ return s + (Number(c.quantity)||0) }, 0),
            value: value,
            currency: currency
          }, { eventID: icId });
        } catch (_) {}
      })();

      /* ========================= 2) SERVER CAPI =========================
         Відправляємо ті ж дані на бек: /api/track/ic (TrackController@ic).
         На бекенді:
           - додаються user_data (IP, UA; за наявності PII — хешується SHA-256);
           - використовується той самий event_id для дедупу з Pixel.
      =================================================================== */
      var fbp = safeDecode(getCookie('_fbp')); // ЛИШЕ читаємо; не генеруємо
      var fbc = safeDecode(getCookie('_fbc')); // ЛИШЕ читаємо; не генеруємо

      var bodyObj = {
        event_id: icId,
        event_time: Math.floor(Date.now()/1000),
        event_source_url: window.location.href,

        // custom_data
        content_type: 'product',
        content_ids: content_ids,
        contents: contents,
        num_items: contents.reduce(function(s,c){ return s + (Number(c.quantity)||0) }, 0),
        value: value,
        currency: currency,

        // маркери для CAPI (можуть бути відсутні — бек це врахує)
        fbp: fbp || null,
        fbc: fbc || null
      };
      if (window._mpTestCode) bodyObj.test_event_code = window._mpTestCode;

      var body = JSON.stringify(bodyObj);

      // Надійна відправка під час навігації:
      // sendBeacon (де доступно) або fetch keepalive як фолбек
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
      // Failsafe: не ламаємо сторінку, лише попереджаємо в консолі
      console.warn('[IC] mpTrackIC exception', e);
    }
  };
})();
</script>
@endif
