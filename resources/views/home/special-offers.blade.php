<section class="container pt-1 mt-2 mt-sm-3 mt-lg-4 mt-xl-5">
  <h3 class="text-center pt-xxl-2 pb-2 pb-md-3">
    {{ __('special_offers.title') }}
  </h3>
  <div class="position-relative px-4 px-md-3 px-lg-4">
    <div class="row position-relative z-2 justify-content-center">

      <!-- ПРАВА КАРТКА -->
      <div class="col-md-4 col-xl-5 order-md-2 d-flex justify-content-center justify-content-md-end py-4 py-md-3 py-lg-4">
        <div class="swiper m-0" data-swiper='{
          "spaceBetween": 24,
          "loop": true,
          "speed": 400,
          "controlSlider": ["#previewImages", "#backgrounds"],
          "pagination": { "el": "#bullets", "clickable": true },
          "navigation": { "prevEl": "#offerPrev", "nextEl": "#offerNext" }
        }' style="max-width: 416px">
          <div class="swiper-wrapper">
            @foreach($specialOffers as $offer)
              @php
                $imgMain = $offer->image_path
                  ? asset('storage/'.$offer->image_path)
                  : asset('assets/img/placeholder.svg');
              @endphp
              <div class="swiper-slide h-auto">
                <div class="card animate-underline h-100 rounded-5 border-0">
                  <div class="pt-3 px-3">
                    @if($offer->discount)
                      <span class="badge text-bg-danger position-absolute top-0 start-0 z-2 mt-2 mt-sm-3 ms-2 ms-sm-3">
                        -{{ $offer->discount }}%
                      </span>
                    @endif
                    <img src="{{ $imgMain }}" alt="{{ $offer->title }}">
                  </div>
                  <div class="card-body text-center py-3">
                    <div class="d-flex justify-content-center min-w-0 fs-sm fw-medium text-dark-emphasis mb-2">
                      <span class="animate-target text-truncate">{{ $offer->title }}</span>
                    </div>
                    <div class="h6 mb-3">
                      ${{ number_format($offer->price, 2) }}
                      @if($offer->old_price)
                        <del class="fs-sm fw-normal text-body-tertiary">
                          ${{ number_format($offer->old_price, 2) }}
                        </del>
                      @endif
                    </div>
                    @if($offer->button_link && $offer->button_text)
                      <a class="btn btn-sm btn-dark stretched-link" href="{{ $offer->button_link }}">{{ $offer->button_text }}</a>
                    @endif
                  </div>
                  <div class="card-footer d-flex flex-column align-items-center border-0 pb-2"
                       @if($offer->expires_at)
                         data-countdown-date="{{ \Carbon\Carbon::parse($offer->expires_at)->format('m/d/Y H:i:s') }}"
                       @endif>
                    @if($offer->expires_at)
                      <div class="mb-1 text-muted small">
                        до {{ \Carbon\Carbon::parse($offer->expires_at)->translatedFormat('m - d - Y, H:i') }}
                      </div>
                      <div class="d-flex align-items-center justify-content-center">
                        <div class="btn btn-secondary pe-none px-2"><span data-days></span><span>д</span></div>
                        <div class="animate-blinking text-body-tertiary fs-lg fw-medium mx-2">:</div>
                        <div class="btn btn-secondary pe-none px-2"><span data-hours></span><span>г</span></div>
                        <div class="animate-blinking text-body-terтіary fs-lg fw-medium mx-2">:</div>
                        <div class="btn btn-secondary pe-none px-2"><span data-minutes></span><span>хв</span></div>
                      </div>
                    @endif
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>

      <!-- ПАГІНАЦІЯ -->
      <div class="swiper-pagination position-static d-md-none mt-n3 mb-2" id="bullets"></div>

      <!-- ЛІВА preview КАРТИНКА -->
      <div class="col-sm-9 col-md-8 col-xl-7 order-md-1 align-self-end">
        <div class="swiper user-select-none" id="previewImages" data-swiper='{
          "allowTouchMove": false,
          "loop": true,
          "effect": "fade",
          "fadeEffect": { "crossFade": true }
        }'>
          <div class="swiper-wrapper">
            @foreach($specialOffers as $offer)
              @php
                $imgPreview = $offer->preview_path
                  ? asset('storage/'.$offer->preview_path)
                  : ($offer->image_path ? asset('storage/'.$offer->image_path) : asset('assets/img/placeholder.svg'));
              @endphp
              <div class="swiper-slide">
                <div class="d-flex align-items-center justify-content-center h-100 w-100">
                  <img src="{{ $imgPreview }}"
                       alt="{{ $offer->title }}"
                       style="max-height: 600px; width: auto; height: auto;">
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    <!-- ФОН КОЛЬОРОМ з БАЗИ -->
    <div class="swiper position-absolute top-0 start-0 w-100 h-100 user-select-none" id="backgrounds" data-swiper='{
      "allowTouchMove": false,
      "loop": true,
      "effect": "fade",
      "fadeEffect": { "crossFade": true }
    }'>
      <div class="swiper-wrapper">
        @foreach($specialOffers as $offer)
          <div class="swiper-slide">
            <span class="position-absolute top-0 start-0 w-100 h-100 rounded-5"
                  style="background-color: {{ $offer->background_path }}"></span>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <!-- КНОПКИ НАВІГАЦІЇ -->
  <div class="d-none d-md-flex justify-content-center gap-2 pt-3 mt-2 mt-lg-3">
    <button type="button" class="btn btn-icon btn-outline-secondary animate-slide-start rounded-circle me-1" id="offerPrev" aria-label="Prev">
      <i class="ci-chevron-left fs-lg animate-target"></i>
    </button>
    <button type="button" class="btn btn-icon btn-outline-secondary animate-slide-end rounded-circle" id="offerNext" aria-label="Next">
      <i class="ci-chevron-right fs-lg animate-target"></i>
    </button>
  </div>
</section>
