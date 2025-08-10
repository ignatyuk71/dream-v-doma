<template>
    <div class="mt-3">
      <div v-if="progress < 100" class="small text-danger fw-semibold mb-1">
        🔥 До безкоштовної доставки залишилось {{ remaining }} грн
      </div>
      <div v-else class="small text-success fw-semibold mb-1">
        ✅ Для вас доставка безкоштовна!
      </div>
  
      <div class="progress" style="height: 6px;">
        <div
          class="progress-bar"
          :class="progress < 100 ? 'bg-danger' : 'bg-success'"
          :style="{ width: progress + '%', transition: 'width 0.4s ease' }"
        ></div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { computed } from 'vue'
  import { useCartStore } from '@/stores/cart'
  
  const cart = useCartStore()
  
  const freeLimit = 1000
  
  // % прогресу на основі суми в кошику
  const progress = computed(() => {
    return Math.min(100, Math.round((cart.subtotal / freeLimit) * 100))
  })
  
  // Скільки ще залишилось до 1000 грн
  const remaining = computed(() => {
    const left = freeLimit - cart.subtotal
    return left > 0 ? left : 0
  })
  </script>
  