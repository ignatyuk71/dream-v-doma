@php
  // читаємо налаштування лише з БД
  $t = \Illuminate\Support\Facades\DB::table('tracking_settings')->first();

  $pixelOk = $t
    && (int)($t->pixel_enabled ?? 0) === 1
    && !empty($t->pixel_id)
    && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  $allowVC = $pixelOk && (int)($t->send_view_content ?? 1) === 1;
@endphp

@if ($allowVC && isset($product))
<script>
(function () {
  // вимкнуто прапорцем з паршала пікселя — не шлемо
  if (window._mpFlags && window._mpFlags.vc === false) return;
  if (!window.fbq) return;

  var pid = String(@json($product->sku ?? $product->id));
  var name = @json($product->name ?? $product->title ?? '');
  var rawPrice = @json($product->price ?? 0);

  var price = (function (p) {
    var s = String(p).replace(',', '.').replace(/[^\d.]/g, '');
    var n = parseFloat(s);
    return Number.isFinite(n) ? Number(n.toFixed(2)) : 0;
  })(rawPrice);

  var payload = {
    content_ids: [pid],
    content_type: 'product',
    content_name: name,
    value: price,
    currency: window.metaPixelCurrency || 'UAH'
  };

  console.log('[MetaPixel] ViewContent', payload);
  fbq('track', 'ViewContent', payload);
})();
</script>
@endif
