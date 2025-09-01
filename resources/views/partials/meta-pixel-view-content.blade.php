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
  // вимкнуто прапорцем з глобального паршала — не шлемо
  if (window._mpFlags && window._mpFlags.vc === false) return;
  if (!window.fbq) return;

  var pid = String(@json($product->sku ?? $product->id ?? ''));
  if (!pid) return; // без id/sku немає сенсу шити VC

  var name = @json($product->name ?? $product->title ?? '');
  var rawPrice = @json($product->price ?? 0);

  // нормалізація ціни
  var price = (function (p) {
    var s = String(p).replace(',', '.').replace(/[^\d.]/g, '');
    var n = parseFloat(s);
    return Number.isFinite(n) ? Number(n.toFixed(2)) : 0;
  })(rawPrice);

  var currency = window.metaPixelCurrency || 'UAH';

  // спільний event_id для дедупу VC
  var vcId = window._mpVCId || (window._mpVCId = (typeof window._mpGenEventId === 'function'
               ? window._mpGenEventId('vc')
               : ('vc-' + Math.random().toString(16).slice(2) + '-' + Date.now())));

  // -------- БРАУЗЕРНИЙ VC --------
  var pixelPayload = {
    content_ids: [pid],
    content_type: 'product',
    content_name: name,
    value: price,
    currency: currency
  };
  fbq('track', 'ViewContent', pixelPayload, { eventID: vcId });

  // -------- CAPI VC (той самий event_id) --------
  try {
    var getCookie = window._mpGetCookie || function(n){
      return document.cookie.split('; ').find(function(r){ return r.indexOf(n + '=') === 0 })?.split('=')[1] || null;
    };
    var safeDecode = function(c){ try { return c ? decodeURIComponent(c) : null } catch(_) { return c } };

    var fbp = safeDecode(getCookie('_fbp'));
    var fbc = safeDecode(getCookie('_fbc'));

    var capiBody = {
      event_id: vcId,
      event_time: Math.floor(Date.now()/1000),
      event_source_url: window.location.href,
      currency: currency,
      contents: [{ id: pid, quantity: 1, item_price: price }],
      // value не передаємо — бек сам по contents рахує
      fbp: fbp,
      fbc: fbc,
      content_name: name // бек це підхопить як custom_data.content_name
    };

    if (window._mpTestCode) capiBody.test_event_code = window._mpTestCode;

    var body = JSON.stringify(capiBody);
    if (navigator.sendBeacon) {
      var blob = new Blob([body], {type: 'application/json'});
      navigator.sendBeacon('/api/track/vc', blob);
    } else {
      fetch('/api/track/vc', { method:'POST', headers:{'Content-Type':'application/json'}, body, keepalive:true });
    }
  } catch (_) {}
})();
</script>
@endif
