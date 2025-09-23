{{-- resources/views/partials/meta-pixel.blade.php --}}
@php
  use Illuminate\Support\Facades\DB;

  // 1) Налаштування з БД
  $t = DB::table('tracking_settings')->first();

  $pixelId  = $t?->pixel_id ?? null;
  $currency = $t?->default_currency ?? 'UAH';

  // Глобове вкл/викл Pixel + не адмін
  $enabled  = $t
    && (int)($t?->pixel_enabled ?? 0) === 1
    && !empty($pixelId)
    && !((int)($t?->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  // Прапорці подій
  $flags = [
    'pv'   => (bool)($t?->send_page_view          ?? true),
    'vc'   => (bool)($t?->send_view_content       ?? true),
    'atc'  => (bool)($t?->send_add_to_cart        ?? true),
    'ic'   => (bool)($t?->send_initiate_checkout  ?? true),
    'pur'  => (bool)($t?->send_purchase           ?? true),
    'lead' => (bool)($t?->send_lead               ?? false),
  ];
  if (!$enabled) {
    $flags = ['pv'=>false,'vc'=>false,'atc'=>false,'ic'=>false,'pur'=>false,'lead'=>false];
  }

  $testCode = $t?->capi_test_code ?? null;
@endphp

<script>
  // 2) Глобальні змінні для фронта
  window._mpFlags          = @json($flags, JSON_UNESCAPED_UNICODE);
  window.metaPixelCurrency = @json($currency);
  window._mpEnabled        = @json($enabled);
  window._mpPixelId        = @json($pixelId);
  window._mpTestCode       = @json($testCode);

  // 3) Хелпери
  window._mpGenEventId = function(name){
    try {
      var a = new Uint8Array(6);
      (window.crypto || window.msCrypto).getRandomValues(a);
      var hex = Array.from(a).map(function(b){ return b.toString(16).padStart(2,'0') }).join('');
      return (name || 'ev') + '-' + hex + '-' + Math.floor(Date.now()/1000);
    } catch (_) {
      return (name || 'ev') + '-' + Math.random().toString(16).slice(2) + '-' + Math.floor(Date.now()/1000);
    }
  };
  window._mpGetCookie = function(n){
    var m = document.cookie.match(new RegExp('(?:^|;\\s*)' + n + '=([^;]+)'));
    return m ? decodeURIComponent(m[1]) : null;
  };
  window._mpGetParam = function(name){
    var m = location.search.match(new RegExp('[?&]'+name+'=([^&]+)'));
    return m ? m[1] : null;
  };
  // FB/IG-трафік: є fbclid у URL або _fbc у cookie
  window._mpIsFbTraffic = function(){
    return !!(_mpGetCookie('_fbc') || _mpGetParam('fbclid'));
  };
  // Фінальний URL та реферер (для діагностики)
  window._mpFinalUrl = function(){ return window.location.href; };
  window._mpRef      = function(){ return document.referrer || null; };
</script>

@if ($enabled)
  <!-- 4) Браузерний Pixel + CAPI PageView для FB/IG-трафіку з дедупом -->
  <script>
  (function(){
    if (!window._mpFlags || window._mpFlags.pv === false) return;
    if (!window._mpIsFbTraffic()) return;

    // 4.1 Ініт Pixel один раз
    if (!window._mpFbqInited) {
      !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
      n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
      n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
      t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script',
      'https://connect.facebook.net/en_US/fbevents.js');

      fbq('init', '{{ $pixelId }}');
      window._mpFbqInited = true;
    }

    // 4.2 Спільний eventID для дедупу
    var pvId = window._mpPVId || (window._mpPVId = window._mpGenEventId('pv'));

    // 4.3 Невелика затримка, щоб URL устаканився (редиректи/SPA)
    setTimeout(function(){
      var finalUrl = window._mpFinalUrl();

      // 4.4 Браузерний PageView (додаємо лише діагностику)
      fbq('track', 'PageView', {
        page_url: finalUrl,        // діагностичне поле (видно в події)
        referrer: window._mpRef(), // діагностичне поле
      }, { eventID: pvId });

      // 4.5 CAPI PageView (той самий event_id і URL)
      var payload = {
        event_id: pvId,
        event_time: Math.floor(Date.now()/1000),
        event_source_url: finalUrl
        // fbc/fbp не передаємо – бекенд бере з cookies (_fbc/_fbp)
      };
      if (window._mpTestCode) payload.test_event_code = window._mpTestCode;

      try {
        var body = JSON.stringify(payload);
        if (navigator.sendBeacon) {
          navigator.sendBeacon('/api/track/pv', new Blob([body], {type:'application/json'}));
        } else {
          fetch('/api/track/pv', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body,
            keepalive: true
          });
        }
      } catch (_) {}
    }, 150); // 100–200 мс зазвичай достатньо
  })();
  </script>
@endif
