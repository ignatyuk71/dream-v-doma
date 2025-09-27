@php
  use Illuminate\Support\Facades\DB;

  $t = DB::table('tracking_settings')->first();

  $pixelOk = $t
    && (int)($t?->pixel_enabled ?? 0) === 1
    && !empty($t?->pixel_id)
    && !((int)($t?->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  $allowVC = $pixelOk && (int)($t?->send_view_content ?? 1) === 1;

  $locale = app()->getLocale() ?: 'uk';
  $tr = ($product->translations ?? collect())
        ->firstWhere('locale', $locale)
        ?? ($product->translations ?? collect())->firstWhere('locale', 'uk')
        ?? ($product->translations ?? collect())->firstWhere('locale', 'ru')
        ?? null;

  $translatedName = $tr->name ?? '';
  $currency = $t?->default_currency ?? 'UAH';

  // Очікуваний URL цієї сторінки продукту
  $productUrl = url()->current();
@endphp

@if ($allowVC && isset($product))
<script>
(function () {
  if (window._sentVC) return;
  window._sentVC = true;

  // --- helper: нормалізація URL (без query/hash, без кінцевого слеша)
  function norm(u){
    try {
      var x = new URL(u, location.origin);
      var p = x.pathname.replace(/\/+$/,''); // обрізаємо кінцевий /
      return x.origin + p;
    } catch (_){
      // fallback
      return String(u).replace(/[?#].*$/,'').replace(/\/+$/,'');
    }
  }

  // Перевірка, що ми справді на сторінці цього продукту
  var expected = norm(@json($productUrl));
  var current  = norm(location.href);
  if (current !== expected) return; // не шлемо, якщо URL не збігається

  // Дані товару
  var pid = String(@json($product->sku ?? $product->id ?? ''));
  if (!pid) return;

  var name     = @json($translatedName);
  var rawPrice = @json($product->price ?? 0);
  var currency = @json($currency);

  var price = (function (p) {
    var s = String(p).replace(',', '.').replace(/[^\d.]/g, '');
    var n = parseFloat(s);
    return Number.isFinite(n) ? Number(n.toFixed(2)) : 0;
  })(rawPrice);

  var contents = [{ id: pid, quantity: 1, item_price: price }];

  // чекаємо fbq і відправляємо VC
  (function sendVC(attempt){
    attempt = attempt || 0;
    if (typeof window.fbq !== 'function') {
      if (attempt > 60) return; // ~5 сек
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
