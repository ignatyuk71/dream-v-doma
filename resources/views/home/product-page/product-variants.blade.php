@php
  $sizes = $product->variants->pluck('size')->unique()->filter()->values();
  $locale = app()->getLocale();
@endphp

<div>
  {{-- ВИБІР КОЛЬОРУ (зліва текст, праворуч малі прев’ю) --}}
  @if ($product->colors->isNotEmpty())
    <div class="mb-2 color-picker-row">
      <label class="form-label fw-semibold mb-0">
        {{ __('product.color') }}:
        
      </label>

      <div class="color-swatches">
        @foreach ($product->colors as $index => $color)
          @php
            $linkedProduct = $color->linkedProduct ?? $product;
            $translation = $linkedProduct->translations->where('locale', $locale)->first();
            $productSlug = $translation->slug ?? $linkedProduct->slug;

            $category = $linkedProduct->categories->first();
            $categoryTranslation = $category?->translations->where('locale', $locale)->first();
            $categorySlug = $categoryTranslation?->slug ?? $category?->slug ?? 'category';

            $colorUrl = route('products.show', [
              'locale'  => $locale,
              'category'=> $categorySlug,
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
                 title="{{ $color->name }}"
                 data-label="{{ $color->name }}"
                 onclick="window.location.href='{{ $colorUrl }}'">
            <img src="{{ asset($color->icon_path) }}" alt="{{ $color->name }}">
            <span class="visually-hidden">{{ $color->name }}</span>
          </label>
        @endforeach
      </div>
    </div>
  @endif

  {{-- ВИБІР РОЗМІРУ (як було) --}}
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

    <select class="form-select form-select-lg" name="size" aria-label="Select size">
      <option value="">{{ __('product.choose_size') }}</option>
      @foreach ($sizes as $size)
        <option value="{{ $size }}">{{ $size }}</option>
      @endforeach
    </select>
  </div>
</div>

@push('styles')
<style>
  /* рядок "Колір: ..." + прев'ю */
  .color-picker-row{
    display:flex;
    flex-direction:column;
    gap:.5rem;
  }
  @media (min-width:576px){
    .color-picker-row{
      flex-direction:row;
      align-items:center;      /* по центру по висоті */
      gap:1rem;                /* невеликий відступ між лейблом і прев'ю */
    }
  }

  /* сам лейбл "Колір: Х" */
  .color-picker-row > .form-label{
    margin:0;
    white-space:nowrap;        /* щоб не переносився "Колір: ..." */
    flex:0 0 auto;             /* фіксована ширина по контенту */
  }

  /* контейнер з прев'юшками */
  .color-swatches{
    display:flex;
    flex-wrap:wrap;
    gap:.5rem;
    flex:1 1 auto;             /* займає решту рядка */
    justify-content:flex-start;/* притискаємо вліво */
  }

  /* компактні квадрати */
  .color-thumb{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    width:76px;                /* було 64px */
    height:76px;
    background:#fff;
    border:1px solid #e9ecef;
    border-radius:.5rem;
    overflow:hidden;
    cursor:pointer;
    transition:box-shadow .2s, border-color .2s, transform .02s;
  }
  @media (max-width:575.98px){
    .color-thumb{ width:72px; height:72px; } /* ще трішки менші на мобайлі */
  }
  .color-thumb:hover{ border-color:#cfd4da; }
  .btn-check:checked + .color-thumb{
    border-color:#ff6b6b;
    box-shadow:0 0 0 .2rem rgba(255,105,97,.15);
  }
  .color-thumb img{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
  }

  /* густіший шрифт для підписів */
  .form-label.fw-semibold{ font-weight:600 !important; }
</style>
@endpush

