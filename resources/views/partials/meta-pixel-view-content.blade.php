@php
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
  if (window._mpFlags && window._mpFlags.vc === false) return;
  if (!window.fbq) return;

  var pid   = String(@json($product->sku ?? $product->id ?? ''));
  if (!pid) return;

  var name      = @json($product->name ?? $product->title ?? '');
  var rawPrice  = @json($product->price ?? 0);
  var currency  = window.metaPixelCurrency || 'UAH';

  var price = (function (p) {
    var s = String(p).replace(',', '.').replace(/[^\d.]/g, '');
    var n = parseFloat(s);
    return Number.isFinite(n) ? Number(n.toFixed(2)) : 0;
  })(rawPrice);

  var contents = [{ id: pid, quantity: 1, item_price: price }];

  // один і той самий ID для дедупу
  var vcId = window._mpVCId || (window._mpVCId = (typeof window._mpGenEventId === 'function'
              ? window._mpGenEventId('vc')
              : ('vc-' + Math.random().toString(16).slice(2) + '-' + Date.now())));

  // ---------- БРАУЗЕРНИЙ VC (повний набір полів) ----------
  fbq('track', 'ViewContent', {
    content_ids: [pid],
    content_type: 'product',
    contents: contents,
    content_name: name,
    value: price,
    currency: currency
  }, { eventID: vcId });

  // ---------- CAPI VC (ідентичні поля) ----------
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
      content_name: name,
      content_type: 'product',
      content_ids: [pid],
      contents: contents,
      value: price,            // не обов’язково (бек все одно перерахує), але для симетрії — залишимо
      currency: currency,
      fbp: fbp,
      fbc: fbc
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
