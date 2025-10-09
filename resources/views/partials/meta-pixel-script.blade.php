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
    // Позначка, щоб не запускати кілька разів
    window._mpPvFired = true;

    // ---------- Helpers: парсинг query / cookie / реферер ----------
    function _mp_qp(name) {
      var m = new RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
      return m ? decodeURIComponent(m[1].replace(/\+/g, ' ')) : '';
    }
    function _mp_rc(name) {
      var m = document.cookie.match('(?:^|; )' + name.replace(/([.$?*|{}()\\[\\]\\\\/+^])/g, '\\$1') + '=([^;]*)');
      return m ? decodeURIComponent(m[1]) : '';
    }
    function _mp_isTikTokTraffic() {
      var ref = (document.referrer || '').toLowerCase();
      return !!_mp_qp('ttclid') || ref.indexOf('tiktok.com') !== -1 || ref.indexOf('tiktok') !== -1;
    }
    function _mp_isMetaTraffic() {
      // Meta кліки зазвичай містять fbclid у URL або cookie _fbc (якщо pixel уже ставився)
      return !!_mp_qp('fbclid') || !!_mp_rc('_fbc');
    }

    // ---------- Розв'язок: дозволити Meta only (Meta && not TikTok) ----------
    var _MP_ALLOW_META = _mp_isMetaTraffic() && !_mp_isTikTokTraffic();

    // Генеруємо eventId один раз (для дедуплікації)
    var mpPvEventId = 'pv-' + Math.random().toString(16).slice(2) + '-' + Date.now();

    // Якщо не дозволено Meta (наприклад, TikTok-трафік), вийдемо — ні Pixel, ні CAPI не виконуємо
    if (!_MP_ALLOW_META) {
      // Можемо логувати для дебагу, але краще не шуміти в проді:
      // console.info('MP: skipping Meta Pixel/CAPI (not meta traffic or is TikTok).');
      return;
    }

    // ▶ Bootstrap FB Pixel
    !function(f,b,e,v,n,t,s){
      if(f.fbq) return;
      n=f.fbq=function(){ n.callMethod ? n.callMethod.apply(n,arguments) : n.queue.push(arguments) };
      if(!f._fbq) f._fbq=n;
      n.push=n; n.loaded=!0; n.version='2.0'; n.queue=[];
      t=b.createElement(e); t.async=!0; t.src=v;
      s=b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t,s);
    }(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');

    // ✅ БРАУЗЕРНИЙ PageView — без додаткової затримки (щоб Pixel поставив _fbc/_fbp)
    try {
      fbq('init', '{{ $pixelId }}');
      fbq('track', 'PageView', {}, { eventID: mpPvEventId });
    } catch (e) { /* ignore */ }

    // ⏱ CAPI — затримка тільки для серверного відправлення (щоб cookie встигли установитися)
    @if ($sendCapiPv)
    (function(){
      var DELAY_MS = 1500;
      setTimeout(function(){
        var payload = JSON.stringify({
          event_id: mpPvEventId,
          page_url: location.href
        });

        var sent = false;
        if (navigator.sendBeacon) {
          try {
            sent = navigator.sendBeacon('/api/track/pv', new Blob([payload], { type: 'application/json' }));
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

    // ---------- Опція: SPA роутінг (якщо потрібен, розкоментуй та викликай при зміні маршруту) ----------
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
