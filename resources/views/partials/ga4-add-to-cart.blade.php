@if(isset($product))
<script>
  // Єдина функція для GA4 ATC. Без слухачів, без викликів — лише push.
  window.ga4AddToCart = function (opts) {
    try {
      if (!opts) return;

      function num(v){
        var s = String(v ?? 0).replace(',', '.').replace(/[^\d.\-]/g,'');
        var n = parseFloat(s);
        return Number.isFinite(n) ? Number(n.toFixed(2)) : 0;
      }

      var pid      = (opts.variant_sku ?? '').toString().trim();
      if (!pid) return;

      var qty      = Number(opts.quantity || 1);
      if (!Number.isFinite(qty) || qty <= 0) qty = 1;

      var price    = num(opts.price);
      var name     = typeof opts.name === 'string' ? opts.name : '';
      var currency = opts.currency || (window.metaPixelCurrency || 'UAH');
      var value    = num(qty * price);

      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({
        event: "add_to_cart",
        ecommerce: {
          currency: currency,
          value: value,
          items: [{
            item_id: pid,
            item_name: name,
            price: price,
            quantity: qty
          }]
        }
      });
    } catch (_) {}
  };
</script>
@endif
