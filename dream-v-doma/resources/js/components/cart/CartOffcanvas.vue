<template>
  <div class="offcanvas offcanvas-end pb-sm-2 px-sm-2" id="shoppingCart" tabindex="-1" aria-labelledby="shoppingCartLabel" style="width: 500px">
    <!-- Header -->
    <div class="offcanvas-header flex-column align-items-start py-3 pt-lg-4">
      <div class="d-flex align-items-center justify-content-between w-100 mb-3 mb-lg-4">
        <h4 class="offcanvas-title" id="shoppingCartLabel">{{ $t('shopping_cart') }}</h4>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <p class="fs-sm">
        {{ $t('buy_more_to_free_shipping') }}
        <span class="text-dark-emphasis fw-semibold">{{ remainingAmount }} грн</span>
      </p>
      <div class="progress w-100" role="progressbar" aria-label="Free shipping progress" :aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100" style="height: 4px">
        <div class="progress-bar bg-dark rounded-pill" :style="{ width: progress + '%' }"></div>
      </div>
    </div>

    <!-- Items -->
    
    <div class="offcanvas-body d-flex flex-column gap-4 pt-2">
      <div v-if="items.length === 0" class="text-center text-muted">{{ $t('cart_empty') }}</div>

      <div v-for="item in items" :key="item.id" class="d-flex align-items-center">
        <img
          :src="item.image"
          alt="Thumbnail"
          class="rounded shadow-sm"
          style="
            width: 110px;
            height: 110px;
            display: block;
            object-fit: cover;
            object-position: center center;
            flex-shrink: 0;"/>
        <div class="w-100 min-w-0 ps-3">
          <h5 class="d-flex animate-underline mb-2">
            <a class="d-block fs-sm fw-medium text-truncate animate-target" :href="item.link">{{ item.name }}</a>
          </h5>
          <div class="h6 pb-1 mb-2">{{ item.price }} грн</div>
          <div class="d-flex align-items-center justify-content-between">
            <div class="count-input rounded-2">
              <button type="button" class="btn btn-icon btn-sm" @click="decrement(item.id)">
                <i class="ci-minus"></i>
              </button>
              <input type="number" class="form-control form-control-sm" :value="item.quantity" readonly>
              <button type="button" class="btn btn-icon btn-sm" @click="increment(item.id)">
                <i class="ci-plus"></i>
              </button>
            </div>
            <button type="button" class="btn-close fs-sm" @click="removeItem(item.id)" :title="$t('remove_from_cart')" aria-label="Remove"></button>
          </div>
        </div>
      </div>
    </div>


    <!-- Footer -->
    <div class="offcanvas-header flex-column align-items-start">
      <div class="d-flex align-items-center justify-content-between w-100 mb-3 mb-md-4">
        <span class="text-light-emphasis">{{ $t('subtotal') }}:</span>
        <span class="h6 mb-0">{{ subtotal }} грн</span>
      </div>
      <div class="d-flex gap-2 w-100">
          <button
            class="btn btn-outline-secondary w-100"
            data-bs-dismiss="offcanvas"
          >
            {{ $t('continue_shopping') }}
          </button>
          <a
          :href="`/${$i18n.locale}/checkout`"
            class="btn btn-dark w-100"
          >
            {{ $t('checkout_cart') }}
          </a>
</div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useCartStore } from '@/stores/cart'

const cart = useCartStore()

const items = computed(() => cart.items)
const subtotal = computed(() => cart.subtotal)

const freeShippingFrom = 1000
const remainingAmount = computed(() => Math.max(0, freeShippingFrom - subtotal.value))
const progress = computed(() => Math.min(100, (subtotal.value / freeShippingFrom) * 100))

const increment = cart.increment
const decrement = cart.decrement
const removeItem = cart.removeItem
</script>
