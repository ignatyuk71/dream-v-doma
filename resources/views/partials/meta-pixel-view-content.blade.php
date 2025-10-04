@php
  use Illuminate\Support\Facades\DB;

  // 1) Тягнемо налаштування трекінгу з БД
  $t = DB::table('tracking_settings')->first();

  // 2) Чи увімкнено Pixel і є pixel_id; не стріляємо на admin*, якщо exclude_admin = 1
  $pixelOk = $t
    && (int)($t->pixel_enabled ?? 0) === 1
    && !empty($t->pixel_id)
    && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  // 3) Дозвіл саме на ViewContent
  $allowVC = $pixelOk && (int)($t->send_view_content ?? 1) === 1;

  // 4) Локалізовані дані товару
  $locale = app()->getLocale() ?: 'uk';
  $tr = ($product->translations ?? collect())
          ->firstWhere('locale', $locale)
        ?? ($product->translations ?? collect())->firstWhere('locale','uk')
        ?? ($product->translations ?? collect())->firstWhere('locale','ru');

  // 5) Поля події
  $contentId   = (string)($product->sku ?? $product->id ?? '');
  $contentName = (string)($tr->name ?? $product->name ?? '');
  $contentCat  = (string)($product->category->name ?? $product->category_name ?? '');
  $price       = isset($product->price) ? round((float)$product->price, 2) : null;
  $currency    = strtoupper($t?->default_currency ?? 'UAH');

  // 6) CAPI: прапорець + токен
  $capiEnabled = $t && (int)($t->capi_enabled ?? 0) === 1 && !empty($t->capi_token);
  $sendCapiVc  = $allowVC && $capiEnabled;
@endphp

{{-- 7) Рендеримо скрипт тільки якщо: VC дозволено, є продукт і є contentId --}}
@if ($allowVC && isset($product) && $contentId !== '')
<script>
(function(){
  // 8) Антидубль на конкретний товар
  window._vcFired = window._vcFired || {};
  if (window._vcFired['{{ $contentId }}']) return;
  window._vcFired['{{ $contentId }}'] = true;

  // 9) Спільний eventID для fbq і CAPI
  var vcEventId = 'vc-' + Math.random().toString(16).slice(2) + '-' + Date.now();

  // 10) Дані події
  var data = {
    content_type: 'product',
    content_ids: [@json($contentId)],
    contents: [{ id: @json($contentId), quantity: 1 }]
  };

  // 11) Додаємо назву/категорію
  @if($contentName !== '') data.content_name = @json($contentName); @endif
  @if($contentCat  !== '') data.content_category = @json($contentCat); @endif

  // 12) Ціна/валюта (якщо є)
  @if(!is_null($price)) {
    data.contents[0].item_price = {{ $price }};
    data.value = {{ $price }};
    data.currency = @json($currency);
  } @endif

  // 13) БРАУЗЕРНИЙ ViewContent — БЕЗ затримки
  (function waitFbq(i){
    if (typeof window.fbq === 'function') {
      try {
        fbq('track', 'ViewContent', data, { eventID: vcEventId });
      } catch(e){}
      return;
    }
    if (i > 25) return; // ~5с
    setTimeout(function(){ waitFbq(i+1); }, 80);
  })(0);

  // 14) CAPI — ЗАТРИМКА 1s ТІЛЬКИ ДЛЯ СЕРВЕРНОГО ВІДПРАВЛЕННЯ
  @if ($sendCapiVc)
  (function(){
    var DELAY_MS = 1500; // ← тільки CAPI
    setTimeout(function(){
      var body = JSON.stringify({
        event_id: vcEventId,      // той самий для дедуплікації
        page_url: location.href,
        product: {
          id: @json((string)($product->id ?? '')),
          sku: @json((string)($product->sku ?? '')),
          name: @json($contentName),
          category: @json($contentCat),
          price: @json($price),
          currency: @json($currency)
        }
      });

      var sent = false;
      if (navigator.sendBeacon) {
        try { sent = navigator.sendBeacon('/api/track/vc', new Blob([body], {type:'application/json'})); } catch(e){}
      }
      if (!sent) {
        try {
          fetch('/api/track/vc', {
            method: 'POST',
            keepalive: true,
            headers: { 'Content-Type': 'application/json' },
            body
          }).catch(function(){});
        } catch(e){}
      }
    }, DELAY_MS);
  })();
  @endif
})();
</script>
@endif
