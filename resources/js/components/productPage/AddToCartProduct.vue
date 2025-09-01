<template>
  <div class="d-flex flex-wrap gap-2 mb-4 w-100">
    <!-- Лічильник (.count-input з data-* + блокування дубль-хендлерів теми) -->
    <div class="count-input">
      <button
        type="button"
        class="btn btn-icon btn-lg"
        data-decrement
        :disabled="quantity <= 1"
        aria-label="Decrement quantity"
        @click="(e)=>{ e.preventDefault(); e.stopPropagation(); e.stopImmediatePropagation && e.stopImmediatePropagation(); if (quantity > 1) decrement() }"
        @keydown.enter.prevent.stop="(e)=>{ e.stopImmediatePropagation && e.stopImmediatePropagation(); if (quantity > 1) decrement() }"
        @keydown.space.prevent.stop="(e)=>{ e.stopImmediatePropagation && e.stopImmediatePropagation(); if (quantity > 1) decrement() }"
      >
        <i class="ci-minus"></i>
      </button>

      <input
        type="number"
        class="form-control form-control-lg"
        :value="quantity"
        min="1"
        max="10"
        readonly
        inputmode="numeric"
        aria-live="polite"
      />

      <button
        type="button"
        class="btn btn-icon btn-lg"
        data-increment
        :disabled="quantity >= 10"
        aria-label="Increment quantity"
        @click="(e)=>{ e.preventDefault(); e.stopPropagation(); e.stopImmediatePropagation && e.stopImmediatePropagation(); if (quantity < 10) increment() }"
        @keydown.enter.prevent.stop="(e)=>{ e.stopImmediatePropagation && e.stopImmediatePropagation(); if (quantity < 10) increment() }"
        @keydown.space.prevent.stop="(e)=>{ e.stopImmediatePropagation && e.stopImmediatePropagation(); if (quantity < 10) increment() }"
      >
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

    <!-- У кошик -->
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
const props = defineProps({ product: { type: Object, required: true } })

const { locale } = useI18n()
const cart = useCartStore()
const quantity = ref(1)

const variants = computed(() => props.product.variants ?? [])

const increment = () => { if (quantity.value < 10) quantity.value++ }
const decrement = () => { if (quantity.value > 1) quantity.value-- }

const addToCart = () => {
  // 1) перевіряємо вибір розміру (як і було)
  const sizeSelect = document.querySelector('select[name="size"]')
  const selectedSize = sizeSelect?.value || ''
  if (!selectedSize) {
    window.showGlobalToast('Будь ласка, виберіть розмір!', 'warning')
    if (sizeSelect) { sizeSelect.classList.add('is-invalid'); sizeSelect.focus() }
    return
  }
  sizeSelect?.classList.remove('is-invalid')

  // 2) знаходимо варіант
  const matchedVariant = variants.value.find(v => v.size === selectedSize)
  if (!matchedVariant) {
    window.showGlobalToast('Обраний розмір недоступний', 'danger')
    return
  }

  // 3) ціна/назва/валюта
  const finalPrice = matchedVariant.price_override ?? props.product.price
  const productName =
    props.product?.translations?.find(t => t.locale === locale.value)?.name ||
    props.product?.translations?.find(t => t.locale === 'uk')?.name ||
    props.product?.translations?.[0]?.name ||
    props.product?.name ||
    ''

  const currency = (window.metaPixelCurrency || 'UAH')

  // 4) додаємо до кошика (твоя бізнес-логіка як і була)
  cart.addToCart({
    id: matchedVariant.id,
    product_id: props.product.id,
    name: productName,
    price: finalPrice,
    image: props.product.images?.[0]?.full_url || '',
    quantity: quantity.value,
    link: props.product.url,    // готовий URL із Blade
    size: matchedVariant.size,
    color: matchedVariant.color ?? '',
  })

  // 5) UI-реакції
  emit('added', productName)
  window.showGlobalToast('🛒  Товар додано в кошик', 'info')
  const cartEl = document.getElementById('shoppingCart')
  if (cartEl && window.bootstrap?.Offcanvas) new bootstrap.Offcanvas(cartEl).show()

  // 6) ТРЕКІНГ AddToCart (Pixel + CAPI з одним event_id через паршал)
  try {
    if (window.mpTrackATC) {
      window.mpTrackATC({
        sku: matchedVariant.sku || props.product.sku || props.product.id, // content_id
        price: finalPrice,
        quantity: quantity.value,
        name: productName,
        currency
      })
    }
  } catch (_) { /* нехай трекінг не ламає UX */ }
}
</script>

