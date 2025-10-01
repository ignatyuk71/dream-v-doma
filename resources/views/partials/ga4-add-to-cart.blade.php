<script>
  // Єдина точка входу для GA4 AddToCart.
  // Викликається з твого коду: window.ga4AddToCart({...})
  window.ga4AddToCart = function (opts) {
    try {
      if (!opts) return;

      // Нормалізація числа (ціни)
      function num(v){
        var s = String(v ?? 0).replace(',', '.').replace(/[^\d.\-]/g,'');
        var n = parseFloat(s);
        return Number.isFinite(n) ? Number(n.toFixed(2)) : 0;
      }

      var items = [];
      var total = 0;

      // Підтримка масиву contents[] або одиничного товару
      if (Array.isArray(opts.contents) && opts.contents.length) {
        for (var i=0;i<opts.contents.length;i++){
          var r   = opts.contents[i] || {};
          var id  = (r.id ?? '').toString().trim();
          if (!id) continue;

          var qty = Number(r.quantity ?? 1);
          if (!Number.isFinite(qty) || qty <= 0) qty = 1;

          var prc = num(r.item_price ?? r.price ?? 0);

          items.push({
            item_id:   id,
            item_name: (opts.name || ''), // за потреби можна передавати назву для кожного айтема окремо
            price:     prc,
            quantity:  qty
          });

          total += prc * qty;
        }
      } else {
        // Фолбек: одиничний товар
        var pid = (opts.variant_sku ?? opts.sku ?? opts.id ?? '').toString().trim();
        if (!pid) return;

        var qty = Number(opts.quantity ?? 1);
        if (!Number.isFinite(qty) || qty <= 0) qty = 1;

        var prc = num(opts.price);

        items.push({
          item_id:   pid,
          item_name: (typeof opts.name === 'string' ? opts.name : ''),
          price:     prc,
          quantity:  qty
        });

        total += prc * qty;
      }

      var currency = (opts.currency || window.metaPixelCurrency || 'UAH')
                      .toString().trim().toUpperCase();

      // GA4 рекомендація: очищати попередній ecommerce-стан перед новим пушем
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({ ecommerce: null });

      // Подія GA4: add_to_cart
      window.dataLayer.push({
        event: "add_to_cart",
        ecommerce: {
          currency: currency,
          value: Number(total.toFixed(2)),
          items: items
        }
      });
    } catch (_) {}
  };
</script>
