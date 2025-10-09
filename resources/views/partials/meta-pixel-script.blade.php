@php
  use Illuminate\Support\Facades\DB;

  $t = DB::table('tracking_settings')->first();

  $pixelId = $t?->pixel_id ?? null;

  $enabled = $t
    && (int)($t->pixel_enabled ?? 0) === 1
    && !empty($pixelId)
    && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  $pvEnabled = $enabled && (bool)($t->send_page_view ?? true);

  $capiEnabled = $t
    && (int)($t->capi_enabled ?? 0) === 1
    && !empty($t->capi_token);

  $sendCapiPv = $pvEnabled && $capiEnabled;
@endphp

@once
@if ($pvEnabled)
<script>
;(function () {
  if (window._mpPvFired) return;
  window._mpPvFired = true;

 // ── helpers ─────────────────────────────────────────────
 function setCookie(name, value, seconds){
    // host-only + SameSite=Lax (стабільно крос-сторінково в межах сайту)
    var attrs = '; Max-Age=' + (seconds|0) + '; Path=/; SameSite=Lax';
    if (location.protocol === 'https:') attrs += '; Secure';
    document.cookie = name + '=' + encodeURIComponent(value) + attrs;
  }
  function hasCookieKV(kv){ return (document.cookie || '').indexOf(kv) !== -1; }

  // ——— Визначення TikTok-трафіку (URL, referrer, user-agent)
  function _mp_isTikTokTraffic() {
    var q   = (location.search   || '').toLowerCase();
    var ref = (document.referrer || '').toLowerCase();
    var ua  = (navigator.userAgent|| '').toLowerCase();
    return q.indexOf('ttclid=') !== -1 || ref.indexOf('tiktok') !== -1 || ua.indexOf('tiktok') !== -1;
  }


  // Якщо вже позначений як TikTok → продовжимо життя мітки (ковзне вікно 60 хв)
  if (hasCookieKV('_mp_src=tiktok')) setCookie('_mp_src','tiktok', 60*60);

  // ✅ Дозволяємо Meta для всього, КРІМ TikTok
  var _MP_ALLOW_META = !_mp_isTikTokTraffic();
  if (!_MP_ALLOW_META) {
    // перший хіт з TikTok → ставимо мітку і нічого не шлемо в Meta
    setCookie('_mp_src', 'tiktok', 60*60);
    return;
  }
  var _isTT = hasCookieKV('_mp_src=tiktok') || _mp_isTikTokTrafficNow();
  if (_isTT) { setCookie('_mp_src','tiktok',60*60); return; }
  // ▶ Bootstrap FB Pixel
  !function(f,b,e,v,n,t,s){
    if(f.fbq) return;
    n=f.fbq=function(){ n.callMethod ? n.callMethod.apply(n,arguments) : n.queue.push(arguments) };
    if(!f._fbq) f._fbq=n;
    n.push=n; n.loaded=!0; n.version='2.0'; n.queue=[];
    t=b.createElement(e); t.async=!0; t.src=v;
    s=b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t,s);
  }(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');

  // ✅ Спільний eventID для дедуплікації
  var mpPvEventId = 'pv-' + Math.random().toString(16).slice(2) + '-' + Date.now();

  // Браузерний PageView
  try {
    fbq('init', '{{ $pixelId }}');
    fbq('track', 'PageView', {}, { eventID: mpPvEventId });
  } catch (e) { /* ignore */ }

  // ⏱ CAPI з затримкою (щоб встигли оновитись куки)
  @if ($sendCapiPv)
  setTimeout(function(){
    var payload = JSON.stringify({
      event_id: mpPvEventId,
      page_url: location.href
    });

    var sent = false;
    if (navigator.sendBeacon) {
      try {
        sent = navigator.sendBeacon('/api/track/pv', new Blob([payload], { type: 'application/json' }));
      } catch(e) {}
    }
    if (!sent) {
      try {
        fetch('/api/track/pv', {
          method: 'POST',
          keepalive: true,
          headers: { 'Content-Type': 'application/json' },
          body: payload
        }).catch(function(){});
      } catch(e) {}
    }
  }, 1500);
  @endif

  // (опційно) SPA-хук:
  // window._mpSendPvOnRoute = function(){
  //   if (_mp_isTikTokTraffic()) return;
  //   var id = 'pv-' + Math.random().toString(16).slice(2) + '-' + Date.now();
  //   try { fbq('track','PageView',{}, { eventID: id }); } catch(e){}
  //   @if ($sendCapiPv)
  //   var payload = JSON.stringify({ event_id: id, page_url: location.href });
  //   if (navigator.sendBeacon) {
  //     try { navigator.sendBeacon('/api/track/pv', new Blob([payload], {type:'application/json'})); return; } catch(e){}
  //   }
  //   fetch('/api/track/pv', { method:'POST', keepalive:true, headers:{'Content-Type':'application/json'}, body:payload }).catch(function(){});
  //   @endif
  // };
})();
</script>
@endif
@endonce
