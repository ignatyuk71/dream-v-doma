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
          <i class="ci-star{{ $i <= floor($rating) ? '-filled text-warning' : ($i - $rating < 1 ? '' : ' text-body-tertiary opacity-75') }}"></i>
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

  <!-- Ціна -->
  <div class="d-flex flex-wrap align-items-center mb-1">
    <div class="h4 d-flex align-items-center my-4 gap-3">
      <span id="product-price" class="new-price">
        {{ (int) $product->price == $product->price ? number_format($product->price, 0, '.', ' ') : number_format($product->price, 2, '.', ' ') }} грн
      </span>
      <del id="product-old-price" class="old-price d-none"></del>
    </div>
    <div class="d-flex align-items-center text-success fs-sm ms-auto">
      <i class="ci-check-circle fs-base me-2"></i>
      {{ __('product.available') }}
    </div>
  </div>

  <!-- Варіанти -->
  @include('home.product-page.product-variants', ['product' => $product])

  @push('styles')
  <style>
    .new-price {
      color: #f45b5b; /* або #e53935 якщо хочеш трохи глибший червоний */
      font-size: 1.75rem;
      font-weight: bold;
    }
    .old-price {
      color: #333;
      font-size: 1.25rem;
      position: relative;
    }
    .old-price::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 0;
      width: 100%;
      height: 1px;
      background-color: #f45b5b; /* така ж червона риска */
      transform: translateY(-50%);
    }
  </style>
@endpush


  @push('scripts')
  <script>
    window.productVariants = @json($product->variants);
    window.basePrice = {{ $product->price }};

    document.addEventListener('DOMContentLoaded', function () {
      const variants = window.productVariants;
      const basePrice = parseFloat(window.basePrice);

      const priceEl = document.getElementById('product-price');
      const oldPriceEl = document.getElementById('product-old-price');
      const sizeSelect = document.querySelector('select[name="size"]');

      if (!priceEl || !oldPriceEl || !sizeSelect) return;

      const formatPrice = (price) => {
        const num = parseFloat(price);
        return (num % 1 === 0 ? num.toFixed(0) : num.toFixed(2)) + ' грн';
      };

      sizeSelect.addEventListener('change', function () {
        const selectedSize = this.value;
        const match = variants.find(v => v.size === selectedSize);

        const newPrice = match?.price_override ?? basePrice;
        priceEl.textContent = formatPrice(newPrice);

        const oldPrice = parseFloat(match?.old_price ?? 0);

        if (oldPrice > 0) {
          oldPriceEl.textContent = formatPrice(oldPrice);
          oldPriceEl.classList.remove('d-none');
        } else {
          oldPriceEl.textContent = '';
          oldPriceEl.classList.add('d-none');
        }
      });
    });
  </script>
  @endpush

  <!-- Кнопка + Варіанти доставки -->
  <div id="add-to-cart" data-product='@json($product)'></div>
  @include('home.product-page.product-delivery', ['product' => $product])
</div>
