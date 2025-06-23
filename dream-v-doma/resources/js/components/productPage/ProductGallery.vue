<template>
      <!-- Preview -->
      <div class="swiper" :data-swiper="JSON.stringify(mainSwiperOptions)">
        <div class="swiper-wrapper">
          <div
            class="swiper-slide"
            v-for="image in product.images"
            :key="image.id"
          >
            <div class="image-wrapper">
              <img
                :src="image.full_url"
                :data-zoom="image.full_url"
                :alt="product.meta_title"
                :data-zoom-options="JSON.stringify(zoomOptions)"
              />
            </div>
          </div>
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
        :data-swiper="JSON.stringify(thumbsSwiperOptions)"
      >
        <div class="swiper-wrapper">
          <div
            class="swiper-slide swiper-thumb"
            v-for="image in product.images"
            :key="image.id"
          >
            <div class="thumb-wrapper">
              <img
                :src="image.full_url"
                class="swiper-thumb-img"
                :alt="product.meta_title"
              />
            </div>
          </div>
        </div>
      </div>
  </template>
  
  <script setup>
  
const props = defineProps({
  product: {
    type: Object,
    required: true
  }
})

// Динамічно вимикаємо loop, якщо менше 3 фото
const mainSwiperOptions = {
  loop: props.product.images.length >= 3,
  navigation: {
    prevEl: '.btn-prev',
    nextEl: '.btn-next'
  },
  thumbs: {
    swiper: '#thumbs'
  }
}

const thumbsSwiperOptions = {
  loop: props.product.images.length >= 3,
  spaceBetween: 12,
  slidesPerView: 3,
  watchSlidesProgress: true,
  breakpoints: {
    340: { slidesPerView: 4 },
    500: { slidesPerView: 5 },
    600: { slidesPerView: 6 },
    768: { slidesPerView: 4 },
    992: { slidesPerView: 5 },
    1200: { slidesPerView: 6 }
  }
}

const zoomOptions = {
  paneSelector: '#zoomPane',
  inlinePane: 768,
  hoverDelay: 500,
  touchDisable: true
}
</script>

  
  <style scoped>
.image-wrapper {
  width: 100%;
  padding-top: 100%; /* квадратне співвідношення, можна змінити */
  position: relative;
  background: #fff;
}

.image-wrapper img {
  position: absolute;
  top: 0; left: 0;
  width: 100%;
  height: 100%;
  object-fit: contain; /* 🔥 щоб не обрізало */
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
  