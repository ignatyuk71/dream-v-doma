@php
  use Illuminate\Support\Facades\DB;

  // Налаштування трекінгу (може бути null)
  $t = DB::table('tracking_settings')->first();

  // Pixel увімкнено, є pixel_id і не адмін-URL
  $pixelOk = $t
    && (int)($t?->pixel_enabled ?? 0) === 1
    && !empty($t?->pixel_id)
    && !((int)($t?->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  // Тумблер для ViewContent
  $allowVC = $pixelOk && (int)($t?->send_view_content ?? 1) === 1;

  // Локаль і перекладена назва товару
  $locale = app()->getLocale() ?: 'uk';
  $tr = ($product->translations ?? collect())
        ->firstWhere('locale', $locale)
        ?? ($product->translations ?? collect())->firstWhere('locale', 'uk')
        ?? ($product->translations ?? collect())->firstWhere('locale', 'ru')
        ?? null;
  $translatedName = $tr->name ?? '';
  $currency = $t?->default_currency ?? 'UAH';
@endphp

@if ($allowVC && isset($product))
<script>
(function () {
  if (window._sentVC) return; // захист від дубляжу
  window._sentVC = true;

  // id товару: SKU або ID
  var pid = String(@json($product->sku ?? $product->id ?? ''));
  if (!pid) return;

  var name     = @json($translatedName);
  var rawPrice = @json($product->price ?? 0);
  var currency = @json($currency);

  // нормалізація ціни
  var price = (function (p) {
    var s = String(p).replace(',', '.').replace(/[^\d.]/g, '');
    var n = parseFloat(s);
    return Number.isFinite(n) ? Number(n.toFixed(2)) : 0;
  })(rawPrice);

  var contents = [{ id: pid, quantity: 1, item_price: price }];

  // чекаємо fbq та відправляємо ViewContent
  (function sendVC(attempt){
    attempt = attempt || 0;
    if (typeof window.fbq !== 'function') {
      if (attempt > 60) return; // ~5 cек
      return setTimeout(function(){ sendVC(attempt+1); }, 80);
    }
    try {
      fbq('track', 'ViewContent', {
        content_ids: [pid],
        content_type: 'product',
        contents: contents,
        content_name: name,
        value: price,
        currency: currency
      });
    } catch(_) {}
  })();
})();
</script>
@endif
