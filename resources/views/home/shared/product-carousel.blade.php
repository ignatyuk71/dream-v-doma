@php
    use App\Models\Product;
    use Illuminate\Support\Str;

    $products = Product::with([
            'images',
            'translations',
            'categories.translations',   // ← додали, щоб взяти slug категорії
        ])
        ->where('status', true)
        ->where('is_popular', true)
        ->orderByDesc('created_at')
        ->take(10)
        ->get();

    $count  = $products->count();
    $locale = app()->getLocale();

    // slidesPerView по брейкпоінтах (має відповідати data-swiper)
    $breakpointsSlides = [
        0   => 2,   // мобілка
        768 => 3,   // планшет
        992 => 4,   // десктоп
    ];
    $maxSlidesPerView = max($breakpointsSlides);

    // loop тільки якщо є що «крутити» на найбільшому брейкпоінті
    $needLoop = $count > $maxSlidesPerView;

    // Унікальні ID для кнопок навігації (окремо для desktop і mobile)
    $uid        = 'tr-' . uniqid();
    $prevIdDesk = 'tr-prev-d-' . $uid;
    $nextIdDesk = 'tr-next-d-' . $uid;
    $prevIdMob  = 'tr-prev-m-' . $uid;
    $nextIdMob  = 'tr-next-m-' . $uid;

    // Опції Swiper — одразу підʼєднаємо обидва набори кнопок
    $swiperOptions = json_encode([
        'slidesPerView' => $breakpointsSlides[0],
        'spaceBetween'  => 24,
        'loop'          => $needLoop,
        'rewind'        => !$needLoop,
        'navigation'    => [
            'prevEl' => "#{$prevIdDesk}, #{$prevIdMob}",
            'nextEl' => "#{$nextIdDesk}, #{$nextIdMob}",
        ],
        'breakpoints'   => [
            768 => ['slidesPerView' => $breakpointsSlides[768]],
            992 => ['slidesPerView' => $breakpointsSlides[992]],
        ],
    ], JSON_UNESCAPED_UNICODE);

    // Хелпер для зображень
    $toPublicUrl = function ($path) {
        if (empty($path)) {
            return asset('assets/img/placeholder.svg');
        }
        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path; // вже абсолютний URL
        }
        $p = ltrim($path, '/');

        // якщо вже веб-шлях /storage/...
        if (Str::startsWith($p, 'storage/')) {
            return asset($p);
        }

        // привести "public/..."/"app/public/..." до storage/...
        $p = preg_replace('#^(?:app/)?public/#', '', $p);

        return asset('storage/'.$p);
    };
@endphp

