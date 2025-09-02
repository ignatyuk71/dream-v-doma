<template>
  <div class="d-flex flex-wrap gap-2 mb-4 w-100">
    <!-- Лічильник -->
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
    <button type="button" class="btn btn-icon btn-lg btn-secondary animate-pulse" title="До обраного">
      <i class="ci-heart fs-base animate-target"></i>
    </button>

    <!-- Порівняти -->
    <button type="button" class="btn btn-icon btn-lg btn-secondary animate-rotate" title="Порівняти">
      <i class="ci-refresh-cw fs-base animate-target"></i>
    </button>

    <!-- У кошик -->
    <div class="flex-grow-1">
      <button type="button" class="btn btn-lg btn-primary w-100 animate-slide-end" @click="addToCart">
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

// джерело варіантів: пріоритет — props, fallback — window.productVariants
const variants = computed(() => {
  if (Array.isArray(props.product?.variants)) return props.product.variants
  if (Array.isArray(window.productVariants)) return window.productVariants
  return []
})

// число з 2 знаками
const toNum = (v) => {
  const s = String(v ?? '').replace(',', '.').replace(/[^\d.\-]/g, '')
  const n = parseFloat(s)
  return Number.isFinite(n) ? Number(n.toFixed(2)) : 0
}

const increment = () => { if (quantity.value < 10) quantity.value++ }
const decrement = () => { if (quantity.value > 1) quantity.value-- }

// читаємо вибраний розмір з <select name="size">
const getSelectedSize = () => {
  const el = document.querySelector('select[name="size"]')
  return el?.value?.toString() ?? ''
}

const getMatchedVariant = (size) => {
  if (!size) return null
  // якщо в майбутньому додасться колір — тут легко розширити
  return variants.value.find(v => (v?.size ?? '') === size) || null
}

const addToCart = async () => {
  // 1) вибір розміру
  const sizeSelect = document.querySelector('select[name="size"]')
  const selectedSize = getSelectedSize()
  if (!selectedSize) {
    window.showGlobalToast?.('Будь ласка, виберіть розмір!', 'warning')
    sizeSelect?.classList.add('is-invalid'); sizeSelect?.focus()
    return
  }
  sizeSelect?.classList.remove('is-invalid')

  // 2) знаходимо варіант
  const matchedVariant = getMatchedVariant(selectedSize)
  if (!matchedVariant) {
    window.showGlobalToast?.('Обраний розмір недоступний', 'danger')
    return
  }

  // 2.1) перевіряємо склад (якщо приходить quantity з бекенда)
  const stock = Number(matchedVariant.quantity ?? 0)
  if (stock > 0 && quantity.value > stock) {
    quantity.value = stock
    window.showGlobalToast?.(`На складі лише ${stock} шт.`, 'warning')
  }

  // 3) дані товару
  const rawPrice   = matchedVariant.price_override ?? props.product.price
  const finalPrice = toNum(rawPrice)
  const productName =
    props.product?.translations?.find(t => t.locale === locale.value)?.name ||
    props.product?.translations?.find(t => t.locale === 'uk')?.name ||
    props.product?.translations?.[0]?.name ||
    props.product?.name || ''
  const currency = window.metaPixelCurrency || 'UAH'

  // 4) кладемо в кошик
  await cart.addToCart({
    id: matchedVariant.id,
    product_id: props.product.id,
    variant_sku: matchedVariant.variant_sku ?? null,
    name: productName,
    price: finalPrice,
    image: props.product.images?.[0]?.full_url || props.product.images?.[0]?.url || '',
    quantity: quantity.value,
    link: props.product.url,
    size: matchedVariant.size,
    color: matchedVariant.color ?? '',
  })

  // 5) UI
  emit('added', productName)
  window.showGlobalToast?.('🛒  Товар додано в кошик', 'info')
  const cartEl = document.getElementById('shoppingCart')
  if (cartEl && window.bootstrap?.Offcanvas) new bootstrap.Offcanvas(cartEl).show()

  // 6) ТРЕК ATC: ТІЛЬКИ variant_sku
  const vSku = (matchedVariant.variant_sku ?? '').toString().trim()
  if (!vSku) {
    window.showGlobalToast?.('⚠️ Відсутній артикул варіанта (variant_sku). Подія трекінгу пропущена.', 'warning')
    console.warn('[ATC] variant_sku missing — skip tracking to avoid wrong id!', matchedVariant)
    return
  }

  try {
    if (typeof window.mpTrackATC === 'function') {
      console.log('[ATC] sending', { variant_sku: vSku, price: finalPrice, qty: quantity.value })
      window.mpTrackATC({
        variant_sku: vSku,
        price: finalPrice,
        quantity: quantity.value,
        name: productName,
        currency
      })
    } else {
      console.warn('[ATC] mpTrackATC is not defined (partial not loaded yet)')
    }
  } catch (e) {
    console.warn('[ATC] tracking error', e)
  }
}
</script>
