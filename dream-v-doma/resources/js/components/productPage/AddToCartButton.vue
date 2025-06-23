<template>
    <div class="count-input flex-shrink-0 order-sm-1">
      <button type="button" class="btn btn-icon btn-lg" @click="decrement">
        <i class="ci-minus"></i>
      </button>
      <input type="number" class="form-control form-control-lg" :value="quantity" readonly>
      <button type="button" class="btn btn-icon btn-lg" @click="increment">
        <i class="ci-plus"></i>
      </button>
    </div>
  
    <button type="button" class="btn btn-icon btn-lg btn-secondary animate-pulse order-sm-3 order-md-2 order-lg-3"
      data-bs-toggle="tooltip" data-bs-placement="top"
      :data-bs-title="$t('add_to_wishlist')">
      <i class="ci-heart fs-lg animate-target"></i>
    </button>
  
    <button type="button" class="btn btn-icon btn-lg btn-secondary animate-rotate order-sm-4 order-md-3 order-lg-4"
      data-bs-toggle="tooltip" data-bs-placement="top"
      :data-bs-title="$t('compare')">
      <i class="ci-refresh-cw fs-lg animate-target"></i>
    </button>
  
    <button type="button" class="btn btn-lg btn-primary w-100 animate-slide-end order-sm-2 order-md-4 order-lg-2"
      @click="addToCart">
      <i class="ci-shopping-cart fs-lg animate-target ms-n1 me-2"></i>
      {{ $t('add_to_cart') }}
    </button>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useCartStore } from '@/stores/cart'
  
  const emit = defineEmits(['added'])
  
  const props = defineProps({
    product: {
      type: Object,
      required: true
    }
  })
  
  const { locale, t } = useI18n()
  const cart = useCartStore()
  const quantity = ref(1)
  
  const increment = () => {
    if (quantity.value < 5) quantity.value++
  }
  
  const decrement = () => {
    if (quantity.value > 1) quantity.value--
  }
  
  const addToCart = () => {
    const productName = props.product.translations.find(t => t.locale === locale.value)?.name || props.product.name
  
    cart.addToCart({
      id: props.product.id,
      name: productName,
      price: props.product.price,
      image: props.product.images?.[0]?.full_url || '',
      quantity: quantity.value,
      link: `/${locale.value}/product/${props.product.slug}`
    })
  
    // 🔔 Виклик тосту
    emit('added', productName)
  
    // 🛒 Відкриття кошика
    const cartEl = document.getElementById('shoppingCart')
    if (cartEl) {
      const bsOffcanvas = new bootstrap.Offcanvas(cartEl)
      bsOffcanvas.show()
    }
  }
  </script>
  