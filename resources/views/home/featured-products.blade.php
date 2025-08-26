@php
    $locale = app()->getLocale();
@endphp

<section class="container pt-1 mt-2 mt-sm-3 mt-lg-4 mt-xl-2">

  @foreach ($categories as $category)
    @php
        $catTranslation   = $category->translations->first(); // вже відфільтровано по $locale в контролері
        $categoryName     = $catTranslation->name ?? '';
        $categorySlug     = $catTranslation->slug ?? $category->id;
        $categoryUrl      = url($locale . '/' . $categorySlug);
        $categoryProducts = $category->products->take(8);
    @endphp

    {{-- Заголовок категорії + "Переглянути всі" --}}
    <div class="d-flex justify-content-between align-items-center mt-5 pb-2 mb-3 border-bottom">
      <h2 class="h4 mb-0">{{ $categoryName }}</h2>

      <a href="{{ $categoryUrl }}"
         class="text-decoration-none small fw-medium text-secondary d-inline-flex align-items-center gap-1"
         aria-label="{{ __('category.view_all') }}: {{ $categoryName }}">
        {{ __('category.view_all') }}
        <i class="ci-chevron-right fs-sm"></i>
      </a>
    </div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 gy-4 gy-md-5">
      @foreach ($categoryProducts as $product)
        @php
          $translation   = $product->translations->first(); // вже по $locale
          $productSlug   = $translation->slug ?? $product->id;
          $name          = trim($translation->name ?? '—');

          // ---------- ЗОБРАЖЕННЯ (надійно) ----------
          $firstImage = $product->images[0]->url ?? null;
          if ($firstImage) {
              // Абсолютний URL?
              if (\Illuminate\Support\Str::startsWith($firstImage, ['http://', 'https://'])) {
                  $image = $firstImage;
              } else {
                  // Нормалізуємо відносний шлях до /storage/...
                  $path = ltrim($firstImage, '/');
                  if (\Illuminate\Support\Str::startsWith($path, 'storage/')) {
                      $path = \Illuminate\Support\Str::after($path, 'storage/'); // прибрати дубльоване "storage/"
                  }
                  $image = asset('storage/'.$path);
              }
          } else {
              $image = asset('assets/img/placeholder.svg');
          }

          $price         = (float) ($product->price ?? 0);
          $oldPrice      = (float) ($product->old_price ?? 0);
          $hasDiscount   = $oldPrice > $price && $price > 0;
          $discountPct   = $hasDiscount ? max(0, min(99, round((1 - $price / $oldPrice) * 100))) : 0;

          // ---------- РЕЙТИНГ (надійно) ----------
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

          $productUrl    = url($locale . '/' . $categorySlug . '/' . $productSlug);

          // Валюта: лейбл для UI та ISO для Schema.org
          $currencyLabel = __('currency'); // напр. "грн"
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
                  <span class="badge bg-danger position-absolute top-0 start-0 mt-2 ms-2 mt-lg-3 ms-lg-3">
                    -{{ $discountPct }}%
                  </span>
                @endif
                <div class="ratio" style="--cz-aspect-ratio: calc(240 / 258 * 100%)">
                  <img src="{{ $image }}"
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
                      <i class="ci-star text-body-terтіary opacity-75"></i>
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

              {{-- Ціна + кнопка переходу --}}
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

            {{-- Характеристики: до 5 шт. поточною мовою (без міксу RU/UK) --}}
            @php
              $attrs = $product->attributeValues
                  ->filter(function($val) use ($locale) {
                      return (bool) $val->attribute->translations->firstWhere('locale', $locale)
                          && (bool) $val->translations->firstWhere('locale', $locale);
                  })
                  ->sortBy(fn($val) => $val->attribute->position ?? 9999)
                  ->take(4);
            @endphp

            @if($attrs->isNotEmpty())
              <div class="product-card-details position-absolute top-100 start-0 w-100 bg-body rounded-bottom shadow mt-n2 p-3 pt-1">
                <span class="position-absolute top-0 start-0 w-100 bg-body mt-n2 py-2"></span>
                <ul class="list-unstyled d-flex flex-column gap-2 m-0">
                  @foreach($attrs as $val)
                    @php
                      $attrTr   = $val->attribute->translations->firstWhere('locale', $locale);
                      $valueTr  = $val->translations->firstWhere('locale', $locale);
                      $attrName = $attrTr?->name  ?? '';
                      $valText  = $valueTr?->value ?? '';
                    @endphp
                    @if($attrName && $valText)
                      <li class="d-flex align-items-center">
                        <span class="fs-xs">{{ $attrName }}:</span>
                        <span class="d-block flex-grow-1 border-bottom border-dashed px-1 mt-2 mx-2"></span>
                        <span class="text-dark-emphasis fs-xs fw-medium text-end">{{ $valText }}</span>
                      </li>
                    @endif
                  @endforeach
                </ul>
              </div>
            @endif
          </article>
        </div>
      @endforeach
    </div>
  @endforeach

</section>
