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
  // ── Guard: не дублювати на сторінці
  if (!window._mpPvFired) {
    window._mpPvFired = true;

    // ── FB Pixel bootstrap
    !function(f,b,e,v,n,t,s){
      if(f.fbq) return;
      n=f.fbq=function(){ n.callMethod ? n.callMethod.apply(n,arguments) : n.queue.push(arguments) };
      if(!f._fbq) f._fbq=n;
      n.push=n; n.loaded=!0; n.version='2.0'; n.queue=[];
      t=b.createElement(e); t.async=!0; t.src=v;
      s=b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t,s);
    }(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');

    // ⏱ Затримка перед відправкою
    (function(){
      var DELAY_MS = 1000; // можна підкрутити (300–1000мс)
      setTimeout(function() {
        // Один і той самий eventID для дедуплікації
        var eventId = 'pv-' + Math.random().toString(16).slice(2) + '-' + Date.now();

        // Browser PV
        try {
          fbq('init', '{{ $pixelId }}');
          fbq('track', 'PageView', {}, { eventID: eventId });
        } catch (e) { /* ignore */ }

        // Server PV через бекенд
        @if ($sendCapiPv)
        var payload = JSON.stringify({ event_id: eventId, page_url: location.href });

        // 1) Перший пріоритет — sendBeacon (працює навіть при unload)
        var sent = false;
        if (navigator.sendBeacon) {
          try {
            var blob = new Blob([payload], { type: 'application/json' });
            sent = navigator.sendBeacon('/api/track/pv', blob);
          } catch(e) { /* ignore */ }
        }

        // 2) Fallback — fetch keepalive
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
        @endif
      }, DELAY_MS);
    })();

    // ── (Опційно) SPA: якщо використовуєш Vue Router, викликай це на зміні маршруту
    // window._mpSendPvOnRoute = function(){
    //   var eventId = 'pv-' + Math.random().toString(16).slice(2) + '-' + Date.now();
    //   fbq('track','PageView',{}, { eventID: eventId });
    //   var payload = JSON.stringify({ event_id: eventId, page_url: location.href });
    //   if (navigator.sendBeacon) {
    //     try { navigator.sendBeacon('/api/track/pv', new Blob([payload],{type:'application/json'})); return; } catch(e){}
    //   }
    //   fetch('/api/track/pv', { method:'POST', keepalive:true, headers:{'Content-Type':'application/json'}, body:payload }).catch(function(){});
    // };
  }
</script>
@endif
@endonce
