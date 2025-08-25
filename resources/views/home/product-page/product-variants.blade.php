@php
  // Витягуємо унікальні розміри з варіантів
  $sizes = $product->variants
      ->pluck('size')
      ->unique()
      ->filter()
      ->values();

  $locale = app()->getLocale();
@endphp

<div>
  <!-- Вибір кольору -->
  @if ($product->colors->isNotEmpty())
    <div class="mb-1">
      <label class="form-label fw-semibold pb-1 mb-1">
        {{ __('product.color') }}:
        <span class="text-body fw-normal" id="selected-color-label">
          {{ $product->colors->firstWhere('is_default', true)?->name ?? $product->colors->first()?->name }}
        </span>
      </label>

      <div class="d-flex flex-wrap gap-2">
        @foreach ($product->colors as $index => $color)
          @php
            // Беремо саме linkedProduct — куди веде цей колір!
            $linkedProduct = $color->linkedProduct ?? $product;
            $translation = $linkedProduct->translations->where('locale', $locale)->first();
            $productSlug = $translation->slug ?? $linkedProduct->slug;

            // Витягуємо категорію для цього продукту
            $category = $linkedProduct->categories->first();
            $categoryTranslation = $category?->translations->where('locale', $locale)->first();
            $categorySlug = $categoryTranslation?->slug ?? $category?->slug ?? 'category';

            $colorUrl = route('products.show', [
              'locale' => $locale,
              'category' => $categorySlug,
              'product' => $productSlug
            ]);
          @endphp

          <input
            type="radio"
            class="btn-check"
            name="color"
            id="color-{{ $index }}"
            value="{{ $color->name }}"
            @checked($color->is_default)
          >

          <label for="color-{{ $index }}"
                 class="color-thumb"
                 data-label="{{ $color->name }}"
                 onclick="window.location.href='{{ $colorUrl }}'">
            <img src="{{ asset($color->icon_path) }}"
                 alt="{{ $color->name }}">
            <span class="visually-hidden">{{ $color->name }}</span>
          </label>
        @endforeach
      </div>
    </div>
  @endif

  <!-- Вибір розміру -->
  <div class="mb-2">
    <div class="d-flex align-items-center justify-content-between mb-1">
      <label class="form-label fw-semibold mb-0">{{ __('product.size') }}</label>
      <div class="nav">
        <a class="nav-link animate-underline fw-normal px-0" href="#sizeGuide" data-bs-toggle="modal">
          <i class="ci-ruler fs-lg me-2"></i>
          <span class="animate-target">{{ __('product.size_guide') }}</span>
        </a>
      </div>
    </div>

    <select
      class="form-select form-select-lg"
      name="size"
      aria-label="Select size"
    >
      <option value="">{{ __('product.choose_size') }}</option>
      @foreach ($sizes as $size)
        <option value="{{ $size }}">{{ $size }}</option>
      @endforeach
    </select>
  </div>
</div>
