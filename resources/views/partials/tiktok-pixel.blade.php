@php
  $enabled = filter_var(env('TIKTOK_PIXEL_ENABLED', true), FILTER_VALIDATE_BOOL);
  $pixelId = env('TIKTOK_PIXEL_ID');
  $excludeAdmin = filter_var(env('TIKTOK_EXCLUDE_ADMIN', true), FILTER_VALIDATE_BOOL);

  if ($excludeAdmin && request()->is('admin*')) {
      $enabled = false;
  }

  $shouldRender = $enabled && !empty($pixelId);
@endphp

@once
@if($shouldRender)
<script>
  (function(){
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
      var parts = [name + '=' + encodeURIComponent(value), 'Path=/', 'SameSite=Lax'];
      if (maxAgeSec) parts.push('Max-Age=' + maxAgeSec);
      if (location.protocol === 'https:') parts.push('Secure');
      document.cookie = parts.join('; ');
    }
    function getCookie(name) {
      var m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/[$()*+./?[\\\]^{|}-]/g,'\\$&') + '=([^;]*)'));
      return m ? decodeURIComponent(m[1]) : null;
    }
    function delCookie(name) {
      document.cookie = name + '=; Path=/; Max-Age=0; SameSite=Lax' + (location.protocol==='https:'?'; Secure':'');
    }

    // ---------- detect TikTok Ads ----------
    var ttclid  = getParam('ttclid');
    var refHost = getRefHost();
    // типові тік-ток редіректи/домен Ads
    var isTiktokRef = /^(t|m|ads|business)\.tiktok\.com$/i.test(refHost);
    var isTiktokAdClickNow = !!ttclid || isTiktokRef;

    // наш прапор сесії TikTok Ads
    var TT_FLAG_NAME = '_tt_ad';
    var TT_FLAG_TTL  = 7 * 24 * 60 * 60; // 7 днів
    var hasTtFlagCookie = getCookie(TT_FLAG_NAME) === '1';
    var hasTtFlagSS = false;
    try { hasTtFlagSS = sessionStorage.getItem(TT_FLAG_NAME) === '1'; } catch(e){}

    // якщо поточний клік з реклами — фіксуємо прапор
    if (isTiktokAdClickNow) {
      setCookie(TT_FLAG_NAME, '1', TT_FLAG_TTL);
      try { sessionStorage.setItem(TT_FLAG_NAME, '1'); } catch(e){}
      hasTtFlagCookie = true;
      hasTtFlagSS = true;
    }

    // (опційно) якщо хочеш глушити TikTok при явному fbclid — розкоментуй:
    // if (new URLSearchParams(location.search).get('fbclid')) {
    //   delCookie(TT_FLAG_NAME);
    //   try { sessionStorage.removeItem(TT_FLAG_NAME); } catch(e){}
    //   hasTtFlagCookie = false;
    //   hasTtFlagSS = false;
    // }

    // дозволяємо TikTok Pixel тільки якщо зараз TikTok Ads або раніше зафіксовано
    var allowTiktokPV = isTiktokAdClickNow || hasTtFlagCookie || hasTtFlagSS;

    // глобальний прапор + безпечний хелпер (для інших подій)
    window._mpTtEnabled = !!allowTiktokPV;
    window.sendTtEvent = function(name, params){
      if (!window._mpTtEnabled) return;
      try { window.ttq && ttq.track && ttq.track(name, params || {}); } catch(e){}
    };

    if (!allowTiktokPV) {
      // Заборонено: не вантажимо TikTok SDK і не шлемо події
      return;
    }

    // ---------- load TikTok SDK + PageView ----------
    !function (w, d, t) {
      w.TiktokAnalyticsObject=t;
      var ttq=w[t]=w[t]||[];
      ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"];
      ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat([].slice.call(arguments,0)))}};
      for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);
      ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e};
      ttq.load=function(e,n){
        var r="https://analytics.tiktok.com/i18n/pixel/events.js",o=n&&n.partner;
        ttq._i=ttq._i||{},ttq._i[e]=[],ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};
        n=d.createElement("script"); n.type="text/javascript"; n.async=!0; n.src=r+"?sdkid="+e+"&lib="+t;
        e=d.getElementsByTagName("script")[0]; e.parentNode.insertBefore(n,e);
      };
      ttq.load(@json($pixelId));
      ttq.page(); // PageView піде лише коли allowTiktokPV === true
    }(window, document, 'ttq');
  })();
</script>
@endif
@endonce
