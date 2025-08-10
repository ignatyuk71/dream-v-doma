@php
    use App\Models\Product;

    $products = Product::with(['images', 'translations'])
        ->where('status', true)
        ->where('is_popular', true)
        ->orderByDesc('created_at')
        ->take(10)
        ->get();

    $locale = app()->getLocale();

    // Breakpoints для slidesPerView (мають відповідати data-swiper)
    $breakpointsSlides = [
        0 => 2,      // мобілка
        768 => 3,    // планшет
        992 => 4     // десктоп
    ];
    $needLoop = false;
    foreach ($breakpointsSlides as $val) {
        if ($products->count() > $val) {
            $needLoop = true;
            break;
        }
    }
@endphp

@if($products->count())
<!-- Trending products (Carousel) -->
<section class="container pt-5 mt-2 mt-sm-3 mt-lg-4 mt-xl-5">
  <h2 class="h3 border-bottom pb-4 mb-0">{{ __('recommended_products') }}</h2>
  <div class="position-relative mx-md-1">

    <!-- External slider prev/next buttons visible on screens > 500px wide (sm breakpoint) -->
    <button type="button" class="trending-prev btn btn-prev btn-icon btn-outline-secondary bg-body rounded-circle animate-slide-start position-absolute top-50 start-0 z-2 translate-middle-y ms-n1 d-none d-sm-inline-flex" aria-label="Prev">
      <i class="ci-chevron-left fs-lg animate-target"></i>
    </button>
    <button type="button" class="trending-next btn btn-next btn-icon btn-outline-secondary bg-body rounded-circle animate-slide-end position-absolute top-50 end-0 z-2 translate-middle-y me-n1 d-none d-sm-inline-flex" aria-label="Next">
      <i class="ci-chevron-right fs-lg animate-target"></i>
    </button>

    <!-- Slider -->
    <div class="swiper py-4 px-sm-3" data-swiper='{
      "slidesPerView": 2,
      "spaceBetween": 24,
      "loop": {{ $needLoop ? 'true' : 'false' }},
      "navigation": {
        "prevEl": ".trending-prev",
        "nextEl": ".trending-next"
      },
      "breakpoints": {
        "768": {
          "slidesPerView": 3
        },
        "992": {
          "slidesPerView": 4
        }
      }
    }'>
      <div class="swiper-wrapper">

        @foreach($products as $product)
          @php
              $tr = $product->translations->firstWhere('locale', $locale);
              $name = $tr?->name ?? $product->sku;
              $slug = $tr?->slug ?? $product->id;
              $price = $product->price;
              $oldPrice = $product->old_price ?? null;
              $mainImage = $product->images->firstWhere('is_main', true);
              $image = $mainImage?->full_url ?? asset('/assets/img/placeholder.svg');

              // Знижка (бейдж)
              $discount = ($oldPrice && $oldPrice > $price)
                  ? '-' . round(100 - $price / $oldPrice * 100) . '%'
                  : null;

              // Рейтинг (можна підключити свою змінну)
              $rating = 5; // Або $product->avg_rating ?? 5
              $reviews = $product->reviews_count ?? 123;
          @endphp

          <div class="swiper-slide">
            <div class="product-card animate-underline hover-effect-opacity bg-body rounded">
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
                    <li>
                      <a class="dropdown-item" href="#!">
                        <i class="ci-heart fs-sm ms-n1 me-2"></i>
                        Add to Wishlist
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="#!">
                        <i class="ci-refresh-cw fs-sm ms-n1 me-2"></i>
                        Compare
                      </a>
                    </li>
                  </ul>
                </div>
                <a class="d-block rounded-top overflow-hidden p-3 p-sm-4" href="{{ url(app()->getLocale() . "/product/{$slug}") }}">
                  @if($discount)
                    <span class="badge bg-danger position-absolute top-0 start-0 mt-2 ms-2 mt-lg-3 ms-lg-3">{{ $discount }}</span>
                  @endif
                  <div class="ratio" style="--cz-aspect-ratio: calc(240 / 258 * 100%)">
                    <img src="{{ $image }}" alt="{{ $name }}">
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
                  <a class="d-block fs-sm fw-medium text-truncate" href="{{ url(app()->getLocale() . "/product/{$slug}") }}">
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
                  <button type="button" class="product-card-button btn btn-icon btn-secondary animate-slide-end ms-2" aria-label="Add to Cart">
                    <i class="ci-shopping-cart fs-base animate-target"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        @endforeach

      </div>
    </div>

    <!-- Кнопки на мобілці -->
    <div class="d-flex justify-content-center gap-2 mt-n2 mb-3 pb-1 d-sm-none">
      <button type="button" class="trending-prev btn btn-prev btn-icon btn-outline-secondary bg-body rounded-circle animate-slide-start me-1" aria-label="Prev">
        <i class="ci-chevron-left fs-lg animate-target"></i>
      </button>
      <button type="button" class="trending-next btn btn-next btn-icon btn-outline-secondary bg-body rounded-circle animate-slide-end" aria-label="Next">
        <i class="ci-chevron-right fs-lg animate-target"></i>
      </button>
    </div>

  </div>
</section>
@endif
