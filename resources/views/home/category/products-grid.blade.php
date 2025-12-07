@php
  $locale = app()->getLocale();
  $catTranslation = $translation; // передаємо з контролера
  $categoryName   = $catTranslation?->name ?? '';
@endphp

<section class="container pt-1 mt-2 mt-sm-3 mt-lg-4 mt-xl-2">
  <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 gy-4 gy-md-5">
    @foreach ($products as $product)
      @php
        // Переклад
        $translation   = $product->translations->first();
        $productSlug   = $translation?->slug ?? $product->id;
        $name          = trim($translation?->name ?? '—');

        // Головне фото
        $imageUrl = optional(
            $product->images->firstWhere('is_main', true)
            ?? $product->images->first()
        )->full_url ?? asset('assets/img/placeholder.svg');

        // Ціни / знижка
        $price         = (float) ($product->price ?? 0);
        $oldPrice      = (float) ($product->old_price ?? 0);
        $hasDiscount   = $oldPrice > $price && $price > 0;
        $discountPct   = $hasDiscount ? max(0, min(99, round((1 - $price / $oldPrice) * 100))) : 0;
        $badgeDiscount = $hasDiscount ? $discountPct : 30;

        // Рейтинг
        $ratingRaw = $product->avg_rating;
        if (is_null($ratingRaw)) {
            $ratingRaw = $product->relationLoaded('approvedReviews')
                ? (float) ($product->approvedReviews->avg('rating') ?? 0)
                : (float) ($product->approvedReviews()->avg('rating') ?? 0);
        }
        $ratingRaw     = (float) $ratingRaw;
        $rating        = max(0, min(5, (int) round($ratingRaw)));
        $reviewsCount  = (int) ($product->reviews_count
                            ?? ($product->relationLoaded('approvedReviews')
                                  ? $product->approvedReviews->count()
                                  : ($product->approvedReviews()->count() ?? 0)));

        // URL
        // $slug — slug поточної категорії, приходить з контролера
        $productUrl    = url($locale . '/' . $slug . '/' . $productSlug);

        // Валюта + наявність
        $currencyLabel = __('currency');
        $currencyIso   = 'UAH';
        $availability  = ($product->quantity_in_stock ?? 0) > 0
            ? 'https://schema.org/InStock'
            : 'https://schema.org/OutOfStock';
      @endphp

      <div class="col">
        <article class="product-card animate-underline hover-effect-opacity bg-body rounded position-relative"
                 itemscope itemtype="https://schema.org/Product">
          <meta itemprop="name" content="{{ $name }}">
          <meta itemprop="category" content="{{ $categoryName }}">

          <div class="position-relative">
            <a class="d-block rounded-top overflow-hidden" href="{{ $productUrl }}" itemprop="url">
              @if($hasDiscount)
                <span
                  class="xmas-tag"
                  aria-label="{{ $badgeDiscount ? ('Різдвяна знижка '.$badgeDiscount.'%') : 'Різдвяна знижка' }}"
                  data-discount="-{{ $badgeDiscount }}%"
                  data-label="Різдвяна знижка"
                ></span>
              @endif
              @if($hasDiscount)
                <span class="badge bg-danger position-absolute top-0 start-0 mt-2 ms-2 mt-lg-3 ms-lg-3">
                  -{{ $discountPct }}%
                </span>
              @endif
              <div class="ratio" style="--cz-aspect-ratio: calc(240 / 258 * 100%)">
                <img src="{{ $imageUrl }}"
                     alt="{{ $name }}"
                     loading="lazy"
                     decoding="async"
                     itemprop="image"
                     style="width: 100%; height: 100%; object-fit: cover;">
              </div>
            </a>
          </div>

          <div class="w-100 min-w-0 px-1 pb-2 px-sm-3 pb-sm-3">
            {{-- Рейтинг --}}
            <div class="d-flex align-items-center gap-2 mb-2 py-2"
                 aria-label="{{ number_format($ratingRaw,1,',',' ') }}/5">
              <div class="d-flex gap-1 fs-xs">
                @for($i=1; $i<=5; $i++)
                  @if($i <= $rating)
                    <i class="ci-star-filled text-warning"></i>
                  @else
                    <i class="ci-star text-body-tertiary opacity-75"></i>
                  @endif
                @endfor
              </div>
              <span class="text-body-tertiary fs-xs">({{ $reviewsCount }})</span>
            </div>

            @if($reviewsCount > 0)
              <div itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
                <meta itemprop="ratingValue" content="{{ number_format($ratingRaw, 1, '.', '') }}">
                <meta itemprop="reviewCount"  content="{{ $reviewsCount }}">
                <meta itemprop="bestRating"   content="5">
                <meta itemprop="worstRating"  content="1">
              </div>
            @endif

            {{-- Назва --}}
            <h3 class="pb-1 mb-2">
              <a class="d-block fs-sm fw-medium text-truncate" href="{{ $productUrl }}">
                <span class="animate-target" itemprop="name">{{ $name }}</span>
              </a>
            </h3>

            {{-- Ціна + кнопка --}}
            <div class="d-flex align-items-center justify-content-between">
              <div class="h5 lh-1 mb-0" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                {{ rtrim(rtrim(number_format($price, 2, '.', ' '), '0'), '.') }} {{ $currencyLabel }}
                @if($hasDiscount)
                  <del class="text-body-tertiary fs-sm fw-normal">
                    {{ rtrim(rtrim(number_format($oldPrice, 2, '.', ' '), '0'), '.') }} {{ $currencyLabel }}
                  </del>
                @endif
                <meta itemprop="price" content="{{ number_format($price, 2, '.', '') }}">
                <meta itemprop="priceCurrency" content="{{ $currencyIso }}">
                <meta itemprop="availability" content="{{ $availability }}">
              </div>

              <a href="{{ $productUrl }}"
                 class="product-card-button btn btn-icon btn-secondary animate-slide-end ms-2"
                 aria-label="{{ __('product.view') }}: {{ $name }}">
                <i class="ci-shopping-cart fs-base animate-target"></i>
              </a>
            </div>
          </div>

          {{-- ▶️ КОЛЬОРИ (квадратні свотчі на hover) --}}
          @php
            /** Очікуємо, що colors підвантажені контролером:
             *  products.colors (+ linkedProduct.translations & categories.translations)
             */
            $colors = $product->colors ?? collect();
            $maxColors = 5;
            $visibleColors = $colors->take($maxColors);
            $remaining = max(0, $colors->count() - $maxColors);
          @endphp

          @if($visibleColors->isNotEmpty())
            <div class="product-card-details position-absolute top-100 start-0 w-100 bg-body rounded-bottom shadow mt-n2 p-3 pt-1">
              <span class="position-absolute top-0 start-0 w-100 bg-body mt-n2 py-2"></span>

              <div class="d-flex align-items-center flex-wrap gap-3">
                @foreach($visibleColors as $color)
                  @php
                    // абсолютний href для свотча
                    $href = method_exists($color, 'buildHref')
                      ? $color->buildHref($locale, $slug, $productSlug)
                      : $productUrl;

                    $iconUrl   = $color->icon_url ?? null;
                    $colorName = trim($color->name ?? '');
                  @endphp

                  <a href="{{ $href }}"
                     class="color-thumb @if(!empty($color->is_default)) is-active @endif"
                     aria-label="{{ __('product.color') }}: {{ $colorName }}"
                     title="{{ $colorName }}"
                     @if($iconUrl) style="background-image:url('{{ $iconUrl }}');" @endif>
                    @unless($iconUrl)
                      {{ mb_substr($colorName, 0, 1) }}
                    @endunless
                  </a>
                @endforeach

                @if($remaining > 0)
                  <a href="{{ $productUrl }}" class="color-thumb color-thumb-more" title="+{{ $remaining }}">
                    +{{ $remaining }}
                  </a>
                @endif
              </div>
            </div>
          @endif
        </article>
      </div>
    @endforeach
  </div>
