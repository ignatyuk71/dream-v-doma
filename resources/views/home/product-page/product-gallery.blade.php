@php
    use Illuminate\Support\Str;

    // Хелпер: нормалізує шлях до публічного URL
    $toPublicUrl = function ($path) {
        if (empty($path)) {
            return asset('assets/img/placeholder.svg');
        }
        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path; // вже абсолютний URL
        }
        $p = ltrim($path, '/');

        // якщо вже /storage/...
        if (Str::startsWith($p, 'storage/')) {
            return asset($p);
        }

        // прибираємо префікси "public/" або "app/public/"
        $p = preg_replace('#^(?:app/)?public/#', '', $p);

        // повертаємо як /storage/{p}
        return asset('storage/'.$p);
    };

    $locale = app()->getLocale();
    $desc   = $product->translations->firstWhere('locale', $locale)?->description ?? '';
    $blocks = $desc ? json_decode($desc, true) : [];

    // ГАЛЕРЕЯ
    $images = $product->images; // за потреби: ->sortBy('position')
    $imagesCount = $images->count();

    // loop лише коли є достатньо слайдів
    $hasLoopMain   = $imagesCount > 1;   // для головного прев’ю
    $maxThumbsSpv  = 6;                  // найбільше slidesPerView у breakpoints
    $hasLoopThumbs = $imagesCount > $maxThumbsSpv;

    // Знижка для бейджа
    $price         = (float) ($product->price ?? 0);
    $oldPrice      = (float) ($product->old_price ?? 0);
    $hasDiscount   = $oldPrice > $price && $price > 0;
    $discountPct   = $hasDiscount ? max(0, min(99, round((1 - $price / $oldPrice) * 100))) : 0;
    $badgeDiscount = $hasDiscount ? $discountPct : 30;

    $mainSwiperOptions = json_encode([
        'loop' => $hasLoopMain,
        'navigation' => [
            'prevEl' => '.btn-prev',
            'nextEl' => '.btn-next',
        ],
        'thumbs' => [
            'swiper' => '#thumbs',
        ],
    ], JSON_UNESCAPED_SLASHES);

    $thumbsSwiperOptions = json_encode([
        'loop' => $hasLoopThumbs,
        'spaceBetween' => 12,
        'slidesPerView' => 2,
        'watchSlidesProgress' => true,
        'breakpoints' => [
            340 => ['slidesPerView' => 4],
            500 => ['slidesPerView' => 5],
            600 => ['slidesPerView' => 6],
            768 => ['slidesPerView' => 4],
            992 => ['slidesPerView' => 5],
            1200 => ['slidesPerView' => 6],
        ],
    ], JSON_UNESCAPED_SLASHES);
@endphp

<!-- Preview -->
<div class="swiper" data-swiper='{{ $mainSwiperOptions }}'>
  <div class="swiper-wrapper">
    @forelse($images as $image)
      <div class="swiper-slide">
        <div class="image-wrapper">
          <span class="bf-badge" aria-label="Black Friday -{{ $badgeDiscount }}%">
            Black Friday<br><strong>-{{ $badgeDiscount }}%</strong>
          </span>
          <img
            src="{{ $toPublicUrl($image->url ?? null) }}"
            class="swiper-thumb-img"
            alt="{{ $product->meta_title }}"
            @if($loop->first) fetchpriority="high" @else loading="lazy" decoding="async" @endif
          />
        </div>
      </div>
    @empty
      <div class="swiper-slide">
        <div class="image-wrapper">
          <img
            src="{{ $toPublicUrl(null) }}"
            class="swiper-thumb-img"
            alt="{{ $product->meta_title }}"
          />
        </div>
      </div>
    @endforelse
  </div>

  <!-- Prev button -->
  <div class="position-absolute top-50 start-0 z-2 translate-middle-y ms-sm-2 ms-lg-3">
    <button
      type="button"
      class="btn btn-prev btn-icon btn-outline-secondary bg-body rounded-circle animate-slide-start"
      aria-label="Prev"
    >
      <i class="ci-chevron-left fs-lg animate-target"></i>
    </button>
  </div>

  <!-- Next button -->
  <div class="position-absolute top-50 end-0 z-2 translate-middle-y me-sm-2 me-lg-3">
    <button
      type="button"
      class="btn btn-next btn-icon btn-outline-secondary bg-body rounded-circle animate-slide-end"
      aria-label="Next"
    >
      <i class="ci-chevron-right fs-lg animate-target"></i>
    </button>
  </div>
</div>

<!-- Thumbnails -->
<div
  class="swiper swiper-load swiper-thumbs pt-2 mt-1"
  id="thumbs"
  data-swiper='{{ $thumbsSwiperOptions }}'
>
  <div class="swiper-wrapper">
    @forelse($images as $image)
      <div class="swiper-slide swiper-thumb">
        <div class="thumb-wrapper">
          <img
            src="{{ $toPublicUrl($image->url ?? null) }}"
            class="swiper-thumb-img"
            alt="{{ $product->meta_title }}"
            loading="lazy"
            decoding="async"
          />
        </div>
      </div>
    @empty
      <div class="swiper-slide swiper-thumb">
        <div class="thumb-wrapper">
          <img
            src="{{ $toPublicUrl(null) }}"
            class="swiper-thumb-img"
            alt="{{ $product->meta_title }}"
          />
        </div>
      </div>
    @endforelse
  </div>
</div>

@push('styles')
<style>
  .image-wrapper {
      width: 100%;
      height: 650px;
      position: relative;
      background: #fff;
  }
  @media (max-width: 768px) {
      .image-wrapper {
          height: auto;
          padding-top: 100%; /* квадратна пропорція */
      }
  }
  .image-wrapper img {
      position: absolute;
      top: 0; left: 0;
      width: 100%;
      height: 100%;
      object-fit: contain;
      object-position: center;
  }

  /* Black Friday badge */
  .bf-badge{
    position:absolute;
    top:8px;
    right:8px;
    width:78px;
    background:#000;
    color:#fff;
    font-size:15px;
    font-weight:800;
    line-height:1.2;
    text-align:center;
    text-transform:uppercase;
    letter-spacing:0.02em;
    z-index:30;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    padding:30px 7px 9px;
    clip-path: polygon(50% 0%, 100% 18%, 100% 100%, 0% 100%, 0% 18%);
    border-radius:7px;
    opacity:0.9;
  }
  .bf-badge::before{
    content:'';
    position:absolute;
    top:10px;
    left:50%;
    transform:translateX(-50%);
    width:12px;
    height:12px;
    border-radius:50%;
    background:#fff;
    box-shadow:0 0 0 2px #000;
  }
  .bf-badge strong{
    display:block;
    font-size:25px;
    margin-top:4px;
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

  .thumb-wrapper {
      aspect-ratio: 1 / 1;
      max-width: 94px;
      border: 1px solid #e9ecef;
      border-radius: .5rem;
      overflow: hidden;
      background: #fff;
  }
  .swiper-slide-thumb-active .thumb-wrapper {
      border-color: #ff6b6b;
      box-shadow: 0 0 0 .12rem rgba(255,105,97,.18);
  }
  .thumb-wrapper img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
      display: block;
  }
</style>
@endpush
