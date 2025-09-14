<div class="ps-md-4 ps-xl-5">
  <!-- Назва -->
  <h1 class="h3 mt-3">
    {{ $product->translations->firstWhere('locale', app()->getLocale())?->name ?? '—' }}
  </h1>

  <!-- Зірки + Код товару -->
  <div class="d-flex justify-content-between align-items-center mb-2">
    <a class="d-flex align-items-center gap-2 text-decoration-none" href="#reviews">
      <div class="d-flex gap-1 fs-sm">
        @php $rating = $product->average_rating ?? 0; @endphp
        @for ($i = 1; $i <= 5; $i++)
          <i class="ci-star{{ $i <= floor($rating) ? '-filled text-warning' : ($i - $rating < 1 ? '' : ' text-body-terтіary opacity-75') }}"></i>
        @endfor
      </div>
      <span class="text-body-tertiary fs-sm">
        {{ count($product->reviews) }} {{ __('product.reviews') }}
      </span>
    </a>

    <div class="text-muted fs-sm">
      <strong>{{ __('product.code') }}:</strong> {{ $product->sku }}
    </div>
  </div>

  @php
    // ===== Обчислення складу для SSR =====
    $variantQtySum = $product->variants->sum(fn($v) => (int)($v->quantity ?? 0));
    $stockTotal = $variantQtySum > 0 ? $variantQtySum : (int)($product->quantity_in_stock ?? 0);
    $inStockSSR = $stockTotal > 0;
  @endphp

  <!-- Ціна + Наявність -->
  <div class="d-flex flex-wrap align-items-center mb-1">
    <div class="h4 d-flex align-items-center my-4 gap-3">
      <span id="product-price" class="new-price">
        {{ (int) $product->price == $product->price ? number_format($product->price, 0, '.', ' ') : number_format($product->price, 2, '.', ' ') }} грн
      </span>
      <del id="product-old-price" class="old-price d-none"></del>
    </div>

    <!-- 🔄 Статус наявності (SSR + JS оновлення) -->
    <div id="stock-status" class="d-flex align-items-center fs-sm ms-auto {{ $inStockSSR ? 'text-success' : 'text-danger' }}">
      <i class="fs-base me-2 {{ $inStockSSR ? 'ci-check-circle' : 'ci-close-circle' }}"></i>
      <span class="stock-text">
        {{ $inStockSSR ? __('product.available') : __('product.out_of_stock') }}
      </span>
    </div>
  </div>

  <!-- Варіанти -->
  @include('home.product-page.product-variants', ['product' => $product])

  @push('styles')
  <style>
    .new-price { color:#f45b5b; font-size:1.75rem; font-weight:bold; }
    .old-price { color:#333; font-size:1.25rem; position:relative; }
    .old-price::after { content:''; position:absolute; top:50%; left:0; width:100%; height:1px; background-color:#f45b5b; transform:translateY(-50%); }
  </style>
  @endpush

  @php
    $currentLocale = app()->getLocale();

    // Переклад продукту (для name+slug)
    $tr = $product->translations->firstWhere('locale', $currentLocale)
          ?? $product->translations->first();
    $productSlug = $tr?->slug ?? (string)$product->id;

    // Основна категорія (для categorySlug)
    $category = $product->categories->first();
    $catTr = $category?->translations->firstWhere('locale', $currentLocale)
            ?? $category?->translations->first();
    $categorySlug = $catTr?->slug ?? (string)($category?->id ?? '');

    // Готовий URL: /{locale}/{categorySlug}/{productSlug} (без /product/)
    $productUrl = $categorySlug
      ? url($currentLocale . '/' . $categorySlug . '/' . $productSlug)
      : url($currentLocale . '/product/' . $productSlug); // fallback

    $payload = [
      'id'    => $product->id,
      'slug'  => $productSlug,
      'url'   => $productUrl,
      'price' => $product->price,
      'name'  => $tr?->name ?? '—',
      'images' => $product->images->map(fn($img) => [
        'full_url' => asset('storage/' . ltrim($img->url, '/')),
      ])->values(),
      'translations' => $product->translations->map(fn($t) => [
        'locale' => $t->locale,
        'name'   => $t->name,
      ])->values(),
      'variants' => $product->variants->map(fn($v) => [
        'id'             => $v->id,
        'size'           => $v->size,
        'color'          => $v->color,
        'price_override' => $v->price_override,
        'old_price'      => $v->old_price ?? null,
        'variant_sku'    => $v->variant_sku,
        'quantity'       => (int)($v->quantity ?? 0),
      '])->values(),
      'stock_total' => (int) $stockTotal, // ← додано
    ];
  @endphp

  @push('scripts')
  <script>
    // Дані
    window.productVariants = @json($payload['variants']);
    window.basePrice = {{ $payload['price'] }};
    window.productStockTotal = {{ (int) $payload['stock_total'] }};

    document.addEventListener('DOMContentLoaded', function () {
      const variants   = Array.isArray(window.productVariants) ? window.productVariants : [];
      const basePrice  = parseFloat(window.basePrice);
      const priceEl    = document.getElementById('product-price');
      const oldPriceEl = document.getElementById('product-old-price');
      const sizeSelect  = document.querySelector('select[name="size"]');
      const colorSelect = document.querySelector('select[name="color"]'); // якщо є
      const stockEl    = document.getElementById('stock-status');
      const stockText  = stockEl ? stockEl.querySelector('.stock-text') : null;
      const labels = {
        available: @json(__('product.available')),
        out_of_stock: @json(__('product.out_of_stock')),
      };

      if (!priceEl || !oldPriceEl) return;

      const formatPrice = (price) => {
        const num = Number.parseFloat(price);
        if (!Number.isFinite(num)) return '';
        return (num % 1 === 0 ? num.toFixed(0) : num.toFixed(2)) + ' грн';
      };

      const findMatchingVariant = () => {
        const s = sizeSelect ? sizeSelect.value : null;
        const c = colorSelect ? colorSelect.value : null;
        if (!variants.length) return null;
        // Спочатку пробуємо повний збіг size+color (якщо обрані)
        let match = variants.find(v =>
          (s ? v.size === s : true) &&
          (c ? (v.color ?? null) === c : true)
        );
        // Якщо не знайшли, повертаємо null (тоді братимемо суму/базу)
        return match || null;
      };

      const updatePrice = () => {
        const match = findMatchingVariant();
        const newPrice = (match && match.price_override != null)
          ? match.price_override
          : basePrice;
        priceEl.textContent = formatPrice(newPrice);

        const oldPrice = Number.parseFloat(match?.old_price ?? 0);
        if (oldPrice > 0) {
          oldPriceEl.textContent = formatPrice(oldPrice);
          oldPriceEl.classList.remove('d-none');
        } else {
          oldPriceEl.textContent = '';
          oldPriceEl.classList.add('d-none');
        }
      };

      const updateStock = () => {
        if (!stockEl || !stockText) return;

        const match = findMatchingVariant();
        let qty;
        if (match && typeof match.quantity === 'number') {
          qty = match.quantity;
        } else if (variants.length) {
          // Сума по всіх варіантах (якщо розмір/колір не обрані)
          qty = variants.reduce((acc, v) => acc + (parseInt(v.quantity) || 0), 0);
        } else {
          // Без варіантів — беремо загальний склад
          qty = parseInt(window.productStockTotal) || 0;
        }

        const inStock = (qty || 0) > 0;

        stockEl.classList.toggle('text-success', inStock);
        stockEl.classList.toggle('text-danger', !inStock);

        const icon = stockEl.querySelector('i');
        if (icon) {
          icon.classList.toggle('ci-check-circle', inStock);
          icon.classList.toggle('ci-close-circle', !inStock);
        }
        stockText.textContent = inStock ? labels.available : labels.out_of_stock;

        // (Необов'язково) зробити кнопку "У кошик" неактивною, якщо немає
        const cartBtn = document.querySelector('#add-to-cart button');
        if (cartBtn) cartBtn.disabled = !inStock;
      };

      // Події
      if (sizeSelect)  sizeSelect.addEventListener('change', () => { updatePrice(); updateStock(); });
      if (colorSelect) colorSelect.addEventListener('change', () => { updatePrice(); updateStock(); });

      // Стартова ініціалізація
      updatePrice();
      updateStock();
    });
  </script>
  @endpush

  {{-- 🔎 попереджувальний банер, якщо не знайшли categorySlug --}}
  @if (empty($categorySlug))
    <div class="alert alert-warning my-2">
      ⚠️ Для цього товару не знайдено <code>categorySlug</code>. Використано резервний шлях <code>/{{ $currentLocale }}/product/{{ $productSlug }}</code>.
    </div>
  @endif

  <!-- Кнопка "У кошик" -->
  <div class="mt-3">
    <div id="add-to-cart" data-product='@json($payload)'></div>
  </div>

  <!-- Варіанти доставки -->
  @include('home.product-page.product-delivery', ['product' => $product])
</div>
