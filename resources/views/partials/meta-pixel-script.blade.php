@php
  use Illuminate\Support\Facades\DB;

  $t = DB::table('tracking_settings')->first();

  $pixelId  = $t?->pixel_id ?? null;
  $currency = $t?->default_currency ?? 'UAH';

  $enabled  = $t
    && (int)($t?->pixel_enabled ?? 0) === 1
    && !empty($pixelId)
    && !((int)($t?->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  $flags = [
    'pv'   => (bool)($t?->send_page_view          ?? true),
    'vc'   => (bool)($t?->send_view_content       ?? true),
    'atc'  => (bool)($t?->send_add_to_cart        ?? true),
    'ic'   => (bool)($t?->send_initiate_checkout  ?? true),
    'pur'  => (bool)($t?->send_purchase           ?? true),
    'lead' => (bool)($t?->send_lead               ?? false),
  ];
  if (!$enabled) {
    $flags = ['pv'=>false,'vc'=>false,'atc'=>false,'ic'=>false,'pur'=>false,'lead'=>false];
  }

  $testCode = $t?->capi_test_code ?? null;
@endphp

<script>
  // Глобальні прапорці/налаштування
  window._mpFlags          = @json($flags, JSON_UNESCAPED_UNICODE);
  window.metaPixelCurrency = @json($currency);
  window._mpEnabled        = @json($enabled);
  window._mpPixelId        = @json($pixelId);
  window._mpTestCode       = @json($testCode);

  // Генератор спільних event_id
  window._mpGenEventId = function(name){
    try {
      var a = new Uint8Array(6);
      (window.crypto || window.msCrypto).getRandomValues(a);
      var hex = Array.from(a).map(function(b){ return b.toString(16).padStart(2,'0') }).join('');
      return (name || 'ev') + '-' + hex + '-' + Math.floor(Date.now()/1000);
    } catch (_) {
      return (name || 'ev') + '-' + Math.random().toString(16).slice(2) + '-' + Math.floor(Date.now()/1000);
    }
  };

  // Хелпери cookie
  window._mpGetCookie = function(n){
    var m = document.cookie.match(new RegExp('(?:^|;\\s*)' + n + '=([^;]+)'));
    return m ? decodeURIComponent(m[1]) : null;
  };
  window._mpSetCookie = function(n, v, days){
    var d = new Date();
    d.setDate(d.getDate() + (days || 365*3)); // за замовч. 3 роки
    var parts = [
      n + '=' + encodeURIComponent(v),
      'Path=/',
      'Expires=' + d.toUTCString(),
      'Domain=.dream-v-doma.com.ua',
      (location.protocol === 'https:' ? 'Secure' : ''),
      'SameSite=Lax',
    ].filter(Boolean);
    document.cookie = parts.join('; ');
  };
  window._mpGetParam = function(name){
    var m = location.search.match(new RegExp('[?&]'+name+'=([^&]+)'));
    return m ? m[1] : null;
  };

  // FB/IG-трафік: fbclid або _fbc
  window._mpIsFbTraffic = function(){
    return !!(_mpGetCookie('_fbc') || _mpGetParam('fbclid'));
  };

  // --- external_id (стабільний, зберігаємо у _extid) ---
  (function () {
    function uuid(){ return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g,c=>
      (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)); }
    var ext = _mpGetCookie('_extid');
    if (!ext) { ext = uuid(); _mpSetCookie('_extid', ext, 365*3); } // 3 роки
    window._extid = ext;
  })();
</script>

@if ($enabled)
  <!-- Meta Pixel (браузер) — лише для FB-трафіку -->
  <script>
  (function(){
    if (!window._mpFlags || window._mpFlags.pv === false) return;
    if (!window._mpIsFbTraffic()) return;
    if (!window._mpPixelId) return;

    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
    n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');

    // Advanced matching — відразу підхоплює external_id
    fbq('init', '{{ $pixelId }}', { external_id: window._extid });

    // Спільний eventID для дедупу
    var pvId = window._mpPVId || (window._mpPVId = window._mpGenEventId('pv'));

    // PageView у Pixel + передаємо external_id в options (не обов’язково, але ок)
    fbq('track', 'PageView', {}, { eventID: pvId, external_id: window._extid });
  })();
  </script>
@endif

<!-- CAPI PageView — лише для FB-трафіку -->
<script>
(function(){
  if (!window._mpFlags || window._mpFlags.pv === false) return;
  if (!window._mpIsFbTraffic()) return;

  var pvId = window._mpPVId || (window._mpPVId = window._mpGenEventId('pv'));

  var payload = {
    event_id: pvId,
    event_time: Math.floor(Date.now()/1000),
    event_source_url: window.location.href
    // fbc/fbp/external_id бекенд дістане з cookies (_fbc/_fbp/_extid)
  };
  if (window._mpTestCode) payload.test_event_code = window._mpTestCode;

  try {
    var body = JSON.stringify(payload);
    if (navigator.sendBeacon) {
      navigator.sendBeacon('/api/track/pv', new Blob([body], {type:'application/json'}));
    } else {
      fetch('/api/track/pv', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body,
        keepalive: true
      });
    }
  } catch (_) {}
})();
</script>
