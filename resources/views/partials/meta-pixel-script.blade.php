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
  if (!window._mpPvFired) {
    window._mpPvFired = true;


    function _mp_isTikTokTraffic() {
      var q   = (location.search || '').toLowerCase();
      var ref = (document.referrer || '').toLowerCase();
      var ua  = (navigator.userAgent || '').toLowerCase();
      // ловимо 3 сигнали: ?ttclid, реферер tiktok, або in-app user agent TikTok
      return q.indexOf('ttclid=') !== -1 || ref.indexOf('tiktok') !== -1 || ua.indexOf('tiktok') !== -1;
    }
    console.debug('TT UA:', navigator.userAgent);
  console.debug('TT ref:', document.referrer);
  console.debug('_MP_ALLOW_META =', _MP_ALLOW_META);
    // ✅ Дозволяємо Meta для ВСЬОГО, крім TikTok
    var _MP_ALLOW_META = !_mp_isTikTokTraffic();
    if (!_MP_ALLOW_META) return;



    // ▶ Bootstrap FB Pixel
    !function(f,b,e,v,n,t,s){
      if(f.fbq) return;
      n=f.fbq=function(){ n.callMethod ? n.callMethod.apply(n,arguments) : n.queue.push(arguments) };
      if(!f._fbq) f._fbq=n;
      n.push=n; n.loaded=!0; n.version='2.0'; n.queue=[];
      t=b.createElement(e); t.async=!0; t.src=v;
      s=b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t,s);
    }(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');

    // ✅ Генеруємо eventId один раз
    var mpPvEventId = 'pv-' + Math.random().toString(16).slice(2) + '-' + Date.now();

    // ✅ БРАУЗЕРНИЙ PageView — БЕЗ ЗАТРИМКИ (щоб Pixel встиг поставити _fbc/_fbp)
    try {
      fbq('init', '{{ $pixelId }}');
      fbq('track', 'PageView', {}, { eventID: mpPvEventId });
    } catch (e) { /* ignore */ }

    // ⏱ CAPI — ЗАТРИМКА 1s ЛИШЕ ДЛЯ СЕРВЕРНОГО ВІДПРАВЛЕННЯ
    @if ($sendCapiPv)
    (function(){
      var DELAY_MS = 1500; // ← затримка тільки для CAPI
      setTimeout(function(){
        var payload = JSON.stringify({
          event_id: mpPvEventId,     // той самий для дедуплікації
          page_url: location.href
        });

        var sent = false;
        if (navigator.sendBeacon) {
          try {
            var blob = new Blob([payload], { type: 'application/json' });
            sent = navigator.sendBeacon('/api/track/pv', blob);
          } catch(e) { /* ignore */ }
        }
        if (!sent) {
          try {
            fetch('/api/track/pv', {
              method: 'POST',
              keepalive: true,
              headers: { 'Content-Type': 'application/json' },
              body: payload
            }).catch(function(){});
          } catch(e) { /* ignore */ }
        }
      }, DELAY_MS);
    })();
    @endif

    // (Опційно) SPA-роутінг: викликати на зміну маршруту
    // window._mpSendPvOnRoute = function(){
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
  }
</script>
@endif
@endonce
