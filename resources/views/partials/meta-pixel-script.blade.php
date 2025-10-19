@php
  use Illuminate\Support\Facades\DB;

  // Беремо перший запис із таблиці налаштувань трекінгу
  $t = DB::table('tracking_settings')->first();

  // ID пікселя Meta (Facebook)
  $pixelId = $t?->pixel_id ?? null;

  // Головний прапор: піксель увімкнено, є pixelId, і ми НЕ в адмінці якщо exclude_admin=1
  $enabled = $t
    && (int)($t->pixel_enabled ?? 0) === 1
    && !empty($pixelId)
    && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  // Чи шлемо браузерний PageView (Pixel)
  $pvEnabled = $enabled && (bool)($t->send_page_view ?? true);

  // Чи увімкнено CAPI і чи є token
  $capiEnabled = $t
    && (int)($t->capi_enabled ?? 0) === 1
    && !empty($t->capi_token);

  // Чи відправляти дубль PageView на бекенд (для CAPI) — лише якщо дозволено PV і CAPI
  $sendCapiPv = $pvEnabled && $capiEnabled;
@endphp

@once
@if ($pvEnabled)
<script>
  // Гард, щоб не відправити PageView двічі при повторному включенні скрипта
  if (!window._mpPvFired) {
    window._mpPvFired = true;

    // ---- ЛИШЕ ПЕРЕВІРКА fbclid У ПОТОЧНОМУ URL ----
    // Якщо в URL немає fbclid — це не клік із реклами Meta → зупиняємось.
    try {
      var fbclid = new URLSearchParams(location.search).get('fbclid');
      if (!fbclid) {
        return; // ❌ не реклама — нічого не вантажимо і не шлемо
      }
    } catch (e) {
      
    }

    // ▶ Bootstrap FB Pixel — стандартна ініціалізація fbq та підвантаження SDK
    !function(f,b,e,v,n,t,s){
      if(f.fbq) return;
      n=f.fbq=function(){ n.callMethod ? n.callMethod.apply(n,arguments) : n.queue.push(arguments) };
      if(!f._fbq) f._fbq=n;
      n.push=n; n.loaded=!0; n.version='2.0'; n.queue=[];
      t=b.createElement(e); t.async=!0; t.src=v;
      s=b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t,s);
    }(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');

    // один eventID для дедуплікації Pixel vs CAPI
    var mpPvEventId = 'pv-' + Math.random().toString(16).slice(2) + '-' + Date.now();

    // БРАУЗЕРНИЙ PageView — одразу
    try {
      fbq('init', '{{ $pixelId }}');
      fbq('track', 'PageView', {}, { eventID: mpPvEventId });
    } catch (e) { /* ignore */ }

    // CAPI — із затримкою (лише якщо увімкнено в налаштуваннях)
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

    // // (Опційно) SPA-хук — якщо треба слати додаткові PV при зміні маршруту:
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
