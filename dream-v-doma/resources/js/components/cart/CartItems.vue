<template>
    <button type="button"
      class="btn btn-icon btn-lg fs-xl btn-outline-secondary position-relative border-0 rounded-circle animate-scale"
      data-bs-toggle="offcanvas"
      data-bs-target="#shoppingCart"
      aria-controls="shoppingCart"
      aria-label="Кошик">
      <i class="ci-shopping-bag animate-target me-1"></i>
      <span v-if="cart.itemCount > 0" 
            class="position-absolute top-0 start-100 badge fs-xs text-bg-primary rounded-pill mt-1 ms-n4 z-2"
            style="--cz-badge-padding-y: .25em; --cz-badge-padding-x: .42em">
        {{ cart.itemCount }}
      </span>
    </button>
  </template>
  
  <script setup>
  import { useCartStore } from '@/stores/cart'
  
  const cart = useCartStore()
  </script>
  