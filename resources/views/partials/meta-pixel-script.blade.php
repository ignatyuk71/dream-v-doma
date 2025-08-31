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
  if (!$enabled) { $flags = ['pv'=>false,'vc'=>false,'atc'=>false,'ic'=>false,'pur'=>false,'lead'=>false]; }
@endphp

<script>
  window._mpFlags = @json($flags, JSON_UNESCAPED_UNICODE);
  window.metaPixelCurrency = @json($currency);
  window._mpEnabled = @json($enabled);
  window._mpPixelId = @json($pixelId);

  window._mpGenEventId = function(name){
    return (name || 'ev') + '-' + Math.random().toString(16).slice(2) + '-' + Date.now();
  };
  window._mpGetCookie = function(n){
    return document.cookie.split('; ').find(r=>r.startsWith(n+'='))?.split('=')[1] || null;
  };

  // анонімний external_id (2 роки)
  (function(){
    var C = 'dv_uid';
    var m = document.cookie.match(new RegExp('(?:^|; )'+C+'=([^;]*)'));
    var uid = m ? decodeURIComponent(m[1]) : null;
    if (!uid) {
      uid = 'dv_' + Math.random().toString(36).slice(2,10) + Date.now().toString(36);
      document.cookie = C + '=' + encodeURIComponent(uid) + '; path=/; samesite=Lax; max-age=' + (730*24*3600);
    }
    window._mpUid = uid;
  })();

  // якщо прийшли з реклами — створюємо _fbc з fbclid
  (function(){
    var q = new URLSearchParams(location.search);
    var fbclid = q.get('fbclid');
    if (fbclid && !document.cookie.includes('_fbc=')) {
      var ts = Math.floor(Date.now()/1000);
      var val = 'fb.1.' + ts + '.' + fbclid;
      document.cookie = '_fbc=' + encodeURIComponent(val) + '; path=/; samesite=Lax; max-age=' + (90*24*3600);
    }
  })();
</script>

@if ($enabled)
  <!-- Meta Pixel -->
  <script>
  !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
  n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script',
  'https://connect.facebook.net/en_US/fbevents.js');

  fbq('set', 'autoConfig', false, '{{ $pixelId }}'); // щоб у PageView не було ap[...]
  fbq('init', '{{ $pixelId }}');

  (function(){
    if (window._mpFlags && window._mpFlags.pv === false) return;
    var pvId = window._mpPVId || (window._mpPVId = window._mpGenEventId('pv'));
    fbq('track', 'PageView', {}, { eventID: pvId });
  })();
  </script>
@endif

<script>
(function(){
  if (!window._mpFlags || window._mpFlags.pv === false) return;

  var pvId = window._mpPVId || (window._mpPVId = window._mpGenEventId('pv'));
  var getCookie = window._mpGetCookie;
  var safeDecode = function(v){ try { return v ? decodeURIComponent(v) : null } catch(_) { return v } };

  var fbcCookie = safeDecode(getCookie('_fbc'));
  var fbclid = new URLSearchParams(location.search).get('fbclid');
  var fbc = fbcCookie || (fbclid ? ('fb.1.' + Math.floor(Date.now()/1000) + '.' + fbclid) : null);

  var payload = {
    event_id: pvId,
    event_time: Math.floor(Date.now()/1000),
    event_source_url: window.location.href,
    fbp: getCookie('_fbp') || null,
    fbc: fbc,
    external_id: window._mpUid || null
  };

  var body = JSON.stringify(payload);
  if (navigator.sendBeacon) {
    navigator.sendBeacon('/api/track/pv', new Blob([body], {type:'application/json'}));
  } else {
    fetch('/api/track/pv', { method:'POST', headers:{'Content-Type':'application/json'}, body, keepalive:true });
  }
})();
</script>
