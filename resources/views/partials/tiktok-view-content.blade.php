{{-- resources/views/partials/tiktok-view-content.blade.php --}}
@php
    /** @var \App\Models\Product $product */

    // 1) Основна валюта (fallback — UAH)
    $curr = $currency ?? 'UAH';

    // 2) Налаштування відстеження (опціонально, для валюти)
    $t = DB::table('tracking_settings')->first();

    // 3) Поточна локаль
    $locale = app()->getLocale() ?: 'uk';

    // 4) Вибір перекладу товару (спочатку поточна мова, потім uk, потім ru)
    $tr = ($product->translations ?? collect())
            ->firstWhere('locale', $locale)
        ?? ($product->translations ?? collect())->firstWhere('locale','uk')
        ?? ($product->translations ?? collect())->firstWhere('locale','ru');

    // 5) Основні дані для події ViewContent
    $contentId   = (string)($product->sku ?? $product->id ?? '');
    $contentName = (string)($tr->name ?? $product->name ?? '');
    $contentCat  = (string)($product->category->name ?? $product->category_name ?? '');
    $price       = isset($product->price) ? round((float)$product->price, 2) : null;
    $currency    = strtoupper($t?->default_currency ?? $curr ?? 'UAH');

    // 6) Формування payload для TikTok Pixel
    $payload = [
        'content_id'       => $contentId,
        'content_type'     => 'product',
        'content_name'     => $contentName,
        'content_category' => $contentCat,
        'value'            => $price,
        'currency'         => $currency,
        'contents'         => [
            [
                'content_id' => $contentId,
                'quantity'   => 1,
                'price'      => $price,
            ],
        ],
    ];

    // 7) Прибрати null-поля
    $payload = array_filter($payload, fn($v) => !is_null($v));
@endphp

@once
<script>
  // ── Guard: не дублювати ViewContent на сторінці
  if (!window._ttViewContentFired) {
    window._ttViewContentFired = true;

    const payload = {!! json_encode($payload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!};

    // ── Надсилання події ViewContent у TikTok Pixel
    (function fireTTQ() {
      if (window.ttq && typeof window.ttq.track === 'function') {
        try {
          window.ttq.track('ViewContent', payload);
        } catch (e) {
          console.warn('TikTok ViewContent error:', e);
        }
      } else {
        // Якщо ttq ще не ініціалізовано — спробувати після готовності
        document.addEventListener('ttq_ready', function onTtqReady() {
          try {
            window.ttq.track('ViewContent', payload);
          } catch (e) {
            console.warn('TikTok ViewContent (late) error:', e);
          }
        }, { once: true });
      }
    })();
  }
</script>
@endonce
