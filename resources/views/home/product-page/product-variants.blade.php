@php
  use Illuminate\Support\Str;
  use Illuminate\Support\Facades\Storage;

  $sizes  = $product->variants->pluck('size')->unique()->filter()->values();
  $locale = app()->getLocale();

  // helper для іконок кольорів
  $iconUrl = function ($path) {
      if (!$path) return asset('assets/img/placeholder.svg');
      if (Str::startsWith($path, ['http://','https://','/'])) return $path;
      return Storage::url(ltrim($path,'/')); // => /storage/...
  };
@endphp

<div id="color-picker-{{ $product->id }}">
  {{-- КОЛІР: заголовок зверху --}}
  @if ($product->colors->isNotEmpty())
    <div class="mb-3">
      <label class="form-label fw-semibold mb-2 d-block">
        {{ __('product.color') }}
      </label>

      {{-- Сітка прев’юшок --}}
      <div class="color-swatches-grid">
        @foreach ($product->colors as $index => $color)
          @php
            $linkedProduct   = $color->linkedProduct ?? $product;
            $translation     = $linkedProduct->translations->where('locale',$locale)->first();
            $productSlug     = $translation->slug ?? $linkedProduct->slug;

            $category        = $linkedProduct->categories->first();
            $categoryTrans   = $category?->translations->where('locale',$locale)->first();
            $categorySlug    = $categoryTrans?->slug ?? $category?->slug ?? 'category';

            $colorUrl = route('products.show', [
              'locale'   => $locale,
              'category' => $categorySlug,
              'product'  => $productSlug
            ]);
          @endphp

          <input
            type="radio"
            class="btn-check js-color-radio"
            name="color"
            id="color-{{ $product->id }}-{{ $index }}"
            value="{{ $color->name }}"
            @checked($color->is_default)
          >
          <label for="color-{{ $product->id }}-{{ $index }}"
                 class="color-thumb"
                 title="{{ $color->name }}"
                 data-label="{{ $color->name }}"
                 onclick="window.location.href='{{ $colorUrl }}'">
            <img
              src="{{ $iconUrl($color->icon_path) }}"
              alt="{{ $color->name }}"
              decoding="async" loading="lazy">
          </label>
        @endforeach
      </div>
    </div>
  @endif

  {{-- РОЗМІР (як було) --}}
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
  /* Сітка прев’юшок під заголовком */
  .color-swatches-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(72px,1fr));
    gap:.6rem;
  }
  @media (min-width:576px){
    .color-swatches-grid{ grid-template-columns:repeat(auto-fill,minmax(76px,0.25fr)); }
  }

  /* Прев’юшка кольору */
  .color-thumb{
    position:relative;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    width:100%;
    aspect-ratio:1/1;
    background:#fff;
    border:1px solid #e9ecef;
    border-radius:12px;
    overflow:hidden;
    cursor:pointer;
    transition:transform .14s ease, box-shadow .2s ease, border-color .2s ease;
  }
  .color-thumb:hover{
    transform:translateY(-2px);
    box-shadow:0 6px 18px rgba(20,34,60,.08);
  }
  .color-thumb:active{ transform:translateY(0); }

  .color-thumb img{
    width:100%; height:100%; object-fit:cover; display:block;
    transform:scale(1.01); transition:transform .25s ease;
  }
  .color-thumb:hover img{ transform:scale(1.03); }

  /* Активний (обраний) стан */
  .btn-check:checked + .color-thumb{
    border-color:#ff6b6b;
    box-shadow:0 0 0 .22rem rgba(255,107,107,.18);
  }
  .btn-check:checked + .color-thumb::after{
    content:'';
    position:absolute; inset:auto 8px 8px auto;
    width:18px; height:18px; border-radius:50%;
    background:#ff6b6b;
    box-shadow:0 2px 6px rgba(255,107,107,.35);
    -webkit-mask: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='white' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M20.285 6.709a1 1 0 010 1.414l-9.19 9.19a1 1 0 01-1.414 0l-5.19-5.19a1 1 0 111.414-1.414l4.483 4.483 8.483-8.483a1 1 0 011.414 0z'/%3E%3C/svg%3E") center/12px 12px no-repeat;
    animation:pop .18s ease-out both;
  }
  @keyframes pop{ from{ transform:scale(.6); opacity:.3 } to{ transform:scale(1); opacity:1 } }

  .form-label.fw-semibold{ font-weight:600 !important; }
</style>
@endpush
