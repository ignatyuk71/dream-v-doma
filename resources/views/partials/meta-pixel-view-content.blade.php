@php
  use Illuminate\Support\Facades\DB;

  // 1) Тягнемо налаштування трекінгу з БД
  $t = DB::table('tracking_settings')->first();

  // 2) Перевіряємо, чи піксель взагалі увімкнено і чи є pixel_id.
  //    Також не стріляємо на адмін-URL, якщо увімкнено exclude_admin.
  $pixelOk = $t
    && (int)($t->pixel_enabled ?? 0) === 1
    && !empty($t->pixel_id)
    && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));

  // 3) Дозвіл саме на ViewContent (можна вимкнути окремо в БД)
  $allowVC = $pixelOk && (int)($t->send_view_content ?? 1) === 1;

  // 4) Дістаємо локалізовані дані товару (назву) з перекладів
  $locale = app()->getLocale() ?: 'uk';
  $tr = ($product->translations ?? collect())
          ->firstWhere('locale', $locale)
        ?? ($product->translations ?? collect())->firstWhere('locale','uk')
        ?? ($product->translations ?? collect())->firstWhere('locale','ru');

  // 5) Формуємо поля для події:
  //    - contentId: краще SKU (щоб збігався з каталогом), інакше беремо id
  //    - contentName: локалізована назва
  //    - category: назва категорії (якщо є)
  //    - price & currency: ціна і валюта
  $contentId   = (string)($product->sku ?? $product->id ?? '');
  $contentName = (string)($tr->name ?? $product->name ?? '');
  $contentCat  = (string)($product->category->name ?? $product->category_name ?? '');
  $price       = isset($product->price) ? round((float)$product->price, 2) : null;
  $currency    = strtoupper($t?->default_currency ?? 'UAH'); // нормалізуємо валюту до UPPERCASE

  // 6) Чи дозволено CAPI: окремий прапорець + наявність токена
  $capiEnabled = $t && (int)($t->capi_enabled ?? 0) === 1 && !empty($t->capi_token);
  $sendCapiVc  = $allowVC && $capiEnabled;
@endphp

{{-- 7) Рендеримо скрипт тільки якщо: ViewContent дозволено, є продукт і є contentId --}}
@if ($allowVC && isset($product) && $contentId !== '')
<script>
(function(){
  // 8) Антидубль: стріляємо VC один раз на конкретний товар (sku/id) за сторінку/сесію
  window._vcFired = window._vcFired || {};
  if (window._vcFired['{{ $contentId }}']) return;
  window._vcFired['{{ $contentId }}'] = true;

  // 9) Генеруємо єдиний eventID для браузера і CAPI (потрібно для дедуплікації у Meta)
  var vcEventId = 'vc-' + Math.random().toString(16).slice(2) + '-' + Date.now();

  // 10) Базовий об’єкт даних ViewContent (без “висячих” ком)
  //     Обов’язкове: content_type, content_ids.
  //     Рекомендовано: contents (масив об’єктів з info про товар).
  var data = {
    content_type: 'product',
    content_ids: [@json($contentId)],
    contents: [{ id: @json($contentId), quantity: 1 }]
  };

  // 11) Додаємо назву і категорію, якщо вони є
  @if($contentName !== '') data.content_name = @json($contentName); @endif
  @if($contentCat  !== '') data.content_category = @json($contentCat); @endif

  // 12) Якщо є ціна — додаємо її і валюту
  @if(!is_null($price)) {
    data.contents[0].item_price = {{ $price }};
    data.value = {{ $price }};              // для VC value — ціна одиниці товару
    data.currency = @json($currency);
  } @endif

  // 13) Надсилаємо браузерну подію через Pixel (fbq).
  //     Чекаємо, поки бібліотека fbq завантажиться (ініціалізується глобальним скриптом).
  (function waitFbq(i){
    if (typeof window.fbq === 'function') {
      try {
        fbq('track', 'ViewContent', data, { eventID: vcEventId }); // той самий eventID → дедуп із CAPI
      } catch(e){}
      return;
    }
    if (i > 25) return;             // максимум ~5 секунд очікування
    setTimeout(function(){ waitFbq(i+1); }, 80);
  })(0);

  // 14) Паралельно надсилаємо на наш бекенд для CAPI (якщо дозволено):
  //     - бекенд сформує user_data (IP/UA + fbc/fbp за потреби) і відправить у Meta
  @if ($sendCapiVc)
  (function(){
    // 15) Готуємо payload для нашого API: обов’язково кладемо той самий event_id
    var body = JSON.stringify({
      event_id: vcEventId,
      page_url: location.href,
      product: {
        id: @json((string)($product->id ?? '')),
        sku: @json((string)($product->sku ?? '')),
        name: @json($contentName),
        category: @json($contentCat),
        price: @json($price),
        currency: @json($currency)
      }
    });

    // 16) Спочатку пробуємо відправити через sendBeacon (надійно при закритті вкладки)
    var sent = false;
    if (navigator.sendBeacon) {
      try { sent = navigator.sendBeacon('/api/track/vc', new Blob([body], {type:'application/json'})); } catch(e){}
    }

    // 17) Якщо sendBeacon не спрацював — fallback на fetch з keepalive
    if (!sent) {
      try {
        fetch('/api/track/vc', {
          method: 'POST',
          keepalive: true,
          headers: { 'Content-Type': 'application/json' },
          body
        }).catch(function(){});
      } catch(e){}
    }
  })();
  @endif
})();
</script>
@endif
