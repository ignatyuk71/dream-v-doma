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

    // ▶ Bootstrap FB Pixel — стандартна ініціалізація fbq та підвантаження SDK
    !function(f,b,e,v,n,t,s){
      if(f.fbq) return;
      n=f.fbq=function(){ n.callMethod ? n.callMethod.apply(n,arguments) : n.queue.push(arguments) };
      if(!f._fbq) f._fbq=n;
      n.push=n; n.loaded=!0; n.version='2.0'; n.queue=[];
      t=b.createElement(e); t.async=!0; t.src=v;
      s=b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t,s);
    }(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');

    // ✅ Генеруємо унікальний eventId для дедуплікації Pixel vs CAPI (той самий id підемо в обидва канали)
    var mpPvEventId = 'pv-' + Math.random().toString(16).slice(2) + '-' + Date.now();

    // ✅ БРАУЗЕРНИЙ PageView — відправляємо одразу (щоб Pixel встиг виставити _fbc/_fbp)
    try {
      fbq('init', '{{ $pixelId }}');                  // ініціалізація пікселя
      fbq('track', 'PageView', {}, { eventID: mpPvEventId }); // PageView з eventID
    } catch (e) { /* ignore */ }

    // ⏱ CAPI — ЗАТРИМКА лише для серверного відправлення (щоб _fbc/_fbp уже були у браузері)
    @if ($sendCapiPv)
    (function(){
      var DELAY_MS = 1500; // ← затримка тільки для CAPI (1.5s)
      setTimeout(function(){
        // Мінімальний payload: спільний event_id і поточний URL
        var payload = JSON.stringify({
          event_id: mpPvEventId,     // той самий для дедуплікації
          page_url: location.href
        });

        // Намагаємося відправити ненав’язливо через sendBeacon (краще при розгортанні сторінки/навігації)
        var sent = false;
        if (navigator.sendBeacon) {
          try {
            var blob = new Blob([payload], { type: 'application/json' });
            sent = navigator.sendBeacon('/api/track/pv', blob); // бекенд-ендпоінт для CAPI
          } catch(e) { /* ignore */ }
        }

        // Якщо sendBeacon недоступний/не спрацював — фолбек на fetch з keepalive
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

    // (Опційно) Для SPA: викликайте цю функцію на зміну маршруту, щоб слати додаткові PageView
    // window._mpSendPvOnRoute = function(){
    //   var id = 'pv-' + Math.random().toString(16).slice(2) + '-' + Date.now(); // свіжий eventID на кожен "роут"
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
