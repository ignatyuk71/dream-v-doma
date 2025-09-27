@php
  use Illuminate\Support\Facades\DB;

  $t = DB::table('tracking_settings')->first();

  $pixelId  = $t?->pixel_id ?? null;

  $enabled  = $t
    && (int)($t?->pixel_enabled ?? 0) === 1
    && !empty($pixelId)
    && !((int)($t?->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  // Лишаємо лише прапорець PageView
  $pvEnabled = $enabled && (bool)($t?->send_page_view ?? true);
@endphp

@if ($pvEnabled)
  <!-- Meta Pixel (тільки браузерний PageView) -->
  <script>
    // (опц.) простий external_id у кукі для advanced matching — не обов'язково
    (function () {
      try {
        var name = '_extid';
        if (!document.cookie.match(new RegExp('(?:^|;\\s*)' + name + '='))) {
          var r = (crypto.getRandomValues ? crypto.getRandomValues(new Uint8Array(16)) : Array.from({length:16},()=>Math.floor(Math.random()*256)))
            .reduce((s,b)=>s+('0'+b.toString(16)).slice(-2),'');
          var d = new Date(); d.setDate(d.getDate() + 365*3);
          document.cookie = name + '=' + r + '; Path=/; Expires=' + d.toUTCString() + '; SameSite=Lax' + (location.protocol==='https:'?'; Secure':'');
          window._extid = r;
        } else {
          window._extid = (document.cookie.match(new RegExp('(?:^|;\\s*)' + name + '=([^;]+)'))||[])[1];
        }
      } catch (_) {}
    })();

    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
    n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');

    // Ініціалізація пікселя. Можеш прибрати external_id, якщо не потрібен.
    fbq('init', '{{ $pixelId }}', window._extid ? { external_id: window._extid } : {});
    fbq('track', 'PageView'); // лише PageView
  </script>
  <noscript>
    <img height="1" width="1" style="display:none"
         src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1"/>
  </noscript>
@endif
