<template>
  <div class="d-flex flex-wrap gap-2 mb-4 w-100">
    <!-- Лічильник -->
    <div class="count-input d-flex align-items-center border rounded px-3">
      <button type="button" class="btn btn-icon btn-sm p-0" @click="decrement">
        <i class="ci-minus"></i>
      </button>
      <input
        type="number"
        class="form-control border-0 text-center shadow-none"
        :value="quantity"
        readonly
        style="width: 50px"
      />
      <button type="button" class="btn btn-icon btn-sm p-0" @click="increment">
        <i class="ci-plus"></i>
      </button>
    </div>

    <!-- В обране -->
    <button
      type="button"
      class="btn btn-icon btn-lg btn-secondary animate-pulse"
      title="До обраного"
    >
      <i class="ci-heart fs-base animate-target"></i>
    </button>

    <!-- Порівняти -->
    <button
      type="button"
      class="btn btn-icon btn-lg btn-secondary animate-rotate"
      title="Порівняти"
    >
      <i class="ci-refresh-cw fs-base animate-target"></i>
    </button>

    <!-- Кнопка "У кошик" -->
    <div class="flex-grow-1">
      <button
        type="button"
        class="btn btn-lg btn-primary w-100 animate-slide-end"
        @click="addToCart"
      >
        <i class="ci-shopping-cart fs-base animate-target me-2"></i>
        {{ $t('add_to_cart') }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useCartStore } from '@/stores/cart'

const emit = defineEmits(['added'])

const props = defineProps({
  product: {
    type: Object,
    required: true,
  },
})

const { locale } = useI18n()
const cart = useCartStore()
const quantity = ref(1)

const variants = computed(() => props.product.variants ?? [])

const increment = () => {
  if (quantity.value < 5) quantity.value++
}

const decrement = () => {
  if (quantity.value > 1) quantity.value--
}

const addToCart = () => {
  const sizeSelect = document.querySelector('select[name="size"]')
  const selectedSize = sizeSelect?.value || ''

  if (!selectedSize) {
    window.showGlobalToast('Будь ласка, виберіть розмір!', 'warning')

    if (sizeSelect) {
      sizeSelect.classList.add('is-invalid') // Підсвітка червоною рамкою
      sizeSelect.focus()
    }

    return
  }

  if (sizeSelect.classList.contains('is-invalid')) {
    sizeSelect.classList.remove('is-invalid') // Знімаємо підсвітку, якщо вже вибрали
  }

  const matchedVariant = variants.value.find(v => v.size === selectedSize)
  const finalPrice = matchedVariant?.price_override ?? props.product.price

  const productName =
    props.product.translations.find(t => t.locale === locale.value)?.name ||
    props.product.name

  cart.addToCart({
    id: props.product.id,
    name: productName,
    price: finalPrice,
    image: props.product.images?.[0]?.full_url || '',
    quantity: quantity.value,
    link: `/${locale.value}/product/${props.product.slug}`,
    size: selectedSize,
  })

  emit('added', productName)
  
  window.showGlobalToast('🛒  Товар додано в кошик', 'info')

  const cartEl = document.getElementById('shoppingCart')
  if (cartEl) {
    const bsOffcanvas = new bootstrap.Offcanvas(cartEl)
    bsOffcanvas.show()
  }
}
</script>

