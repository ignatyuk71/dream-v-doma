@php
  use Illuminate\Support\Facades\DB;

  $t = DB::table('tracking_settings')->first();

  $pixelOk = $t
    && (int)($t->pixel_enabled ?? 0) === 1
    && !empty($t->pixel_id)
    && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  $allowVC = $pixelOk && (int)($t->send_view_content ?? 1) === 1;
  $currency = $t->default_currency ?? 'UAH';

  // URL поточної сторінки товару (з урахуванням локалі/слугу)
  $productUrl = url()->current();
@endphp

@if ($allowVC && isset($product))
<script>
(function(){
  // не дублюємо у SPA
  if (window.__vcSentOnce) return;
  window.__vcSentOnce = true;

  // дані товару
  var pid   = @json($product->sku ?? $product->id);
  var price = Number(String(@json($product->price ?? 0)).replace(',', '.').replace(/[^\d.]/g,''));
  var curr  = @json($currency);
  var expectedUrl = @json($productUrl); // те, що Blade бачить як URL товару

  function sendVC(){
    // 1) fbq готовий?
    if (typeof window.fbq !== 'function') { return setTimeout(sendVC, 80); }

    // 2) ми точно на URL товару? (захист від запуску на головній/категорії)
    if (location.href.indexOf(expectedUrl) !== 0) {
      return; // не шлемо, якщо URL не співпадає
    }

    try {
      fbq('track', 'ViewContent', {
        content_ids: [String(pid)],
        content_type: 'product',
        value: Number.isFinite(price) ? +price.toFixed(2) : 0,
        currency: curr
      });
      // (опц.) дебаг у Test Events
      // console.log('[FB Pixel] VC sent @', location.href, {pid, price, curr});
    } catch(_) {}
  }

  // чекаємо DOM і один тик черги — щоб URL точно був уже продукту (важливо для SPA/pushState)
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function(){ setTimeout(sendVC, 0); });
  } else {
    setTimeout(sendVC, 0);
  }
})();
</script>
@endif
