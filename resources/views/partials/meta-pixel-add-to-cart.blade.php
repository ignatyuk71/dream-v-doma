@php
  use Illuminate\Support\Facades\DB;
  $t = DB::table('tracking_settings')->first();

  $pixelOk = $t
    && (int)($t->pixel_enabled ?? 0) === 1
    && !empty($t->pixel_id)
    && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  $allowATC = $pixelOk && (int)($t->send_add_to_cart ?? 1) === 1;
@endphp

@if ($allowATC)
<script>
(function(){
  if (window.mpTrackATC) return;                       // не перевизначати
  if (!window._mpFlags || window._mpFlags.atc === false) return;

  // утиліти
  function num(v){
    var s = String(v ?? 0).replace(',', '.').replace(/[^\d.\-]/g, '');
    var n = parseFloat(s);
    return Number.isFinite(n) ? Number(n.toFixed(2)) : 0;
  }
  function getCookie(n){
    return document.cookie.split('; ').find(function(r){ return r.indexOf(n + '=') === 0 })?.split('=')[1] || null;
  }
  function safeDecode(c){ try { return c ? decodeURIComponent(c) : null } catch(_) { return c } }

  // анти-даблклік: кеш останньої події по (sku+qty+price) на 800мс
  var _atcSeen = new Map();
  function shouldSkip(key){
    var now = Date.now();
    var prev = _atcSeen.get(key) || 0;
    _atcSeen.set(key, now);
    return (now - prev) < 800;
  }

  /**
   * ГОЛОВНА ФУНКЦІЯ
   * Викликати після успішного додавання в кошик:
   *   mpTrackATC({ sku|id, price, quantity?, name?, currency? })
   */
  window.mpTrackATC = function(opts){
    try{
      if (!opts) return;

      var pid = String(opts.sku || opts.id || '');
      if (!pid) return;

      var qty   = Number(opts.quantity || 1); if (!Number.isFinite(qty) || qty <= 0) qty = 1;
      var price = num(opts.price);
      var name  = typeof opts.name === 'string' ? opts.name : '';
      var currency = opts.currency || (window.metaPixelCurrency || 'UAH');

      // анти-дабл
      var k = pid + '|' + qty + '|' + price;
      if (shouldSkip(k)) return;

      var contents = [{ id: pid, quantity: qty, item_price: price }];
      var value = num(qty * price);

      // один і той самий event_id для дедупу
      var atcId = (window._mpGenEventId
        ? window._mpGenEventId('atc')
        : ('atc-' + Math.random().toString(16).slice(2) + '-' + Date.now()));

      // -------- БРАУЗЕР (ретрай, поки fbq не готовий) --------
      (function sendPixel(){
        if (typeof window.fbq !== 'function'){ setTimeout(sendPixel, 80); return; }
        window.fbq('track', 'AddToCart', {
          content_ids: [pid],
          content_type: 'product',
          contents: contents,
          content_name: name,
          value: value,
          currency: currency
        }, { eventID: atcId }); // ВАЖЛИВО: eventID (camelCase)
      })();

      // -------- CAPI (той самий event_id) --------
      var fbp = safeDecode(getCookie('_fbp'));
      var fbc = safeDecode(getCookie('_fbc'));
      var bodyObj = {
        event_id: atcId,                               // ВАЖЛИВО: event_id (snake_case)
        event_time: Math.floor(Date.now()/1000),
        event_source_url: window.location.href,
        content_type: 'product',
        content_ids: [pid],
        contents: contents,
        content_name: name,
        value: value,
        currency: currency,
        fbp: fbp,
        fbc: fbc
      };
      if (window._mpTestCode) bodyObj.test_event_code = window._mpTestCode;

      var body = JSON.stringify(bodyObj);

      // iOS/Instagram → тільки fetch keepalive
      var ua = navigator.userAgent || '';
      var isiOS = /iPad|iPhone|iPod/i.test(ua) || (/Macintosh/i.test(ua) && 'ontouchend' in document);

      if (isiOS || !navigator.sendBeacon) {
        fetch('/api/track/atc', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'same-origin',
          keepalive: true,
          body
        }).catch(function(){ setTimeout(function(){
          fetch('/api/track/atc', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'same-origin', keepalive:true, body });
        }, 300); });
      } else {
        navigator.sendBeacon('/api/track/atc', new Blob([body], {type:'application/json'}));
      }
    }catch(_){}
  };
})();
</script>
@endif
