@php
  use Illuminate\Support\Facades\DB;

  // Налаштування
  $t = DB::table('tracking_settings')->first();

  $pixelOk = $t
    && (int)($t->pixel_enabled ?? 0) === 1
    && !empty($t->pixel_id)
    && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  $allowPurchase = $pixelOk && (int)($t->send_purchase ?? 1) === 1;

  // Чи слати CAPI з фронта на бек
  $capiOn = $allowPurchase && $t && (int)($t->capi_enabled ?? 0) === 1 && !empty($t->capi_token);

  $defaultCurrency = strtoupper(trim($t->default_currency ?? 'UAH'));
@endphp

@if ($allowPurchase)
  <script>window.metaPixelCurrency = "{{ $defaultCurrency }}";</script>

  <script>
  (function(){
    if (window.mpTrackPurchase) return; // одноразова ініціалізація

    // --- helpers ---
    function num(v){
      var s = String(v ?? 0).replace(',', '.').replace(/[^\d.\-]/g,'');
      var n = parseFloat(s);
      return Number.isFinite(n) ? Number(n.toFixed(2)) : 0;
    }
    function genEventId(name){
      try{
        var a = new Uint8Array(6);
        (window.crypto || window.msCrypto).getRandomValues(a);
        var hex = Array.from(a).map(function(b){ return b.toString(16).padStart(2,'0') }).join('');
        return name + '-' + hex + '-' + Math.floor(Date.now()/1000);
      }catch{
        return name + '-' + Math.random().toString(16).slice(2) + '-' + Math.floor(Date.now()/1000);
      }
    }
    function buildContents(items){
      var out = [];
      for (var i=0;i<(items||[]).length;i++){
        var it = items[i] || {};
        var id = (it.variant_sku ?? it.sku ?? it.id ?? '').toString().trim();
        if (!id) continue;
        var qty   = Number(it.quantity || 1); if (!Number.isFinite(qty) || qty <= 0) qty = 1;
        var price = num(it.price ?? it.item_price ?? 0);
        out.push({ id:id, quantity:qty, item_price:price });
      }
      return out;
    }

    /**
     * Викликати відразу після успішного створення замовлення/оплати:
     * window.mpTrackPurchase({
     *   order_number: 'A12345',                  // бажано
     *   items: [{ variant_sku, price, quantity, name? }, ...], // обов'язково
     *   value?: number, currency?: 'UAH', shipping?: number, tax?: number,
     *   // опціонально (для кращого матчінгу; бек хешує):
     *   email?: string, phone?: string, first_name?: string, last_name?: string, external_id?: string
     * })
     */
    window.mpTrackPurchase = function(opts){
      try{
        if (!opts || !Array.isArray(opts.items) || !opts.items.length) return;

        var contents = buildContents(opts.items);
        if (!contents.length) return;

        var ids      = contents.map(function(c){ return c.id });
        var qtySum   = contents.reduce(function(s,c){ return s + (Number(c.quantity)||0) }, 0);
        var subtotal = contents.reduce(function(s,c){ return s + num(c.item_price) * (Number(c.quantity)||0) }, 0);

        var currency = (opts.currency || window.metaPixelCurrency || 'UAH').toString().trim().toUpperCase();
        var shipping = num(opts.shipping || 0);
        var tax      = num(opts.tax || 0);
        var value    = (typeof opts.value === 'number') ? num(opts.value) : num(subtotal + shipping + tax);

        var orderNo  = opts.order_number ? String(opts.order_number) : null;
        var eventId  = genEventId('purchase');

        // локальний anti-dup за order_number
        if (orderNo) {
          var guardKey = 'purchase_sent_' + orderNo;
          if (localStorage.getItem(guardKey) === '1') return;
          localStorage.setItem(guardKey, '1');
        }

        // 1) Browser Pixel — чекаємо fbq до ~2с (25 * 80мс)
        (function wait(i){
          i = i || 0;
          if (typeof window.fbq === 'function') {
            try{
              fbq('track', 'Purchase', {
                content_ids: ids,
                content_type: 'product',
                contents: contents,
                num_items: qtySum,
                value: value,
                currency: currency,
                shipping: shipping,
                tax: tax
              }, { eventID: eventId });
            }catch(_){}
            return;
          }
          if (i >= 25) return;
          setTimeout(function(){ wait(i+1); }, 80);
        })();

        // 2) Server (CAPI) — той самий event_id; бек сам додасть event_time/url/test_code
        @if ($capiOn)
        (function(){
          var body = JSON.stringify({
            event_id: eventId,
            page_url: location.href,
            // custom_data core:
            currency: currency,
            contents: contents,
            content_ids: ids,    // зручно бекові для швидкого доступу
            num_items: qtySum,
            value: value,
            shipping: shipping,
            tax: tax,
            order_number: orderNo,
            // PII (опціонально — бек їх хешує):
            email: opts.email || null,
            phone: opts.phone || null,
            first_name: opts.first_name || null,
            last_name: opts.last_name || null
          });

          var sent = false;
          if (navigator.sendBeacon) {
            try { sent = navigator.sendBeacon('/api/track/purchase', new Blob([body], {type:'application/json'})); } catch(_){}
          }
          if (!sent) {
            try {
              fetch('/api/track/purchase', {
                method: 'POST',
                keepalive: true,
                headers: { 'Content-Type': 'application/json' },
                body
              }).catch(function(){});
            } catch(_){}
          }
        })();
        @endif

        // (опціонально) GA4 purchase — за потреби можемо додати окремий хук

      } catch(_){}
    };
  })();
  </script>
@endif
