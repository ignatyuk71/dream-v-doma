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

    // ---------------- helpers ----------------
    function getParam(name) {
      try { return new URLSearchParams(location.search).get(name); }
      catch(e){ return null; }
    }
    function getRefHost() {
      try { return new URL(document.referrer).hostname || ''; }
      catch(e){ return ''; }
    }
    function setCookie(name, value, maxAgeSec) {
      var parts = [name + '=' + encodeURIComponent(value), 'Path=/', 'SameSite=Lax'];
      if (maxAgeSec) parts.push('Max-Age=' + maxAgeSec);
      if (location.protocol === 'https:') parts.push('Secure');
      document.cookie = parts.join('; ');
    }
    function getCookie(name) {
      // швидкий парсер куки
      var m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/[$()*+./?[\\\]^{|}-]/g,'\\$&') + '=([^;]*)'));
      return m ? decodeURIComponent(m[1]) : null;
    }
    function delCookie(name) {
      document.cookie = name + '=; Path=/; Max-Age=0; SameSite=Lax' + (location.protocol==='https:'?'; Secure':'');
    }

    // ------------- detect sources -------------
    var fbclid  = getParam('fbclid');
    var ttclid  = getParam('ttclid'); // TikTok
    var refHost = getRefHost();

    // Рекламні редіректи Meta
    var isMetaRef        = /^(l|lm)\.(facebook|instagram)\.com$/i.test(refHost);
    var isMetaAdClickNow = !!fbclid || isMetaRef;

    // _fbc ставиться Meta лише коли був fbclid (формат: fb.1.<ts>.<fbclid>)
    var fbcCookie = getCookie('_fbc');
    var hasFbc    = !!(fbcCookie && /^fb\.1\.\d+\.[A-Za-z0-9_-]+$/.test(fbcCookie));

    // Прапор сесії Meta
    var META_FLAG_NAME = '_meta_ad';
    var META_FLAG_TTL  = 7 * 24 * 60 * 60; // 7 днів (за потреби зміни на 86400)
    var hasMetaFlagCookie = getCookie(META_FLAG_NAME) === '1';
    var hasMetaFlagSS = false;
    try { hasMetaFlagSS = sessionStorage.getItem(META_FLAG_NAME) === '1'; } catch(e){}

    // Якщо зараз явний Meta-клік або вже є _fbc — фіксуємо прапор
    if (isMetaAdClickNow || hasFbc) {
      setCookie(META_FLAG_NAME, '1', META_FLAG_TTL);
      try { sessionStorage.setItem(META_FLAG_NAME, '1'); } catch(e){}
      hasMetaFlagCookie = true;
      hasMetaFlagSS = true;
    }

    // Якщо явний TikTok click — глушимо Meta-прапор (щоб не змішувати атрибуцію)
    if (ttclid) {
      delCookie(META_FLAG_NAME);
      try { sessionStorage.removeItem(META_FLAG_NAME); } catch(e){}
      hasMetaFlagCookie = false;
      hasMetaFlagSS = false;
    }

    // Дозволяємо PageView, якщо:
    //  - поточний Meta Ad click (fbclid або l./lm. реферер),
    //  - або присутня _fbc (ознака попереднього fbclid),
    //  - або вже стоїть наш прапор (cookie / sessionStorage).
    var allowMetaPV = isMetaAdClickNow || hasFbc || hasMetaFlagCookie || hasMetaFlagSS;

    // ----------- fire Pixel/CAPI only for Meta Ads -----------
    if (allowMetaPV) {
      // Bootstrap FB Pixel
      !function(f,b,e,v,n,t,s){
        if(f.fbq) return;
        n=f.fbq=function(){ n.callMethod ? n.callMethod.apply(n,arguments) : n.queue.push(arguments) };
        if(!f._fbq) f._fbq=n;
        n.push=n; n.loaded=!0; n.version='2.0'; n.queue=[];
        t=b.createElement(e); t.async=!0; t.src=v;
        s=b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t,s);
      }(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');

      // Дедуп для браузер+сервер
      var mpPvEventId = 'pv-' + Math.random().toString(16).slice(2) + '-' + Date.now();

      // Браузерний PageView — без затримки
      try {
        fbq('init', '{{ $pixelId }}');
        fbq('track', 'PageView', {}, { eventID: mpPvEventId });
      } catch(e){/* ignore */}

      // CAPI — з легкою затримкою, щоб _fbp/_fbc встигли з’явитися
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
            meta_session: (hasMetaFlagCookie || hasMetaFlagSS) ? 1 : 0
          });

          var sent = false;
          if (navigator.sendBeacon) {
            try {
              sent = navigator.sendBeacon('/api/track/pv', new Blob([payload], { type: 'application/json' }));
            } catch(e){/* ignore */}
          }
          if (!sent) {
            try {
              fetch('/api/track/pv', {
                method: 'POST',
                keepalive: true,
                headers: { 'Content-Type': 'application/json' },
                body: payload
              }).catch(function(){});
            } catch(e){/* ignore */}
          }
        }, DELAY_MS);
      })();
      @endif
    } else {
      // console.debug('PageView заблоковано (не Meta Ads):', { refHost, fbclid, ttclid, hasFbc, hasMetaFlagCookie, hasMetaFlagSS });
    }

    // ---------- (опційно) для SPA на зміну маршруту ----------
    // window._mpSendPvOnRoute = function(){
    //   var hasFlag = (getCookie(META_FLAG_NAME) === '1');
    //   var hasFlagSS = false;
    //   try { hasFlagSS = sessionStorage.getItem(META_FLAG_NAME) === '1'; } catch(e){}
    //   if (!(hasFlag || hasFlagSS || getCookie('_fbc'))) return;
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
