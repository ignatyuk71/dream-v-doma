@php
  /** читаємо налаштування без жодних сторонніх залежностей */
  $t        = \Illuminate\Support\Facades\DB::table('tracking_settings')->first();
  $pixelId  = $t?->pixel_id ?? null;
  $enabled  = $t && (int)($t->pixel_enabled ?? 0) === 1 && !empty($pixelId)
             && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));
  $testCode = $t?->capi_test_code ?? null;
@endphp

@once
@if ($enabled && $pixelId)
<script>
/* ====== Глобальне (нічого зі старого коду) ====== */
(function(){
  var PIXEL_ID   = @json($pixelId);
  var TEST_CODE  = @json($testCode);
  var PAGE_URL   = location.href;

  // --- cookie helpers ---
  function getCookie(n){var m=document.cookie.match(new RegExp('(?:^|;\\s*)'+n+'=([^;]+)'));return m?decodeURIComponent(m[1]):null;}
  function setCookie(n,v,days){
    var d=new Date(); d.setDate(d.getDate()+(days||1000));
    var host=location.hostname.replace(/^www\./,''); var isIp=/^[\d.]+$/.test(host);
    var dom=(!isIp&&host!=='localhost')?';Domain=.'+host:'';
    document.cookie=n+'='+encodeURIComponent(v)+';Path=/;Expires='+d.toUTCString()+dom+';SameSite=Lax'+(location.protocol==='https:'?';Secure':'');
  }

  // --- external_id у _extid (UUID) ---
  (function(){
    function uuid(){return([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g,c=>(c^crypto.getRandomValues(new Uint8Array(1))[0]&15>>c/4).toString(16));}
    var ext=getCookie('_extid'); if(!ext){ ext=uuid(); setCookie('_extid',ext,1000); }
    window.__EXTID__ = ext;
  })();

  // --- генератор eventID (для дедуп Pixel+CAPI) ---
  function genEventId(name){
    try{
      var a=new Uint8Array(6); crypto.getRandomValues(a);
      var hex=Array.from(a).map(b=>b.toString(16).padStart(2,'0')).join('');
      return (name||'ev')+'-'+hex+'-'+Math.floor(Date.now()/1000);
    }catch(_){ return (name||'ev')+'-'+Math.random().toString(16).slice(2)+'-'+Math.floor(Date.now()/1000); }
  }

  // --- чекаємо _fbp до 1.5с (не критично, просто шанс) ---
  function waitFbp(ms){
    ms=Number(ms)||1500;
    return new Promise(function(r){
      if(getCookie('_fbp')) return r(true);
      var st=Date.now(); (function t(){ if(getCookie('_fbp')) return r(true);
        if(Date.now()-st>=ms) return r(false); setTimeout(t,80); })();
    });
  }

  // ===== PageView =====
  (function(){
    var eventId = genEventId('pv');

    // 1) Pixel (захист від подвійного init)
    if (!(window.fbq && window.fbq.__pvInitDone)) {
      !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
      n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
      n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
      t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script',
      'https://connect.facebook.net/en_US/fbevents.js');

      fbq('init', PIXEL_ID, { external_id: window.__EXTID__ });
      fbq.__pvInitDone = true;
    }

    fbq('track', 'PageView', { external_id: window.__EXTID__ }, { eventID: eventId });

    // 2) CAPI (бекенд)
    waitFbp(1500).then(function(){
      var payload = {
        event_id: eventId,
        event_time: Math.floor(Date.now()/1000),
        event_source_url: PAGE_URL
      };
      if (TEST_CODE) payload.test_event_code = TEST_CODE;

      var body = JSON.stringify(payload);
      try{
        if (navigator.sendBeacon) {
          navigator.sendBeacon('/api/track/pv', new Blob([body],{type:'application/json'}));
        } else {
          fetch('/api/track/pv', {
            method: 'POST',
            headers: { 'Content-Type':'application/json' },
            body,
            keepalive: true,
            credentials: 'same-origin'
          });
        }
      }catch(_){}
    });
  })();
})();
</script>

<noscript>
  <img src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1"
       height="1" width="1" style="display:none" alt="">
</noscript>
@endif
@endonce
