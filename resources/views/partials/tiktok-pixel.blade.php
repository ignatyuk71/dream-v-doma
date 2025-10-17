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

    // ---------- detect ONLY by referrer ----------
    var refHost      = getRefHost();
    var isTiktokRef  = /^(t|m|ads|business)\.tiktok\.com$/i.test(refHost);

    // прапор через cookie (без sessionStorage)
    var TT_FLAG_NAME = '_tt_ad';
    var TT_FLAG_TTL  = 7 * 24 * 60 * 60; // 7 днів (можеш змінити)
    var hasTtFlagCookie = getCookie(TT_FLAG_NAME) === '1';

    // якщо зараз чіткий TikTok referrer — ставимо cookie
    if (isTiktokRef) {
      setCookie(TT_FLAG_NAME, '1', TT_FLAG_TTL);
      hasTtFlagCookie = true;
    }

    // ✅ дозволяємо TikTok Pixel ТІЛЬКИ за referrer або вже виставленою cookie
    var allowTiktokPV = isTiktokRef || hasTtFlagCookie;

    // глобальний прапор + безпечний хелпер для інших подій
    window._mpTtEnabled = !!allowTiktokPV;
    window.sendTtEvent = function(name, params){
      if (!window._mpTtEnabled) return;
      try { window.ttq && ttq.track && ttq.track(name, params || {}); } catch(e){}
    };

    if (!allowTiktokPV) {
      // Не TikTok Ads — не вантажимо SDK і не шлемо події
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
      ttq.page(); // піде лише якщо allowTiktokPV === true
    }(window, document, 'ttq');
  })();
</script>
@endif
@endonce
