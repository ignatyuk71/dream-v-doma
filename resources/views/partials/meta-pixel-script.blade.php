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

    // -----------------------
    // 1) Визначення джерела Meta Ads
    // -----------------------
    function getParam(name) {
      try {
        return new URLSearchParams(location.search).get(name);
      } catch (e) { return null; }
    }

    function getRefHost() {
      try {
        return new URL(document.referrer).hostname || '';
      } catch (e) { return ''; }
    }

    var fbclid = getParam('fbclid');
    var refHost = getRefHost();

    // Рекламні редіректи Meta + fbclid у URL
    var isMetaAdRef = /^(l|lm)\.(facebook|instagram)\.com$/i.test(refHost);
    var isMetaAdClick = !!fbclid || isMetaAdRef;

    // OPTIONAL: якщо хочеш ще й органіку Meta (коментар знизу)
    // var isMetaAny = isMetaAdClick || /(facebook|instagram)\.com$/i.test(refHost);

    // -----------------------
    // 2) Bootstrap FB Pixel (тільки якщо потенційно будемо щось слати)
    //    (можеш завжди грузити fbq, але подію шлемо лише при isMetaAdClick)
    // -----------------------
    !function(f,b,e,v,n,t,s){
      if(f.fbq) return;
      n=f.fbq=function(){ n.callMethod ? n.callMethod.apply(n,arguments) : n.queue.push(arguments) };
      if(!f._fbq) f._fbq=n;
      n.push=n; n.loaded=!0; n.version='2.0'; n.queue=[];
      t=b.createElement(e); t.async=!0; t.src=v;
      s=b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t,s);
    }(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');

    // Один eventId для браузерного та CAPI (дедуп)
    var mpPvEventId = 'pv-' + Math.random().toString(16).slice(2) + '-' + Date.now();

    // -----------------------
    // 3) Вогонь: шлемо ТІЛЬКИ для реклами Meta
    // -----------------------
    if (isMetaAdClick) {
      try {
        fbq('init', '{{ $pixelId }}');
        fbq('track', 'PageView', {}, { eventID: mpPvEventId });
      } catch (e) { /* ignore */ }

      // CAPI з невеликою затримкою (щоб _fbp/_fbc встигли встановитись)
      @if ($sendCapiPv)
      (function(){
        var DELAY_MS = 1500;
        setTimeout(function(){
          var payload = JSON.stringify({
            event_id: mpPvEventId,
            page_url: location.href,
            referrer: document.referrer || null,
            fbclid: fbclid || null
          });

          var sent = false;
          if (navigator.sendBeacon) {
            try {
              sent = navigator.sendBeacon('/api/track/pv', new Blob([payload], { type: 'application/json' }));
            } catch(e) { /* ignore */ }
          }
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
    } else {
      // Не Meta-реклама — нічого не шлемо
      // console.debug('PageView заблоковано: не Meta Ads', { refHost, fbclid });
    }

    // -----------------------
    // 4) (Опційно) якщо у тебе SPA — додай аналогічну перевірку на роут-чендж
    // -----------------------
    // window._mpSendPvOnRoute = function(){
    //   var fbclidNow = getParam('fbclid');
    //   var refNow = getRefHost();
    //   var isMetaAdNow = !!fbclidNow || /^(l|lm)\.(facebook|instagram)\.com$/i.test(refNow);
    //   if (!isMetaAdNow) return;
    //
    //   var id = 'pv-' + Math.random().toString(16).slice(2) + '-' + Date.now();
    //   try { fbq('track', 'PageView', {}, { eventID: id }); } catch(e){}
    //   @if ($sendCapiPv)
    //   var payload = JSON.stringify({ event_id: id, page_url: location.href, referrer: document.referrer || null, fbclid: fbclidNow || null });
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

