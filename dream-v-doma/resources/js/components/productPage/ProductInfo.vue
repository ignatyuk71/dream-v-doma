<template>
    <div class="ps-md-4 ps-xl-5">
  <!-- Назва -->
    <h1 class="h3 mt-3">
        {{ product.translations.find(t => t.locale === $i18n.locale)?.name || '—' }}
    </h1>
      <!-- Зірки + Код товару в ряд -->
    <div class="d-flex justify-content-between align-items-center mb-2">
      <a class="d-flex align-items-center gap-2 text-decoration-none" href="#reviews">
        <div class="d-flex gap-1 fs-sm">
          <i class="ci-star-filled text-warning"></i>
          <i class="ci-star-filled text-warning"></i>
          <i class="ci-star-filled text-warning"></i>
          <i class="ci-star-filled text-warning"></i>
          <i class="ci-star text-body-tertiary opacity-75"></i>
        </div>
        <span class="text-body-tertiary fs-sm">
          {{ product.reviews?.length || 0 }} {{ $t('reviews') }}
        </span>
      </a>

      <div class="text-muted fs-sm">
        <strong>{{ $t('product_code') }}:</strong> {{ product.sku }}
      </div>
    </div>
  
      <!-- Ціна -->
        <div class="d-flex flex-wrap align-items-center mb-1">
        <div class="h4 d-flex align-items-center my-4">
            {{ Number(product.price).toFixed(2) }} грн
            <del class="fs-5 fw-normal text-body-tertiary ms-3">
            453 ₴
            </del>
        </div>
        <div class="d-flex align-items-center text-success fs-sm ms-auto">
            <i class="ci-check-circle fs-base me-2"></i>
            {{ $t('available') }}
        </div>
        </div>

  
      <!-- Варіанти -->
      <ProductVariants :product="product" />
  
      <!-- Кількість + Кнопки -->
      <div class="d-flex flex-wrap flex-sm-nowrap flex-md-wrap flex-lg-nowrap gap-3 gap-lg-2 gap-xl-3 mb-4">

      </div>
      
  
      <!-- Варіанти доставки -->
      <ProductDelivery />
  
    </div>
  </template>
  
  <script setup>
  import ProductVariants from './ProductVariants.vue'
  import ProductDelivery from './ProductDelivery.vue'
  
  defineProps({
    product: {
      type: Object,
      required: true
    }
  })
  </script>
  