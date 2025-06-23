<template>
    <section class="container pt-5 mt-2 mt-sm-3 mt-lg-4 mt-xl-5">
      
      <div class="row g-0 overflow-x-auto pb-2 pb-sm-3 mb-3">
        <div class="col-auto pb-1 pb-sm-0 mx-auto">
          <ul class="nav nav-pills flex-nowrap text-nowrap">
            <li class="nav-item">
              <a class="nav-link active" href="#">Best sellers</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">New arrivals</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">Sale items</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">Top rated</a>
            </li>
          </ul>
        </div>
      </div>
      <h2 class="text-center pb-2 pb-sm-3">Домашні тапочки</h2>
      <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 gy-4 gy-md-5">
        <div class="col" v-for="(product, n) in products" :key="product.id">
          <div class="animate-underline hover-effect-opacity">
            <div class="position-relative mb-3">
              <span
                class="badge text-bg-danger position-absolute top-0 start-0 z-2 mt-2 mt-sm-3 ms-2 ms-sm-3"
                v-if="n === 0"
              >Sale</span>
              <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
                <i class="ci-heart animate-target"></i>
              </button>
                <a class="d-block" :href="`/${locale}/product/${product.translations[0]?.slug}`">
                    <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
                        <img :src="product.images[0]?.full_url" alt="Product Image" style="object-fit: cover; width: 100%; height: 100%;" />
                    </div>
                </a>
            </div>
            <div class="nav mb-2">
              <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="#">
                <span class="text-truncate">{{ product.translations[0]?.name }}</span>
              </a>
            </div>
            <div class="h6 mb-2">
              {{ product.price }} грн
              <del v-if="product.old_price" class="fs-sm fw-normal text-body-tertiary">{{ product.old_price }} ₴</del>
            </div>
            <div class="position-relative">
              <div class="hover-effect-target fs-xs text-body-secondary opacity-100">+1 color</div>
              <div class="hover-effect-target d-flex gap-2 position-absolute top-0 start-0 opacity-0">
                <input type="radio" class="btn-check" :id="`color-${product.id}-1`" checked>
                <label :for="`color-${product.id}-1`" class="btn btn-color fs-base" style="color: #284971">
                  <span class="visually-hidden">Color</span>
                </label>
                <input type="radio" class="btn-check" :id="`color-${product.id}-2`">
                <label :for="`color-${product.id}-2`" class="btn btn-color fs-base" style="color: #8b9bc4">
                  <span class="visually-hidden">Color</span>
                </label>
              </div>
            </div>
          </div>
        </div>
        
      </div>
    </section>

  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue'
  import axios from 'axios'
  
  const products = ref([])
  const locale = window.location.pathname.split('/')[1] || 'ua'
  
  onMounted(async () => {
    const res = await axios.get(`/${locale}/api/products`)
    products.value = res.data
  })
  </script>
  