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

    // ---------- helpers ----------
    function getParam(name) {
      try { return new URLSearchParams(location.search).get(name); }
      catch(e){ return null; }
    }
    function getRefHost() {
      try { return new URL(document.referrer).hostname || ''; }
      catch(e){ return ''; }
    }
    function setCookie(name, value, maxAgeSec) {
      var parts = [name + '=' + encodeURIComponent(value), 'Path=/','SameSite=Lax'];
      if (maxAgeSec) parts.push('Max-Age=' + maxAgeSec);
      // якщо сайт на HTTPS — додай Secure:
      if (location.protocol === 'https:') parts.push('Secure');
      document.cookie = parts.join('; ');
    }
    function getCookie(name) {
      return document.cookie.split('; ').reduce(function(acc, pair){
        var idx = pair.indexOf('=');
        var k = pair.slice(0, idx), v = pair.slice(idx+1);
        if (k === name) acc = decodeURIComponent(v);
        return acc;
      }, null);
    }
    function delCookie(name) {
      document.cookie = name + '=; Path=/; Max-Age=0; SameSite=Lax' + (location.protocol==='https:'?'; Secure':'');
    }

    // ---------- detect sources ----------
    var fbclid = getParam('fbclid');
    var ttclid = getParam('ttclid'); // TikTok
    var refHost = getRefHost();

    var isMetaRef = /^(l|lm)\.(facebook|instagram)\.com$/i.test(refHost);
    var isMetaAdClickNow = !!fbclid || isMetaRef;

    // прапор: чи вже знаємо, що це сесія з Meta Ads
    var META_FLAG_NAME = '_meta_ad';
    var META_FLAG_TTL = 7 * 24 * 60 * 60; // 7 днів (зміни за потреби)
    var hasMetaFlag = getCookie(META_FLAG_NAME) === '1';

    // якщо зараз явний Meta Ad click — фіксуємо прапор
    if (isMetaAdClickNow) {
      setCookie(META_FLAG_NAME, '1', META_FLAG_TTL);
      hasMetaFlag = true;
    }

    // якщо зараз явний TikTok click — приберемо Meta прапор (щоб не змішувати)
    if (ttclid) {
      delCookie(META_FLAG_NAME);
      hasMetaFlag = false;
    }

    // дозволяємо відправку подій, якщо:
    //  - поточний перехід Meta Ads АБО
    //  - раніше у сесії вже позначено як Meta Ads
    var allowMetaPV = isMetaAdClickNow || hasMetaFlag;

    // ---------- bootstrap pixel (лише якщо потенційно шлемо) ----------
    if (allowMetaPV) {
      !function(f,b,e,v,n,t,s){
        if(f.fbq) return;
        n=f.fbq=function(){ n.callMethod ? n.callMethod.apply(n,arguments) : n.queue.push(arguments) };
        if(!f._fbq) f._fbq=n;
        n.push=n; n.loaded=!0; n.version='2.0'; n.queue=[];
        t=b.createElement(e); t.async=!0; t.src=v;
        s=b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t,s);
      }(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');

      // один eventId для дедупу
      var mpPvEventId = 'pv-' + Math.random().toString(16).slice(2) + '-' + Date.now();

      // Браузерний PageView — без затримки
      try {
        fbq('init', '{{ $pixelId }}');
        fbq('track', 'PageView', {}, { eventID: mpPvEventId });
      } catch(e){}

      // CAPI з малою затримкою (щоб _fbp/_fbc встигли з'явитись)
      @if ($sendCapiPv)
      (function(){
        var DELAY_MS = 1500;
        setTimeout(function(){
          var payload = JSON.stringify({
            event_id: mpPvEventId,
            page_url: location.href,
            referrer: document.referrer || null,
            fbclid: fbclid || null,
            ttclid: ttclid || null,
            meta_session: hasMetaFlag ? 1 : 0
          });

          var sent = false;
          if (navigator.sendBeacon) {
            try {
              sent = navigator.sendBeacon('/api/track/pv', new Blob([payload], { type: 'application/json' }));
            } catch(e){}
          }
          if (!sent) {
            try {
              fetch('/api/track/pv', {
                method: 'POST',
                keepalive: true,
                headers: { 'Content-Type': 'application/json' },
                body: payload
              }).catch(function(){});
            } catch(e){}
          }
        }, DELAY_MS);
      })();
      @endif
    } else {
      // console.debug('PageView заблоковано (не Meta Ads):', { refHost, fbclid, ttclid, hasMetaFlag });
    }

    // ---------- (опційно) для SPA викликайте на зміну маршруту ----------
    // window._mpSendPvOnRoute = function(){
    //   var hasFlag = getCookie(META_FLAG_NAME) === '1';
    //   if (!hasFlag) return;
    //
    //   var id = 'pv-' + Math.random().toString(16).slice(2) + '-' + Date.now();
    //   try { fbq('track', 'PageView', {}, { eventID: id }); } catch(e){}
    //   @if ($sendCapiPv)
    //   var payload = JSON.stringify({ event_id: id, page_url: location.href, referrer: document.referrer || null, meta_session: 1 });
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