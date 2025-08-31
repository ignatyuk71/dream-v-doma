@php
  use Illuminate\Support\Facades\DB;

  $t = DB::table('tracking_settings')->first();

  $pixelId  = $t->pixel_id ?? null;
  $currency = $t->default_currency ?? 'UAH';

  $enabled  = $t
    && (int)($t->pixel_enabled ?? 0) === 1
    && !empty($pixelId)
    && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  $flags = [
    'pv'   => (bool)($t->send_page_view          ?? true),
    'vc'   => (bool)($t->send_view_content       ?? true),
    'atc'  => (bool)($t->send_add_to_cart        ?? true),
    'ic'   => (bool)($t->send_initiate_checkout  ?? true),
    'pur'  => (bool)($t->send_purchase           ?? true),
    'lead' => (bool)($t->send_lead               ?? false),
  ];

  if (!$enabled) {
    $flags = ['pv'=>false,'vc'=>false,'atc'=>false,'ic'=>false,'pur'=>false,'lead'=>false];
  }
@endphp

<script>
  window._mpFlags = @json($flags, JSON_UNESCAPED_UNICODE);
  window.metaPixelCurrency = @json($currency);
  window._mpEnabled = @json($enabled);
  window._mpPixelId = @json($pixelId);

  // генератор спільних event_id
  window._mpGenEventId = function(name){
    return (name || 'ev') + '-' + Math.random().toString(16).slice(2) + '-' + Date.now();
  };

  // cookie helper
  window._mpGetCookie = function(n){
    return document.cookie.split('; ').find(r=>r.startsWith(n+'='))?.split('=')[1] || null;
  };
</script>

@if ($enabled)
  <!-- Meta Pixel -->
  <script>
  !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
  n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script',
  'https://connect.facebook.net/en_US/fbevents.js');

  fbq('init', '{{ $pixelId }}');

  // Browser PageView з event_id (для дедупу з CAPI)
  (function(){
    if (window._mpFlags && window._mpFlags.pv === false) return;
    var pvId = window._mpPVId || (window._mpPVId = window._mpGenEventId('pv'));
    fbq('track', 'PageView', {}, { eventID: pvId });
  })();
  </script>
@endif

{{-- CAPI PageView з тим самим event_id --}}
<script>
(function(){
  if (!window._mpFlags || window._mpFlags.pv === false) return;

  var pvId = window._mpPVId || (window._mpPVId = window._mpGenEventId('pv'));
  var fbc = (function(c){ try { return c ? decodeURIComponent(c) : null } catch(_) { return c } })(window._mpGetCookie('_fbc'));

  var payload = {
    event_id: pvId,
    event_time: Math.floor(Date.now()/1000),
    event_source_url: window.location.href,
    fbp: window._mpGetCookie('_fbp'),
    fbc: fbc
  };

  try {
    var body = JSON.stringify(payload);
    if (navigator.sendBeacon) {
      var blob = new Blob([body], {type: 'application/json'});
      navigator.sendBeacon('/api/track/pv', blob);
    } else {
      fetch('/api/track/pv', { method:'POST', headers:{'Content-Type':'application/json'}, body, keepalive:true });
    }
  } catch (_) {}
})();
</script>