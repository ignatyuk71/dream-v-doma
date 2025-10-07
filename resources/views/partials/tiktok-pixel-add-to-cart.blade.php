{{-- resources/views/partials/tiktok-pixel-add-to-cart.blade.php --}}
@php
  /** @var \App\Models\Product $product */
  $loc = app()->getLocale();

  // Локалізована назва товару (для фолу-беку)
  $tr = $product->translations->firstWhere('locale', $loc)
      ?? $product->translations->firstWhere('locale', 'uk')
      ?? $product->translations->first();

  $fallbackName = $tr?->name ?? $product->sku;

  // Головна категорія (локалізовано) — опційно для payload
  $cat = isset($category) ? $category : ($product->categories->first());
  $catTr = $cat?->translations?->firstWhere('locale', $loc)
        ?? $cat?->translations?->first();
  $fallbackCategory = $catTr?->name ?? null;

  // Валюта з урахуванням локалі / твого глобального сетапу
  $fallbackCurrency = match($loc) {
      'pl' => 'PLN',
      default => 'UAH',
  };

  $BASE = [
    'name'     => (string) $fallbackName,
    'category' => $fallbackCategory ?: null,
    'currency' => $fallbackCurrency,
  ];
@endphp

@if(isset($product))
<script>
/**
 * TikTok AddToCart (browser pixel only)
 * Цей скрипт "обгортає" твою window.mpTrackATC так, щоб паралельно відправляти AddToCart у TikTok Pixel.
 * Нічого не ламає, просто додає ще один трек.
 *
 * ВАЖЛИВО: підключай цей partial ДО того місця, де викликається mpTrackATC (зазвичай унизу сторінки перед JS-бандлом теж ок).
 */
(function () {
  // ── Заготівки з бекенду (фолбеки)
  const BASE = @json($BASE, JSON_UNESCAPED_UNICODE);

  // ── Антидубль/антиспам (захист від подвійних кліків)
  let _ttLastAtcTs = 0;

  /**
   * Відправка події в TikTok Pixel
   * args очікує поля:
   * - variant_sku (обов'язково), price (number), quantity (number),
   * - name? (якщо не передаси — візьмемо з BASE), category? (BASE), currency? (BASE),
   * - size?, color? (додаються як item_variant у contents[0])
   */
  function trackTikTokATC(args) {
    if (!(window.ttq && typeof window.ttq.track === 'function')) {
      console.warn('[TikTok] ttq не знайдений — AddToCart не надіслано');
      return;
    }

    const now = Date.now();
    if (now - _ttLastAtcTs < 300) return; // 300мс антидубль
    _ttLastAtcTs = now;

    const variantSku = String(args?.variant_sku ?? '').trim();
    if (!variantSku) {
      console.warn('[TikTok] Пропущено: пустий variant_sku');
      return;
    }

    const unitPrice = Number(args?.price ?? 0);
    const qty       = Math.max(1, Number(args?.quantity ?? 1));
    const name      = String(args?.name ?? BASE.name ?? variantSku);
    const category  = args?.category ?? BASE.category ?? undefined;
    const currency  = String(args?.currency ?? BASE.currency ?? 'UAH');

    const payload = {
      content_id: variantSku,
      content_type: 'product',
      content_name: name,
      content_category: category,
      value: unitPrice * qty,
      currency,
      contents: [{
        content_id: variantSku,
        content_type: 'product',
        content_name: name,
        quantity: qty,
        price: unitPrice
      }]
    };

    // Додамо позначення варіанта (size/color) — приємно мати в аналітиці
    const itemVariant = [args?.size, args?.color].filter(Boolean).join(' ').trim();
    if (itemVariant) payload.contents[0].item_variant = itemVariant;

    ttq.track('AddToCart', payload);
    // Залиш лог на час тестів
    console.log('[TikTok] AddToCart payload', payload);
  }

  /**
   * Акуратний "обгортальник" над window.mpTrackATC:
   * - якщо mpTrackATC існує — загортаємо та викликаємо обидва (спочатку твій, потім TikTok).
   * - якщо не існує — створимо "сумісну" функцію, щоб фронт міг її викликати як завжди.
   * Обгортання робимо ОДИН РАЗ.
   */
  (function wrapMpTrackATCOnce() {
    if (window.__ttWrappedMpAtc) return;
    window.__ttWrappedMpAtc = true;

    const original = typeof window.mpTrackATC === 'function' ? window.mpTrackATC : null;

    window.mpTrackATC = function (data) {
      try {
        // 1) Викликаємо твою оригінальну аналітику (якщо була)
        if (original) original.call(this, data);
      } catch (err) {
        console.warn('[mpTrackATC original error]', err);
      }

      try {
        // 2) TikTok AddToCart
        // Очікуємо структуру { variant_sku, price, quantity, name, currency, size?, color?, category? }
        trackTikTokATC(data || {});
      } catch (err) {
        console.warn('[TikTok ATC error]', err);
      }
    };
  })();

  /**
   * Резервний варіант: якщо в розмітці є кнопки з data-tt-add-to-cart,
   * можна відправити подію й без mpTrackATC (не обов'язково, але хай буде).
   * Щоб не заважати, делегат увімкнуто, але сам він нічого не перехоплює,
   * якщо у вас немає таких атрибутів у DOM.
   */
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-tt-add-to-cart]');
    if (!btn) return;

    const variant_sku = btn.getAttribute('data-variant-sku') || btn.getAttribute('data-sku') || '';
    const price       = Number(btn.getAttribute('data-price') || 0);
    const quantity    = Number(btn.getAttribute('data-qty') || 1);
    const name        = btn.getAttribute('data-name') || BASE.name;
    const category    = btn.getAttribute('data-category') || BASE.category;
    const currency    = btn.getAttribute('data-currency') || BASE.currency;

    trackTikTokATC({ variant_sku, price, quantity, name, category, currency });
  }, { passive: true });
})();
</script>
@endif
