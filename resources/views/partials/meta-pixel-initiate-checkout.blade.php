@php
  use Illuminate\Support\Facades\DB;

  // 1) Читаємо налаштування трекінгу з БД
  $t = DB::table('tracking_settings')->first();

  // 2) Перевіряємо, що Pixel увімкнений, є pixel_id і ми не на адмін-URL
  $pixelOk = $t
    && (int)($t->pixel_enabled ?? 0) === 1
    && !empty($t->pixel_id)
    && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  // 3) Окремий тумблер для InitiateCheckout
  $allowIC = $pixelOk && (int)($t->send_initiate_checkout ?? 1) === 1;
@endphp

@if ($allowIC)
<script>
(function(){
  if (window.mpTrackIC) return;                     // захист від повторного оголошення
  if (!window._mpFlags || window._mpFlags.ic === false) return;

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

  // Зчитування cookie (нічого не створюємо)
  function getCookie(n){
    var m = document.cookie.match(new RegExp('(?:^|;\\s*)' + n + '=([^;]+)'));
    return m ? decodeURIComponent(m[1]) : null;
  }


  // Генератор event_id (спільний для Pixel і CAPI → дедуп)
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
     Виклик із UI, коли юзер починає оформлення:

       window.mpTrackIC({
         items: [
           { variant_sku: 'PRD-001', price: 799.00, quantity: 1, name: 'Назва 1' },
           { variant_sku: 'PRD-002', price: 399.00, quantity: 2, name: 'Назва 2' }
         ],
         currency: 'UAH' // опціонально
       })

     Ноти:
     • content_id = ТІЛЬКИ variant_sku (жодних product_id).
     • PII не передаємо з фронта; бек сам додасть user_data.
     • Один event_id для Pixel і CAPI → дедуп у Meta.
  =============================================================== */
  window.mpTrackIC = function(opts){
    try{
      if (!opts || !Array.isArray(opts.items) || !opts.items.length) return;
   

      // Будуємо contents[] / content_ids[] тільки з валідних variant_sku
      var contents = [];
      var content_ids = [];
      var total = 0;

      for (var i=0; i<opts.items.length; i++){
        var it = opts.items[i] || {};
        var id = (it.variant_sku ?? '').toString().trim();
        if (!id) continue; // пропустити позиції без SKU варіанта

        var qty   = Number(it.quantity || 1); if (!Number.isFinite(qty) || qty <= 0) qty = 1;
        var price = num(it.price);

        contents.push({ id:id, quantity:qty, item_price:price });
        content_ids.push(id);
        total += qty * price;
      }

      if (!contents.length) return;

      // ✅ Приводимо валюту до UPPERCASE (узгоджено з беком)
      var currency    = (opts.currency || window.metaPixelCurrency || 'UAH')
                          .toString().trim().toUpperCase();
      var value       = num(total);
      var icId        = genEventId('ic'); // спільний event_id

      // Рахуємо num_items один раз і використовуємо в обох відправках
      var numItems = contents.reduce(function(s,c){ return s + (Number(c.quantity)||0) }, 0);

      // (опц.) content_name — візьмемо перший валідний name
      var contentName = '';
      for (var j=0; j<opts.items.length; j++){
        var it2 = opts.items[j] || {};
        if ((it2.variant_sku ?? '').toString().trim() && typeof it2.name === 'string' && it2.name.trim()){
          contentName = it2.name.trim();
          break;
        }
      }

      /* ====================== 1) БРАУЗЕРНИЙ PIXEL ======================
         Ретраїмо до 5 секунд, якщо fbq ще не доступний.
      ================================================================== */
      (function sendPixel(attempt){
        attempt = attempt || 0;
        if (typeof window.fbq !== 'function') {
          if (attempt > 60) return; // ~5 секунд
          return setTimeout(function(){ sendPixel(attempt+1) }, 80);
        }
        try {
          fbq('track', 'InitiateCheckout', {
            content_ids: content_ids,
            content_type: 'product',
            contents: contents,
            num_items: numItems,
            value: value,
            currency: currency,
            ...(contentName ? { content_name: contentName } : {})
          }, { eventID: icId });
        } catch (_) {}
      })();

      /* ========================= 2) SERVER CAPI =========================
         Надсилаємо ті ж custom_data на бек:
         - бек додасть user_data (IP, UA, fbc/fbp з cookies, PII-хеші за потреби)
         - використає той самий event_id для дедупу
      =================================================================== */
      var bodyObj = {
        event_id: icId,
        event_time: Math.floor(Date.now()/1000),
        event_source_url: window.location.href,

        content_type: 'product',
        content_ids: content_ids,
        contents: contents,
        num_items: numItems,
        value: value,
        currency: currency
      };
      if (contentName) bodyObj.content_name = contentName;
      if (window._mpTestCode) bodyObj.test_event_code = window._mpTestCode;

      var body = JSON.stringify(bodyObj);

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
      console.warn('[IC] mpTrackIC exception', e);
    }
  };
})();
</script>
@endif
