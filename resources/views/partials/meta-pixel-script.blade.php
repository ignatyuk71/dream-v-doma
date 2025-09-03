@php
  use Illuminate\Support\Facades\DB;

  // Читаємо налаштування трекінгу (може бути null)
  $t = DB::table('tracking_settings')->first();

  // Базові параметри (без фаталів, завдяки ?->)
  $pixelId  = $t?->pixel_id ?? null;
  $currency = $t?->default_currency ?? 'UAH';

  // Глобовий перемикач Pixel: увімкнено + є pixel_id + не адмін-URL
  $enabled  = $t
    && (int)($t?->pixel_enabled ?? 0) === 1
    && !empty($pixelId)
    && !((int)($t?->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  // Прапорці подій (за замовчуванням true, окрім lead)
  $flags = [
    'pv'   => (bool)($t?->send_page_view          ?? true),
    'vc'   => (bool)($t?->send_view_content       ?? true),
    'atc'  => (bool)($t?->send_add_to_cart        ?? true),
    'ic'   => (bool)($t?->send_initiate_checkout  ?? true),
    'pur'  => (bool)($t?->send_purchase           ?? true),
    'lead' => (bool)($t?->send_lead               ?? false),
  ];

  // Якщо Pixel глобально вимкнено — глушимо всі події
  if (!$enabled) {
    $flags = ['pv'=>false,'vc'=>false,'atc'=>false,'ic'=>false,'pur'=>false,'lead'=>false];
  }

  // Тест-код із Events Manager (необов'язковий)
  $testCode = $t?->capi_test_code ?? null;
@endphp

<script>
  // Глобальні прапорці та налаштування (видимі на фронті)
  window._mpFlags          = @json($flags, JSON_UNESCAPED_UNICODE);
  window.metaPixelCurrency = @json($currency);
  window._mpEnabled        = @json($enabled);
  window._mpPixelId        = @json($pixelId);
  window._mpTestCode       = @json($testCode);

  // Генератор спільних event_id для дедупу Pixel↔CAPI
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

  // Хелпер читання cookie
  window._mpGetCookie = function(n){
    return document.cookie.split('; ').find(function(r){ return r.indexOf(n+'=')===0 })?.split('=')[1] || null;
  };
</script>

@if ($enabled)
  <!-- Meta Pixel (браузерний) -->
  <script>
  !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
  n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script',
  'https://connect.facebook.net/en_US/fbevents.js');

  fbq('init', '{{ $pixelId }}');

  // Browser PageView із спільним event_id (для дедупу з CAPI)
  (function(){
    if (window._mpFlags && window._mpFlags.pv === false) return;
    var pvId = window._mpPVId || (window._mpPVId = window._mpGenEventId('pv'));
    fbq('track', 'PageView', {}, { eventID: pvId });
  })();
  </script>
@endif

{{-- CAPI PageView (той самий event_id, що й у Pixel) --}}
<script>
(function(){
  if (!window._mpFlags || window._mpFlags.pv === false) return;

  var pvId = window._mpPVId || (window._mpPVId = window._mpGenEventId('pv'));

  // Акуратне декодування cookie (fbp/fbc можуть бути URL-encoded)
  var safeDecode = function(c){ try { return c ? decodeURIComponent(c) : null } catch(_) { return c } };
  var fbp = safeDecode(window._mpGetCookie('_fbp'));
  var fbc = safeDecode(window._mpGetCookie('_fbc'));

  var payload = {
    event_id: pvId,
    event_time: Math.floor(Date.now()/1000),
    event_source_url: window.location.href,
    // user_data додається на бекенді (IP/UA + хешовані поля за наявності)
    fbp: fbp,
    fbc: fbc
  };

  // Додаємо test_event_code, якщо задано в БД
  if (window._mpTestCode) payload.test_event_code = window._mpTestCode;

  try {
    var body = JSON.stringify(payload);
    // Надійна відправка перед навігацією: sendBeacon або keepalive fetch
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
})();
</script>
