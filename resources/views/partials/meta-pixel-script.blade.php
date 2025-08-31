@php
  use Illuminate\Support\Facades\DB;

  $t = DB::table('tracking_settings')->first();

  $pixelId  = $t?->pixel_id ?? null;
  $currency = $t?->default_currency ?? 'UAH';

  // Чи вмикати піксель на цій сторінці
  $enabled  = $t
    && (int)($t?->pixel_enabled ?? 0) === 1
    && !empty($pixelId)
    && !((int)($t?->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  // Прапорці (беремо з БД; якщо поле відсутнє — true, крім lead)
  $flags = [
    'pv'   => (int)($t?->send_page_view          ?? 1) === 1,
    'vc'   => (int)($t?->send_view_content       ?? 1) === 1,
    'atc'  => (int)($t?->send_add_to_cart        ?? 1) === 1,
    'ic'   => (int)($t?->send_initiate_checkout  ?? 1) === 1,
    'pur'  => (int)($t?->send_purchase           ?? 1) === 1,
    'lead' => (int)($t?->send_lead               ?? 0) === 1,
  ];

  $requireConsent = (int)($t?->require_consent ?? 0) === 1;
  $testCode       = $t?->capi_test_code ?? null; // щоб серверні події були видні у Test Events
@endphp

<script>
  // ---- експорт у вікно ----
  window._mpEnabled        = @json($enabled);
  window._mpPixelId        = @json($pixelId);
  window.metaPixelCurrency = @json($currency);
  window._mpFlags          = @json($flags, JSON_UNESCAPED_UNICODE);
  window._mpRequireConsent = @json($requireConsent);
  window._mpTestCode       = @json($testCode);

  // ✅ стабільний cookie helper без регулярок
  window._mpCookie = function(name){
    if (!document.cookie) return null;
    var parts = document.cookie.split('; ');
    for (var i = 0; i < parts.length; i++) {
      if (parts[i].indexOf(name + '=') === 0) {
        return decodeURIComponent(parts[i].substring(name.length + 1));
      }
    }
    return null;
  };

  // генератор event_id (узгоджений із беком)
  window._mpMakeEventId = function(name){
    try {
      var box = new Uint8Array(6);
      (window.crypto || window.msCrypto).getRandomValues(box);
      var rnd = Array.from(box).map(function(b){ return b.toString(16).padStart(2,'0'); }).join('');
      return name + '-' + rnd + '-' + Math.floor(Date.now()/1000);
    } catch (_e) {
      return name + '-' + Math.random().toString(16).slice(2,14) + '-' + Math.floor(Date.now()/1000);
    }
  };
</script>

@if ($enabled)
  <!-- Meta Pixel + CAPI PageView (дедуп через однаковий event_id) -->
  <script>
  (function(){
    // Не ініціалізуємо, якщо потрібна згода і її ще не дали
    if (window._mpRequireConsent && !(window.__consent && window.__consent.ad_storage === 'granted')) {
      return;
    }

    // safeguard від повторної ініціалізації (SPA/partial reload)
    if (window._mpPixelInited) return;
    window._mpPixelInited = true;

    // 1) підключення fbq
    if (!window.fbq) {
      !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
      n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
      n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
      t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script',
      'https://connect.facebook.net/en_US/fbevents.js');
    }

    // 2) спільний event_id для дедупу
    var eid = window._mpMakeEventId('pv');
    try { window._mpLastEventId = window._mpLastEventId || {}; window._mpLastEventId['PageView'] = eid; } catch(_){}

    // 3) браузерний PageView (якщо дозволено)
    if (window._mpFlags && window._mpFlags.pv !== false) {
      fbq('init', window._mpPixelId);
      fbq('track', 'PageView', { event_id: eid });
      if (window.DEBUG_PIXEL) console.log('[Pixel] PageView', {event_id: eid});
    }

    // 4) паралельно — CAPI PageView на бек (той самий event_id)
    try {
      var body = {
        event_id: eid,
        event_time: Math.floor(Date.now()/1000),
        event_source_url: window.location.href,
        fbp: window._mpCookie('_fbp') || undefined,
        fbc: window._mpCookie('_fbc') || undefined
      };
      if (window._mpTestCode) body.test_event_code = window._mpTestCode;

      fetch('/api/track/pv', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        keepalive: true,
        body: JSON.stringify(body)
      })
      .then(function(r){ return r.json().catch(function(){return {}}); })
      .then(function(j){
        if (window.DEBUG_PIXEL) console.log('[CAPI] PageView', j);
      })
      .catch(function(e){
        if (window.DEBUG_PIXEL) console.warn('[CAPI] PageView ERR', e);
      });

    } catch (e) {
      if (window.DEBUG_PIXEL) console.warn('[CAPI] PageView exception', e);
    }
  })();
  </script>
@endif
