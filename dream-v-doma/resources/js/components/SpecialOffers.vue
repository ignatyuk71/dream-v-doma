<template>
    <section class="container pt-5 mt-2 mt-sm-3 mt-lg-4 mt-xl-5">
      <h3 class="text-center pt-xxl-2 pb-2 pb-md-3">Special offers for you</h3>
      <div class="position-relative px-4 px-md-3 px-lg-4">
        <div class="row position-relative z-2 justify-content-center">
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
                <div class="swiper-slide h-auto">
                  <div class="card animate-underline h-100 rounded-5 border-0">
                    <div class="pt-3 px-3">
                      <span class="badge text-bg-danger position-absolute top-0 start-0 z-2 mt-2 mt-sm-3 ms-2 ms-sm-3">-30%</span>
                      <img src="/public/assets/img/home/fashion/v2/offers-slider/product01.png" alt="Image">
                    </div>
                    <div class="card-body text-center py-3">
                      <div class="d-flex justify-content-center min-w-0 fs-sm fw-medium text-dark-emphasis mb-2">
                        <span class="animate-target text-truncate">Polarized sunglasses for men</span>
                      </div>
                      <div class="h6 mb-3">$56.00 <del class="fs-sm fw-normal text-body-tertiary">$80.00</del></div>
                      <a class="btn btn-sm btn-dark stretched-link" href="/product">Shop now</a>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-center bg-transparent border-0 pb-4" data-countdown-date="10/15/2025 12:00:00">
                      <div class="btn btn-secondary pe-none px-2"><span data-days></span><span>d</span></div>
                      <div class="animate-blinking text-body-tertiary fs-lg fw-medium mx-2">:</div>
                      <div class="btn btn-secondary pe-none px-2"><span data-hours></span><span>h</span></div>
                      <div class="animate-blinking text-body-tertiary fs-lg fw-medium mx-2">:</div>
                      <div class="btn btn-secondary pe-none px-2"><span data-minutes></span><span>m</span></div>
                    </div>
                  </div>
                </div>
                <div class="swiper-slide h-auto">
                  <div class="card animate-underline h-100 rounded-5 border-0">
                    <div class="pt-3 px-3">
                      <span class="badge text-bg-danger position-absolute top-0 start-0 z-2 mt-2 mt-sm-3 ms-2 ms-sm-3">-17%</span>
                      <img src="/public/assets/img/home/fashion/v2/offers-slider/product02.png" alt="Image">
                    </div>
                    <div class="card-body text-center py-3">
                      <div class="d-flex justify-content-center min-w-0 fs-sm fw-medium text-dark-emphasis mb-2">
                        <span class="animate-target text-truncate">Fashionable women fedora hat</span>
                      </div>
                      <div class="h6 mb-3">$43.00 <del class="fs-sm fw-normal text-body-tertiary">$50.00</del></div>
                      <a class="btn btn-sm btn-dark stretched-link" href="/product">Shop now</a>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-center bg-transparent border-0 pb-4" data-countdown-date="10/15/2025 12:00:00">
                      <div class="btn btn-secondary pe-none px-2"><span data-days></span><span>d</span></div>
                      <div class="animate-blinking text-body-tertiary fs-lg fw-medium mx-2">:</div>
                      <div class="btn btn-secondary pe-none px-2"><span data-hours></span><span>h</span></div>
                      <div class="animate-blinking text-body-tertiary fs-lg fw-medium mx-2">:</div>
                      <div class="btn btn-secondary pe-none px-2"><span data-minutes></span><span>m</span></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
  
          <div class="swiper-pagination position-static d-md-none mt-n3 mb-2" id="bullets"></div>
  
          <div class="col-sm-9 col-md-8 col-xl-7 order-md-1 align-self-end">
            <div class="swiper user-select-none" id="previewImages" data-swiper='{
              "allowTouchMove": false,
              "loop": true,
              "effect": "fade",
              "fadeEffect": { "crossFade": true }
            }'>
              <div class="swiper-wrapper">
                <div class="swiper-slide">
                  <div class="ratio" style="--cz-aspect-ratio: calc(542 / 718 * 100%)">
                    <img src="/public/assets/img/home/fashion/v2/offers-slider/preview01.png" alt="Image">
                  </div>
                </div>
                <div class="swiper-slide">
                  <div class="ratio" style="--cz-aspect-ratio: calc(542 / 718 * 100%)">
                    <img src="/public/assets/img/home/fashion/v2/offers-slider/preview02.png" alt="Image">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
  
        <div class="swiper position-absolute top-0 start-0 w-100 h-100 user-select-none" id="backgrounds" data-swiper='{
          "allowTouchMove": false,
          "loop": true,
          "effect": "fade",
          "fadeEffect": { "crossFade": true }
        }'>
          <div class="swiper-wrapper">
            <div class="swiper-slide">
              <span class="position-absolute top-0 start-0 w-100 h-100 rounded-5 d-none-dark" style="background-color: #dceee7"></span>
              <span class="position-absolute top-0 start-0 w-100 h-100 rounded-5 d-none d-block-dark" style="background-color: #1b282c"></span>
            </div>
            <div class="swiper-slide">
              <span class="position-absolute top-0 start-0 w-100 h-100 rounded-5 d-none-dark" style="background-color: #dddad7"></span>
              <span class="position-absolute top-0 start-0 w-100 h-100 rounded-5 d-none d-block-dark" style="background-color: #272729"></span>
            </div>
          </div>
        </div>
      </div>
  
      <div class="d-none d-md-flex justify-content-center gap-2 pt-3 mt-2 mt-lg-3">
        <button type="button" class="btn btn-icon btn-outline-secondary animate-slide-start rounded-circle me-1" id="offerPrev" aria-label="Prev">
          <i class="ci-chevron-left fs-lg animate-target"></i>
        </button>
        <button type="button" class="btn btn-icon btn-outline-secondary animate-slide-end rounded-circle" id="offerNext" aria-label="Next">
          <i class="ci-chevron-right fs-lg animate-target"></i>
        </button>
      </div>
    </section>
  </template>
  
  <script setup>
  // No logic needed
  </script>
  