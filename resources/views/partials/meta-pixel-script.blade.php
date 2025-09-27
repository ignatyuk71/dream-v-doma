@php
  use Illuminate\Support\Facades\DB;

  $t = DB::table('tracking_settings')->first();
  $pixelId  = $t?->pixel_id ?? null;
  $currency = $t?->default_currency ?? 'UAH';
  $enabled  = $t
    && (int)($t?->pixel_enabled ?? 0) === 1
    && !empty($pixelId)
    && !((int)($t?->exclude_admin ?? 1) === 1 && request()->is('admin*'));
  $testCode = $t?->capi_test_code ?? null;
@endphp

@once
@if ($enabled && $pixelId)
<script>
// =================== Глобальні налаштування ===================
window._mpPixelId  = @json($pixelId);
window._mpTestCode = @json($testCode);
window._mpPVUrl    = location.href;

// Генератор event_id (для дедупу Pixel+CAPI)
window._mpGenEventId = function(name){
  try {
    var a = new Uint8Array(6);
    (crypto||msCrypto).getRandomValues(a);
    var hex = Array.from(a).map(b=>b.toString(16).padStart(2,'0')).join('');
    return (name||'ev')+'-'+hex+'-'+Math.floor(Date.now()/1000);
  } catch(e){
    return (name||'ev')+'-'+Math.random().toString(16).slice(2)+'-'+Math.floor(Date.now()/1000);
  }
};

// ===== cookies =====
function _mpGetCookie(n){ var m=document.cookie.match(new RegExp('(?:^|;\\s*)'+n+'=([^;]+)')); return m?decodeURIComponent(m[1]):null; }
function _mpSetCookie(n,v,days){
  var d=new Date(); d.setDate(d.getDate()+(days||365*3));
  var host=location.hostname.replace(/^www\./,''); var isIp=/^[\d.]+$/.test(host);
  var domainAttr=(!isIp&&host!=='localhost')?';Domain=.'+host:'';
  document.cookie=n+'='+encodeURIComponent(v)+';Path=/;Expires='+d.toUTCString()+domainAttr+';SameSite=Lax'+(location.protocol==='https:'?';Secure':'');
}

// stable external_id
(function(){
  function uuid(){return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g,c=>(c^crypto.getRandomValues(new Uint8Array(1))[0]&15>>c/4).toString(16))}
  var ext=_mpGetCookie('_extid'); if(!ext){ ext=uuid(); _mpSetCookie('_extid',ext,365*3); }
  window._extid = ext;
})();

// дати шанс Pixel створити _fbp (до 1.5с)
function _mpWaitForFbp(ms){
  ms=Number(ms)||1500; return new Promise(r=>{
    if(_mpGetCookie('_fbp')) return r(true);
    var s=Date.now(); (function t(){ if(_mpGetCookie('_fbp')) return r(true);
      if(Date.now()-s>=ms) return r(false); setTimeout(t,80); })();
  });
}

// =================== PAGE VIEW ===================
(function(){
  var pvId = window._mpPVId || (window._mpPVId = _mpGenEventId('pv'));

  // --- Pixel: захист від повторної ініціалізації ---
  if (!(window.fbq && window.fbq.__mpInitDone)) {
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
    n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');

    fbq('init', window._mpPixelId, { external_id: window._extid });
    fbq.__mpInitDone = true;
  }

  fbq('track', 'PageView', { external_id: window._extid }, { eventID: pvId });

  // --- CAPI ---
  _mpWaitForFbp(1500).then(function(){
    var payload = {
      event_id: pvId,
      event_time: Math.floor(Date.now()/1000),
      event_source_url: window._mpPVUrl
    };
    if (window._mpTestCode) payload.test_event_code = window._mpTestCode;

    var body = JSON.stringify(payload);
    try {
      if (navigator.sendBeacon) {
        navigator.sendBeacon('/api/track/pv', new Blob([body], {type:'application/json'}));
      } else {
        fetch('/api/track/pv', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body,
          keepalive: true,
          credentials: 'same-origin'
        });
      }
    } catch(_) {}
  });
})();
</script>

@endif
@endonce
