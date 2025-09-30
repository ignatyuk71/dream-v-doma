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

    (function(){
      // Один і той самий eventID для fbq і CAPI
      var eventId = 'pv-' + Math.random().toString(16).slice(2) + '-' + Date.now();

      // Ініціалізація Pixel одразу
      fbq('init', '{{ $pixelId }}');

      // 👉 Відкладений на 2 секунди браузерний PageView
      setTimeout(function () {
        fbq('track', 'PageView', {}, { eventID: eventId });
      }, 2000);

      @if ($sendCapiPv)
      // Server PV через бекенд — одразу (той самий eventId)
      var payload = JSON.stringify({ event_id: eventId, page_url: location.href });

      // 1) Перший пріоритет — sendBeacon
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
    })();
  }
</script>

@endif
@endonce
