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
  // ▸ Не переоголошувати у SPA
  if (window.mpTrackATC) return;

  // ▸ Глобальний вимикач через _mpFlags.atc (якщо є)
  var atcEnabled = !(window._mpFlags && window._mpFlags.atc === false);

  /* ========================== УТИЛІТИ ========================== */
  function num(v){
    var s = String(v ?? 0).replace(',', '.').replace(/[^\d.\-]/g,'');
    var n = parseFloat(s);
    return Number.isFinite(n) ? Number(n.toFixed(2)) : 0;
  }

  /* ====================== ГОЛОВНА ФУНКЦІЯ ======================
     Викликати у момент реального додавання в кошик:

     window.mpTrackATC({
       variant_sku: 'PRD77-1234', // ⚠️ обов’язково: content_id
       price: 799.00,              // ціна за одиницю
       quantity?: 1,               // (опц.) кількість
       name?: 'Назва товару',      // (опц.) назва
       currency?: 'UAH'            // (опц.) валюта (дефолт з window.metaPixelCurrency або 'UAH')
     })
  =============================================================== */
  window.mpTrackATC = function(opts){
    try{
      if (!opts || !atcEnabled) return;

      // ---------- GA4 (залишаємо як просили) ----------
      if (typeof window.ga4AddToCart === 'function') {
        window.ga4AddToCart(opts);
      }

      // ---------- Валідація ----------
      var pid = (opts.variant_sku ?? '').toString().trim();
      if (!pid) {
        console.warn('[ATC] Пропущено — немає variant_sku');
        return;
      }

      // ---------- Підготовка даних для Pixel ----------
      var qty = Number(opts.quantity || 1);
      if (!Number.isFinite(qty) || qty <= 0) qty = 1;

      var price    = num(opts.price);
      var name     = typeof opts.name === 'string' ? opts.name : '';
      var currency = (opts.currency || window.metaPixelCurrency || 'UAH').toString().trim().toUpperCase();

      var contents = [{ id: pid, quantity: qty, item_price: price }];
      var value    = num(qty * price);

      // ---------- Надсилання тільки у Pixel ----------
      (function sendPixel(attempt){
        attempt = attempt || 0;
        if (typeof window.fbq !== 'function') {
          if (attempt > 60) return; // ~5 сек очікування підвантаження fbevents.js
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
          });
        } catch(_) {}
      })();

    } catch(e){
      console.warn('[ATC] mpTrackATC exception', e);
    }
  };
})();
</script>
@endif
