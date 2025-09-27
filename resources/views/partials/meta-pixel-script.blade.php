@php
  use Illuminate\Support\Facades\DB;

  $t = DB::table('tracking_settings')->first();

  $pixelId  = $t?->pixel_id ?? null;

  $enabled  = $t
    && (int)($t?->pixel_enabled ?? 0) === 1
    && !empty($pixelId)
    && !((int)($t?->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  $pvEnabled = $enabled && (bool)($t?->send_page_view ?? true);
@endphp

@if ($pvEnabled)
  <!-- Meta Pixel (лише браузерний PageView + SPA-хук) -->
  <script>
    // (опц.) external_id у cookie для advanced matching
    (function () {
      try {
        var name = '_extid';
        if (!document.cookie.match(new RegExp('(?:^|;\\s*)' + name + '='))) {
          var src = (window.crypto && crypto.getRandomValues) ? crypto.getRandomValues(new Uint8Array(16)) : Array.from({length:16},()=>Math.floor(Math.random()*256));
          var r = Array.from(src).map(b=>('0'+b.toString(16)).slice(-2)).join('');
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

    // Ініт (можеш прибрати external_id, якщо не треба)
    fbq('init', '{{ $pixelId }}', window._extid ? { external_id: window._extid } : {});

    // 1) первинний PageView (на завантаженні сторінки)
    fbq('track', 'PageView');

    // 2) SPA-хук: тригеримо PageView при зміні history (push/replace/back/forward)
    (function(){
      if (window.__mpSpaHooked) return; // щоб не дублювати при повторних інжектах
      window.__mpSpaHooked = true;

      var lastUrl = location.href;

      function firePVIfChanged() {
        var now = location.href;
        if (now !== lastUrl) {
          lastUrl = now;
          try { fbq('track', 'PageView'); } catch (_) {}
        }
      }

      // Обгортаємо pushState / replaceState
      ['pushState','replaceState'].forEach(function(fn){
        var orig = history[fn];
        if (!orig) return;
        history[fn] = function(){
          var ret = orig.apply(this, arguments);
          // Даємо історії оновити адресу, тоді шлемо PV
          setTimeout(firePVIfChanged, 0);
          return ret;
        };
      });

      // Назад/вперед
      window.addEventListener('popstate', function(){ setTimeout(firePVIfChanged, 0); });

      // (опц.) публічний хелпер, якщо хочеш вручну штовхнути PV після свого роутера:
      window.mpPageView = function(){ try{ fbq('track','PageView'); }catch(_){ } };
    })();
  </script>
  <noscript>
    <img height="1" width="1" style="display:none"
         src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1"/>
  </noscript>
@endif
