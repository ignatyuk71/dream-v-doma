<template>
    <section class="container pt-5 mt-2 mt-sm-3 mt-lg-4 mt-xl-1">
      <div class="d-flex align-items-center justify-content-between pt-1 pt-lg-0 pb-3 mb-2 mb-sm-1">
        <h2 class="mb-0 me-3">{{ title }}</h2>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-icon btn-outline-secondary animate-slide-start rounded-circle me-1" :id="prevEl" aria-label="Prev">
            <i class="ci-chevron-left fs-lg animate-target"></i>
          </button>
          <button type="button" class="btn btn-icon btn-outline-secondary animate-slide-end rounded-circle" :id="nextEl" aria-label="Next">
            <i class="ci-chevron-right fs-lg animate-target"></i>
          </button>
        </div>
      </div>
  
      <div class="swiper" :data-swiper="swiperOptions">
        <div class="swiper-wrapper">
          <div v-for="(product, index) in products" :key="index" class="swiper-slide">
            <a :href="`/product/${product.slug}`" class="d-flex bg-body-tertiary rounded p-3 text-decoration-none">
              <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
                <img :src="product.image" :alt="product.name" />
              </div>
            </a>
            <div class="nav mb-2 mt-3">
              <span class="nav-link animate-target min-w-0 text-dark-emphasis p-0 text-truncate">{{ product.name }}</span>
            </div>
            <div class="h6 mb-2">
              {{ product.price }} <del v-if="product.old_price" class="fs-sm text-body-tertiary">{{ product.old_price }}</del>
            </div>
          </div>
        </div>
      </div>
    </section>
  </template>
  
  <script setup>
  import { onMounted, computed } from 'vue'
  
  const props = defineProps({
    products: { type: Array, required: true },
    title: { type: String, default: 'Рекомендовані товари' },
    uid: { type: String, default: 'carousel1' }
  })
  
  const prevEl = computed(() => `#${props.uid}-prev`)
  const nextEl = computed(() => `#${props.uid}-next`)
  
  const swiperOptions = computed(() => JSON.stringify({
    slidesPerView: 2,
    spaceBetween: 24,
    loop: true,
    navigation: {
      prevEl: `#${props.uid}-prev`,
      nextEl: `#${props.uid}-next`
    },
    breakpoints: {
      768: { slidesPerView: 3 },
      992: { slidesPerView: 4 }
    }
  }))
  
  onMounted(() => {
    // якщо Swiper ініціалізація не відбувається автоматично — підключити вручну тут
  })
  </script>
  