@php
  use Illuminate\Support\Facades\DB;

  $t = DB::table('tracking_settings')->first();

  $pixelOk = $t
    && (int)($t->pixel_enabled ?? 0) === 1
    && !empty($t->pixel_id)
    && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  $allowVC = $pixelOk && (int)($t->send_view_content ?? 1) === 1;

  $locale = app()->getLocale() ?: 'uk';
  $tr = ($product->translations ?? collect())
          ->firstWhere('locale', $locale)
        ?? ($product->translations ?? collect())->firstWhere('locale','uk')
        ?? ($product->translations ?? collect())->firstWhere('locale','ru');

  $contentId   = (string)($product->sku ?? $product->id ?? '');
  $contentName = (string)($tr->name ?? $product->name ?? '');
  $contentCat  = (string)($product->category->name ?? $product->category_name ?? '');
  $price       = isset($product->price) ? round((float)$product->price, 2) : null;
  $currency    = strtoupper($t?->default_currency ?? 'UAH');

  $capiEnabled = $t && (int)($t->capi_enabled ?? 0) === 1 && !empty($t->capi_token);
  $sendCapiVc  = $allowVC && $capiEnabled;
@endphp

@if ($allowVC && isset($product) && $contentId !== '')
<script>
;(function () {
  // антидубль на конкретний товар
  window._vcFired = window._vcFired || {};
  if (window._vcFired[@json($contentId)]) return;

  // --- фільтр: усе, крім TikTok
  function _mp_isTikTokTraffic() {
    var q   = (location.search   || '').toLowerCase();
    var ref = (document.referrer || '').toLowerCase();
    var ua  = (navigator.userAgent|| '').toLowerCase();
    return q.indexOf('ttclid=') !== -1 || ref.indexOf('tiktok') !== -1 || ua.indexOf('tiktok') !== -1;
  }
  if (_mp_isTikTokTraffic()) return;

  window._vcFired[@json($contentId)] = true;

  // спільний eventID для дедуплікації
  var vcEventId = 'vc-' + Math.random().toString(16).slice(2) + '-' + Date.now();

  // дані події
  var data = {
    content_type: 'product',
    content_ids: [@json($contentId)],
    contents: [{ id: @json($contentId), quantity: 1 }]
  };
  @if($contentName !== '') data.content_name = @json($contentName); @endif
  @if($contentCat  !== '') data.content_category = @json($contentCat); @endif
  @if(!is_null($price)) {
    data.contents[0].item_price = {{ $price }};
    data.value = {{ $price }};
    data.currency = @json($currency);
  } @endif

  // браузерний ViewContent (чекаємо fbq, якщо ще завантажується)
  (function waitFbq(i){
    if (typeof window.fbq === 'function') {
      try { fbq('track', 'ViewContent', data, { eventID: vcEventId }); } catch(e){}
      return;
    }
    if (i > 15) return;
    setTimeout(function(){ waitFbq(i+1); }, 80);
  })(0);

  // CAPI з невеликою затримкою
  @if ($sendCapiVc)
  setTimeout(function(){
    var body = JSON.stringify({
      event_id: vcEventId,
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

    var sent=false;
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
  }, 1500);
  @endif
})();
</script>
@endif
ы