</section>

{{-- Локальний CSS для квадратних свотчів у категоріях --}}
<style>
  .product-card-details .color-thumb{
    width:54px; height:54px;                /* розмір */
    border-radius:10px;                      /* постав 0 — будуть повністю квадратні */
    border:1.5px solid rgba(0,0,0,.25);
    display:flex; align-items:center; justify-content:center;
    background-size:cover; background-position:center; background-repeat:no-repeat;
    text-decoration:none; font-size:12px; font-weight:600; color:#333; text-transform:uppercase;
    transition:box-shadow .15s ease, border-color .15s ease, transform .15s ease;
  }
  .product-card-details .color-thumb:hover,
  .product-card-details .color-thumb:focus{
    border-color:rgba(0,0,0,.55);
    box-shadow:0 0 0 4px rgba(0,0,0,.06);
    transform:translateY(-1px);
    outline:none;
  }
  .product-card-details .color-thumb.is-active{
    border-color:#0b1320;
    box-shadow:0 0 0 2px rgba(11,19,32,.08);
  }
  .product-card-details .color-thumb-more{
    border-style:dashed;
  }

  /* Black Friday badge */
  /* Christmas discount tag */
  .xmas-tag{
    position:absolute;
    top:0;
    right:0;
    width:80px;
    aspect-ratio: 300 / 450;
    background-image:url('/assets/img/christmas-tag.webp');
    background-size:contain;
    background-repeat:no-repeat;
    background-position:center;
    display:block;
    z-index:30;
  }
  .xmas-tag::after{
    content: attr(data-discount);
    position:absolute;
    left:52%;
    top:52%;
    transform:translate(-50%, -50%);
    font-size:17px;
    font-weight:700;
    color:#fff;
    line-height:1;
  }
  .xmas-tag::before{
    content: attr(data-label);
    position:absolute;
    left:50%;
    bottom:19%;
    transform:translateX(-50%);
    font-size:9px;
    line-height:1.1;
    text-align:center;
    font-weight:700;
    color:#fff;
    text-transform:uppercase;
    width:80%;
  }
  @media (max-width: 576px){
    .xmas-tag{
      width:52px;
      top:0;
      right:0;
    }
    .xmas-tag::after{
      font-size:10px;
      top:53%;
      left:52%;
    }
    .xmas-tag::before{
      font-size:5px;
      bottom:18%;
    }
  }
  @media (max-width: 576px){
    .bf-badge{
      width:40px;
      padding:21px 0 3px;
      top:3px;
      right:3px;
      font-size: 8px;
      border-radius:5px;
      opacity:0.7;
    }
    .bf-badge strong{
      font-size:10px;
      margin-top:1px;
    }
  }

</style>
