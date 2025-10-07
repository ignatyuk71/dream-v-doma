{{-- resources/views/partials/tiktok-view-content.blade.php --}}
@php
  /** @var \App\Models\Product $product */
  $loc = app()->getLocale();

  // Локалізована назва
  $tr = $product->translations->firstWhere('locale', $loc);
  $productName = $tr?->name ?? $product->sku;

  // Категорія (беремо поточну, якщо передана з контролера; інакше першу)
  $cat = isset($category) ? $category : ($product->categories->first());
  $catTr = $cat?->translations?->firstWhere('locale', $loc);
  $categoryName = $catTr?->name ?? null;

  // Валюта (підправ за своєю логікою, якщо треба)
  $currency = match($loc) {
      'pl' => 'PLN',
      default => 'UAH',
  };

  $vc = [
    'sku'      => (string) $product->sku,
    'name'     => (string) $productName,
    'category' => $categoryName ?: null,
    'price'    => (float) $product->price,
    'currency' => $currency,
  ];
@endphp

@if(isset($product))
<script>
(function () {
  if (window._ttVcFired) return;
  window._ttVcFired = true;

  const P = @json($vc, JSON_UNESCAPED_UNICODE);

  const contentId = P.sku;
  const itemPrice = Number(P.price);

  const payload = {
    content_id: contentId,
    content_type: 'product',
    content_name: P.name,
    content_category: P.category || undefined,
    value: itemPrice,
    currency: P.currency,
    contents: [{
      content_id: contentId,
      content_type: 'product',
      content_name: P.name,
      quantity: 1,
      price: itemPrice
    }]
  };

  if (window.ttq && typeof window.ttq.track === 'function') {
    ttq.track('ViewContent', payload);
  } else {
    console.warn('[TikTok] ttq не знайдений — ViewContent не надіслано', payload);
  }

  console.log('[TikTok] ViewContent payload', payload);
})();
</script>
@endif
