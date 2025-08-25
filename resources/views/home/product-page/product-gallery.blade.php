@php
    $images = $product->images;
    $hasMultiple = $images->count() >= 3;

    $mainSwiperOptions = json_encode([
        'loop' => $hasMultiple,
        'navigation' => [
            'prevEl' => '.btn-prev',
            'nextEl' => '.btn-next',
        ],
        'thumbs' => [
            'swiper' => '#thumbs',
        ],
    ]);

    $thumbsSwiperOptions = json_encode([
        'loop' => $hasMultiple,
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
    ]);
@endphp

<!-- Preview -->
<div class="swiper" data-swiper='{{ $mainSwiperOptions }}'>
  <div class="swiper-wrapper">
    @foreach($images as $image)
      <div class="swiper-slide">
        <div class="image-wrapper">
        <img
              src="{{ $image->url ? asset(ltrim($image->url, '/')) : asset('assets/img/placeholder.svg') }}"
              class="swiper-thumb-img"
              alt="{{ $product->meta_title }}"
          />
        </div>
      </div>
    @endforeach
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
    @foreach($images as $image)
      <div class="swiper-slide swiper-thumb">
        <div class="thumb-wrapper">
        <img
            src="{{ $image->url ? asset(ltrim($image->url, '/')) : asset('assets/img/placeholder.svg') }}"
            class="swiper-thumb-img"
            alt="{{ $product->meta_title }}"
        />
        </div>
      </div>
    @endforeach
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
        .thumb-wrapper {
            aspect-ratio: 1 / 1;
            max-width: 94px;
            overflow: hidden;
        }
        .thumb-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }
    </style>
@endpush
