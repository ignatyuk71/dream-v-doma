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
  (function(){
    if (window._mpPvFired) return;
    window._mpPvFired = true;



// інакше продовжуємо: Bootstrap Pixel + відправка подій

    // ▶ Bootstrap FB Pixel (без змін)
    !function(f,b,e,v,n,t,s){
      if(f.fbq) return;
      n=f.fbq=function(){ n.callMethod ? n.callMethod.apply(n,arguments) : n.queue.push(arguments) };
      if(!f._fbq) f._fbq=n;
      n.push=n; n.loaded=!0; n.version='2.0'; n.queue=[];
      t=b.createElement(e); t.async=!0; t.src=v;
      s=b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t,s);
    }(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');

    var mpPvEventId = 'pv-' + Math.random().toString(16).slice(2) + '-' + Date.now();

    try {
      fbq('init', '{{ $pixelId }}');
      fbq('track', 'PageView', {}, { eventID: mpPvEventId });
    } catch (e) {}

    @if ($sendCapiPv)
    setTimeout(function(){
      var payload = JSON.stringify({
        event_id: mpPvEventId,
        page_url: location.href
      });

      var sent = false;
      if (navigator.sendBeacon) {
        try { sent = navigator.sendBeacon('/api/track/pv', new Blob([payload], { type: 'application/json' })); } catch(e){}
      }
      if (!sent) {
        try {
          fetch('/api/track/pv', { method:'POST', keepalive:true, headers:{'Content-Type':'application/json'}, body:payload }).catch(function(){});
        } catch(e){}
      }
    }, 1500);
    @endif
  })();
</script>
@endif
@endonce