@if($count)
<section class="container pt-5 mt-2 mt-sm-3 mt-lg-4 mt-xl-5">
  <h2 class="h3 border-bottom pb-4 mb-0">{{ __('recommended_products') }}</h2>

  <div class="position-relative mx-md-1">

    <!-- Prev / Next (desktop & tablet) -->
    <button id="{{ $prevIdDesk }}" type="button"
            class="trending-prev btn btn-prev btn-icon btn-outline-secondary bg-body rounded-circle animate-slide-start position-absolute top-50 start-0 z-2 translate-middle-y ms-n1 d-none d-sm-inline-flex"
            aria-label="Prev" @if($count <= 1) disabled @endif>
      <i class="ci-chevron-left fs-lg animate-target"></i>
    </button>
    <button id="{{ $nextIdDesk }}" type="button"
            class="trending-next btn btn-next btn-icon btn-outline-secondary bg-body rounded-circle animate-slide-end position-absolute top-50 end-0 z-2 translate-middle-y me-n1 d-none d-sm-inline-flex"
            aria-label="Next" @if($count <= 1) disabled @endif>
      <i class="ci-chevron-right fs-lg animate-target"></i>
    </button>

    <!-- Slider -->
    <div class="swiper py-4 px-sm-3" data-swiper='@php echo e($swiperOptions); @endphp'>
      <div class="swiper-wrapper">
        @foreach($products as $product)
          @php
              // Переклад продукту
              $tr        = $product->translations->firstWhere('locale', $locale) ?? $product->translations->first();
              $name      = trim($tr?->name ?? $product->sku ?? '—');
              $slug      = $tr?->slug ?? $product->id;

              // Категорія для URL
              $primaryCategory = $product->categories
                  ->sortByDesc(fn($c) => $c->pivot->is_primary ?? 0)
                  ->first() ?? $product->categories->first();

              $catTr        = $primaryCategory?->translations->firstWhere('locale', $locale)
                              ?? $primaryCategory?->translations->first();
              $categorySlug = $catTr->slug ?? ($primaryCategory->id ?? 'catalog');

              $productUrl = url($locale . '/' . $categorySlug . '/' . $slug);

              // Ціни / знижка
              $price     = $product->price;
              $oldPrice  = $product->old_price ?: null;
              $discount  = ($oldPrice && $oldPrice > $price)
                           ? '-' . round(100 - ($price / $oldPrice * 100)) . '%'
                           : null;

              // Картинка
              $mainImage = $product->images->firstWhere('is_main', true) ?? $product->images->first();
              $imgPath   = $mainImage->url ?? $mainImage->path ?? $mainImage->full_url ?? null;
              $image     = $toPublicUrl($imgPath);

              // Рейтинг
              $rating  = 5; // поки фіксовано
              $reviews = $product->reviews_count ?? 0;
          @endphp

          <div class="swiper-slide">
            <div class="product-card animate-underline hover-effect-opacity bg-body rounded h-100">
              <div class="position-relative">
                <div class="position-absolute top-0 end-0 z-2 hover-effect-target opacity-0 mt-3 me-3">
                  <div class="d-flex flex-column gap-2">
                    <button type="button" class="btn btn-icon btn-secondary animate-pulse d-none d-lg-inline-flex" aria-label="Add to Wishlist">
                      <i class="ci-heart fs-base animate-target"></i>
                    </button>
                    <button type="button" class="btn btn-icon btn-secondary animate-rotate d-none d-lg-inline-flex" aria-label="Compare">
                      <i class="ci-refresh-cw fs-base animate-target"></i>
                    </button>
                  </div>
                </div>

                <div class="dropdown d-lg-none position-absolute top-0 end-0 z-2 mt-2 me-2">
                  <button type="button" class="btn btn-icon btn-sm btn-secondary bg-body" data-bs-toggle="dropdown" aria-expanded="false" aria-label="More actions">
                    <i class="ci-more-vertical fs-lg"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end fs-xs p-2" style="min-width: auto">
                    <li><a class="dropdown-item" href="#!"><i class="ci-heart fs-sm ms-n1 me-2"></i> Add to Wishlist</a></li>
                    <li><a class="dropdown-item" href="#!"><i class="ci-refresh-cw fs-sm ms-n1 me-2"></i> Compare</a></li>
                  </ul>
                </div>

                <a class="d-block rounded-top overflow-hidden p-2 p-sm-2" href="{{ $productUrl }}">
                  @if($discount)
                    <span class="badge bg-danger position-absolute top-0 start-0 mt-2 ms-2 mt-lg-3 ms-lg-3">{{ $discount }}</span>
                  @endif

                  <!-- Зображення -->
                  <div class="ratio" style="--cz-aspect-ratio: calc(240 / 258 * 100%); aspect-ratio: 240/258;">
                    <img src="{{ $image }}"
                         alt="{{ $name }}"
                         loading="lazy"
                         decoding="async"
                         style="width:100%; height:100%; object-fit:cover; object-position:center;">
                  </div>
                </a>
              </div>

              <div class="w-100 min-w-0 px-1 pb-2 px-sm-3 pb-sm-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <div class="d-flex gap-1 fs-xs">
                    @for($i = 0; $i < 5; $i++)
                      <i class="ci-star{{ $i < $rating ? '-filled text-warning' : ' text-body-tertiary opacity-75' }}"></i>
                    @endfor
                  </div>
                  <span class="text-body-tertiary fs-xs">({{ $reviews }})</span>
                </div>

                <h3 class="pb-1 mb-2">
                  <a class="d-block fs-sm fw-medium text-truncate" href="{{ $productUrl }}">
                    <span class="animate-target">{{ $name }}</span>
                  </a>
                </h3>

                <div class="d-flex align-items-center justify-content-between">
                  <div class="h5 lh-1 mb-0">
                    {{ $price }} {{ __('currency') }}
                    @if($oldPrice)
                      <del class="text-body-tertiary fs-sm fw-normal">{{ $oldPrice }} {{ __('currency') }}</del>
                    @endif
                  </div>
                  <a href="{{ $productUrl }}" class="product-card-button btn btn-icon btn-secondary animate-slide-end ms-2" aria-label="View product">
                    <i class="ci-shopping-cart fs-base animate-target"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>

        @endforeach
      </div>
    </div>

    <!-- Кнопки на мобілці -->
    <div class="d-flex justify-content-center gap-2 mt-n2 mb-3 pb-1 d-sm-none">
      <button id="{{ $prevIdMob }}" type="button"
              class="trending-prev btn btn-prev btn-icon btn-outline-secondary bg-body rounded-circle animate-slide-start me-1"
              aria-label="Prev" @if($count <= 1) disabled @endif>
        <i class="ci-chevron-left fs-lg animate-target"></i>
      </button>
      <button id="{{ $nextIdMob }}" type="button"
              class="trending-next btn btn-next btn-icon btn-outline-secondary bg-body rounded-circle animate-slide-end"
              aria-label="Next" @if($count <= 1) disabled @endif>
        <i class="ci-chevron-right fs-lg animate-target"></i>
      </button>
    </div>

  </div>
</section>
@endif
