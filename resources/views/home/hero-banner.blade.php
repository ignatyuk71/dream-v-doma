<section class="bg-body-tertiary">
  <div class="container">
    <div class="row">

      <!-- Titles master slider -->
      <div class="col-md-6 col-lg-5 d-flex flex-column">
        <div class="py-4 mt-auto">
          <div class="swiper pb-1 pt-3 pt-sm-4 py-md-4 py-lg-3" data-swiper='{
            "spaceBetween": 24,
            "loop": true,
            "speed": 400,
            "controlSlider": "#heroImages",
            "pagination": {
              "el": "#sliderBullets",
              "clickable": true
            },
            "autoplay": {
              "delay": 5500,
              "disableOnInteraction": false
            }
          }'>
            <div class="swiper-wrapper align-items-center">
              @foreach($banners as $banner)
                <div class="swiper-slide text-center text-md-start">
                  @if(!empty($banner->subtitle))
                    <p class="fs-xl mb-2 mb-lg-3 mb-xl-4">{{ $banner->subtitle }}</p>
                  @endif

                  @if(!empty($banner->title))
                    <h2 class="display-4 text-uppercase mb-4 mb-xl-5">
                      {!! nl2br(e($banner->title)) !!}
                    </h2>
                  @endif

                  @if(!empty($banner->button_link) && !empty($banner->button_text))
                    <a class="btn btn-lg btn-outline-dark" href="{{ $banner->button_link }}">
                      {{ $banner->button_text }}
                      <i class="ci-arrow-up-right fs-lg ms-2 me-n1"></i>
                    </a>
                  @endif
                </div>
              @endforeach
            </div>
          </div>
        </div>

        <!-- Slider bullets (pagination) -->
        <div class="d-flex justify-content-center justify-content-md-start pb-4 pb-xl-5 mt-n1 mt-md-auto mb-md-3 mb-lg-4">
          <div class="swiper-pagination position-static w-auto pb-1" id="sliderBullets"></div>
        </div>
      </div>

      <!-- Linked images (controlled slider) -->
      <div class="col-md-6 col-lg-7 align-self-end">
        <div class="position-relative ms-md-n4">
          <div class="ratio" style="--cz-aspect-ratio: calc(420 / 770 * 100%)"></div>
          <div class="swiper position-absolute top-0 start-0 w-100 h-100 user-select-none" id="heroImages" data-swiper='{
            "allowTouchMove": false,
            "loop": true,
            "effect": "fade",
            "fadeEffect": { "crossFade": true }
          }'>
            <div class="swiper-wrapper">
              @foreach($banners as $banner)
                @php
                  $raw = $banner->image ?? null;

                  if ($raw) {
                      if (\Illuminate\Support\Str::startsWith($raw, ['http://','https://'])) {
                          $bannerImg = $raw;
                      } else {
                          $path = ltrim($raw, '/');
                          if (\Illuminate\Support\Str::startsWith($path, 'storage/')) {
                              $path = \Illuminate\Support\Str::after($path, 'storage/'); // прибрати дубльоване "storage/"
                          }
                          $bannerImg = asset('storage/'.$path);
                      }
                  } else {
                      $bannerImg = asset('assets/img/placeholder.svg');
                  }
                @endphp

                <div class="swiper-slide">
                  <img
                    src="{{ $bannerImg }}"
                    class="rtl-flip"
                    alt="{{ $banner->title ?? 'Banner' }}"
                    loading="lazy"
                    decoding="async"
                    style="width:100%;height:100%;object-fit:cover;">
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>
