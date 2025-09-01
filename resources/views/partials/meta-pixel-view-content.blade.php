@if (isset($product))
<script>
(function () {
  // прості гейти
  if (!window._mpEnabled) return;
  if (window._mpFlags && window._mpFlags.vc === false) return;
  if (!window.fbq) return; // піксель має бути ініціалізований у meta-pixel-script.blade.php

  var pid      = String(@json($product->sku ?? $product->id));
  var name     = @json($product->name ?? $product->title ?? '');
  var rawPrice = @json($product->price ?? 0);
  var currency = window.metaPixelCurrency || 'UAH';

  // нормалізація ціни
  var price = (function (p) {
    var s = String(p).replace(',', '.').replace(/[^\d.]/g, '');
    var n = parseFloat(s);
    return Number.isFinite(n) ? Number(n.toFixed(2)) : 0;
  })(rawPrice);

  // суто браузерний VC
  fbq('track', 'ViewContent', {
    content_ids: [pid],
    content_type: 'product',
    content_name: name,
    value: price,
    currency: currency
  }, {
    // щоб у Test Events було легше впізнати, даємо унікальний eventID (не для дедупу)
    eventID: 'vc-browser-' + Date.now()
  });
})();
</script>
@endif
