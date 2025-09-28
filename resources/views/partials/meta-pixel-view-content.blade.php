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

  $pid      = (string)($product->sku ?? $product->id ?? '');
  $name     = (string)($tr->name ?? $product->name ?? '');
  $price    = round((float)($product->price ?? 0), 2);       // ✅ порахували в PHP
  $currency = $t?->default_currency ?? 'UAH';
@endphp

@if ($allowVC && isset($product) && $pid !== '')
<script>
(function(){
  if (window._vcOnce) return; window._vcOnce = true;

  var pid      = @json($pid);
  var name     = @json($name);
  var price    = @json($price);        // ✅ вже число
  var currency = @json($currency);

  var contents = [{ id: pid, quantity: 1, item_price: price }];

  function fireVC(){
    try {
      fbq('track', 'ViewContent', {
        content_ids: [pid],
        content_type: 'product',
        contents: contents,
        content_name: name,
        value: price,
        currency: currency
      });
    } catch(e) {}
  }

  (function wait(i){
    if (typeof window.fbq === 'function') return fireVC();
    if (i > 60) return;
    setTimeout(function(){ wait(i+1); }, 80);
  })(0);
})();
</script>
@endif
