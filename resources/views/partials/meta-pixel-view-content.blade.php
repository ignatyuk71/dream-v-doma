@if (isset($product))
<script>
(function () {
  // глобальні гейтири з паршала пікселя
  if (!window._mpEnabled) return;
  if (window._mpFlags && window._mpFlags.vc === false) return;

  var pid      = String(@json($product->sku ?? $product->id));
  var name     = @json($product->name ?? $product->title ?? '');
  var rawPrice = @json($product->price ?? 0);
  var currency = window.metaPixelCurrency || 'UAH';

  // нормалізація ціни
  var price = (function (p) {
    var s = String(p).replace(',', '.').replace(/[^\d.]/g, '');
    var n = parseFloat(s);
    return Number.isFinite(n) ? Number(n.toFixed(2)) : 0;
  })(rawPrice);

  // payload для браузерного Pixel
  var pixelPayload = {
    content_ids: [pid],
    content_type: 'product',
    content_name: name,
    value: price,
    currency: currency
  };

  // спільний event_id для дедупу з CAPI
  var vcId = window._mpVCId || (window._mpVCId =
    (window._mpGenEventId ? window._mpGenEventId('vc') : ('vc-' + Math.random().toString(16).slice(2) + '-' + Date.now()))
  );

  // 1) Браузерний ViewContent
  if (window.fbq) {
    fbq('track', 'ViewContent', pixelPayload, { eventID: vcId });
  }

  // 2) CAPI ViewContent з тим самим event_id
  try {
    var safe = function(c){ try { return c ? decodeURIComponent(c) : null } catch(_){ return c } };
    var payload = {
      event_id: vcId,
      event_time: Math.floor(Date.now()/1000),
      event_source_url: window.location.href,
      currency: currency,
      contents: [{ id: pid, quantity: 1, item_price: price }],
      fbp: window._mpGetCookie ? safe(window._mpGetCookie('_fbp')) : null,
      fbc: window._mpGetCookie ? safe(window._mpGetCookie('_fbc')) : null
    };
    if (window._mpTestCode) payload.test_event_code = window._mpTestCode;

    var body = JSON.stringify(payload);
    if (navigator.sendBeacon) {
      navigator.sendBeacon('/api/track/vc', new Blob([body], {type:'application/json'}));
    } else {
      fetch('/api/track/vc', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: body,
        keepalive:true
      });
    }
  } catch (_) {}
})();
</script>
@endif
