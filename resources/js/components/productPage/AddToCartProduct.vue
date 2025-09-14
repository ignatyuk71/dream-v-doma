<template>
  <div class="d-flex flex-wrap gap-2 mb-4 w-100">
    <!-- Лічильник кількості (1…10 або менше, якщо мало на складі) -->
    <div class="count-input">
      <button
        type="button"
        class="btn btn-icon btn-lg"
        data-decrement
        :disabled="quantity <= 1 || isOutOfStock || isAdding"
        aria-label="Decrement quantity"
        @click="(e)=>{ e.preventDefault(); e.stopPropagation(); e.stopImmediatePropagation && e.stopImmediatePropagation(); if (quantity > 1 && !isOutOfStock && !isAdding) decrement() }"
        @keydown.enter.prevent.stop="(e)=>{ e.stopImmediatePropagation && e.stopImmediatePropagation(); if (quantity > 1 && !isOutOfStock && !isAdding) decrement() }"
        @keydown.space.prevent.stop="(e)=>{ e.stopImmediatePropagation && e.stopImmediatePropagation(); if (quantity > 1 && !isOutOfStock && !isAdding) decrement() }"
      >
        <i class="ci-minus"></i>
      </button>

      <input
        type="number"
        class="form-control form-control-lg"
        :value="quantity"
        min="1"
        :max="maxAllowedQty"
        readonly
        inputmode="numeric"
        aria-live="polite"
      />

      <button
        type="button"
        class="btn btn-icon btn-lg"
        data-increment
        :disabled="quantity >= maxAllowedQty || isOutOfStock || isAdding"
        aria-label="Increment quantity"
        @click="(e)=>{ e.preventDefault(); e.stopPropagation(); e.stopImmediatePropagation && e.stopImmediatePropagation(); if (quantity < maxAllowedQty && !isOutOfStock && !isAdding) increment() }"
        @keydown.enter.prevent.stop="(e)=>{ e.stopImmediatePropagation && e.stopImmediatePropagation(); if (quantity < maxAllowedQty && !isOutOfStock && !isAdding) increment() }"
        @keydown.space.prevent.stop="(e)=>{ e.stopImmediatePropagation && e.stopImmediatePropagation(); if (quantity < maxAllowedQty && !isOutOfStock && !isAdding) increment() }"
      >
        <i class="ci-plus"></i>
      </button>
    </div>

    <!-- В обране -->
    <button type="button" class="btn btn-icon btn-lg btn-secondary animate-pulse" title="До обраного" :disabled="isAdding">
      <i class="ci-heart fs-base animate-target"></i>
    </button>

    <!-- Порівняти -->
    <button type="button" class="btn btn-icon btn-lg btn-secondary animate-rotate" title="Порівняти" :disabled="isAdding">
      <i class="ci-refresh-cw fs-base animate-target"></i>
    </button>

    <!-- У кошик -->
    <div class="flex-grow-1">
      <button
        type="button"
        :class="['btn','btn-lg','w-100','animate-slide-end', ctaClass]"
        :disabled="ctaDisabled"
        :aria-disabled="ctaDisabled ? 'true' : 'false'"
        :title="ctaTitle"
        @click="onCtaClick"
      >
        <!-- спінер під час додавання -->
        <span v-if="isAdding" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>

        <!-- іконки для станів (без спінера) -->
        <i v-else-if="justAdded" class="ci-check-circle fs-base animate-target me-2"></i>
        <i v-else-if="isOutOfStock" class="ci-close-circle fs-base animate-target me-2"></i>
        <i v-else-if="needsSize" class="ci-alert-circle fs-base animate-target me-2"></i>
        <i v-else class="ci-shopping-cart fs-base animate-target me-2"></i>

        {{ ctaText }}
      </button>
    </div>
  </div>
</template>

<script setup>
/**
 * Кнопка "У кошик" зі станами:
 *  - ready:        "Додати в кошик" (primary)
 *  - needsSize:    "Оберіть розмір" (primary, клік підсвічує <select>)
 *  - outOfStock:   "Немає в наявності" (secondary, disabled)
 *  - isAdding:     спінер, заблоковано
 *  - justAdded:    "Додано!" (success ~1.2s)
 */

import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useCartStore } from '@/stores/cart'

/* ---- вхідні дані та сервіси ---- */
const emit  = defineEmits(['added'])
const props = defineProps({ product: { type: Object, required: true } })
const { locale, t } = useI18n()
const cart = useCartStore()

/* ---- локальний стан ---- */
const quantity   = ref(1)
const isAdding   = ref(false)   // true під час додавання
const justAdded  = ref(false)   // true коротко після успіху

// Розмір із зовнішнього <select name="size">
const selectedSize = ref('')
let sizeEl = null

/* ---- джерело варіантів ---- */
const variants = computed(() => {
  if (Array.isArray(props.product?.variants)) return props.product.variants
  if (Array.isArray(window.productVariants))  return window.productVariants
  return []
})

/* ---- агрегована кількість ---- */
const variantsTotal = computed(() =>
  variants.value.reduce((acc, v) => acc + (parseInt(v?.quantity ?? 0) || 0), 0)
)

/* ---- синхронізація з DOM ---- */
const readSelectedSize = () => {
  const el = document.querySelector('select[name="size"]')
  sizeEl = el
  selectedSize.value = el?.value?.toString() ?? ''
}

onMounted(() => {
  readSelectedSize()
  if (sizeEl) sizeEl.addEventListener('change', readSelectedSize, { passive: true })
})
onBeforeUnmount(() => { if (sizeEl) sizeEl.removeEventListener('change', readSelectedSize) })

/* ---- варіант, доступність ---- */
const matchedVariant = computed(() => {
  const sz = selectedSize.value
  if (!sz) return null
  return variants.value.find(v => (v?.size ?? '') === sz) || null
})

const availableQty = computed(() => {
  if (variants.value.length) {
    if (matchedVariant.value) return parseInt(matchedVariant.value.quantity ?? 0) || 0
    return variantsTotal.value
  }
  return parseInt(props.product?.stock_total ?? props.product?.quantity_in_stock ?? 0) || 0
})

const isOutOfStock = computed(() => (availableQty.value || 0) <= 0)
const needsSize    = computed(() => variants.value.length > 0 && !matchedVariant.value)

/* ---- обмеження лічильника ---- */
const maxAllowedQty = computed(() => {
  const cap = availableQty.value > 0 ? availableQty.value : 10
  return Math.min(10, cap)
})
const increment = () => { if (!isOutOfStock.value && !isAdding.value && quantity.value < maxAllowedQty.value) quantity.value++ }
const decrement = () => { if (!isOutOfStock.value && !isAdding.value && quantity.value > 1) quantity.value-- }
watch(availableQty, (qty) => {
  if (qty <= 0) { quantity.value = 1 }
  else if (quantity.value > qty) { quantity.value = Math.max(1, qty) }
})

/* ---- утиліти ---- */
const toNum = (v) => {
  const s = String(v ?? '').replace(',', '.').replace(/[^\d.\-]/g, '')
  const n = parseFloat(s)
  return Number.isFinite(n) ? Number(n.toFixed(2)) : 0
}

/* ========= CTA (кнопка) — текст / класи / доступність ========= */
const i18n = {
  add_to_cart:      t('add_to_cart') || 'Додати в кошик',
  select_size:      t('select_size') || 'Оберіть розмір',
  out_of_stock:     t('product.out_of_stock') || 'Немає в наявності',
  added_short:      t('added_short') || 'Додано!',
}

const ctaText = computed(() => {
  if (isAdding.value)   return i18n.add_to_cart
  if (justAdded.value)  return i18n.added_short
  if (isOutOfStock.value) return i18n.out_of_stock
  if (needsSize.value)  return i18n.select_size
  return i18n.add_to_cart
})

const ctaClass = computed(() => {
  if (justAdded.value)     return 'btn-success'
  if (isOutOfStock.value)  return 'btn-secondary'
  // loading і needsSize залишаються primary
  return 'btn-primary'
})

const ctaDisabled = computed(() => isOutOfStock.value || isAdding.value)

const ctaTitle = computed(() => {
  if (isOutOfStock.value) return i18n.out_of_stock
  if (needsSize.value)    return i18n.select_size
  return i18n.add_to_cart
})

/* ---- клік по кнопці ---- */
const onCtaClick = () => {
  if (isOutOfStock.value || isAdding.value) return

  // Якщо треба вибрати розмір — підсвітимо select і дамо тост, але не блокуємо кнопку назавжди
  if (needsSize.value) {
    window.showGlobalToast?.(i18n.select_size, 'warning')
    sizeEl?.classList.add('is-invalid'); sizeEl?.focus()
    return
  }
  sizeEl?.classList.remove('is-invalid')

  // Інакше — додаємо
  void addToCart()
}

/* ---- додати до кошика + трекінг ---- */
const addToCart = async () => {
  try {
    isAdding.value = true

    // Якщо є варіанти — matchedVariant обов’язковий
    const variant = matchedVariant.value ?? (variants.value.length ? null : {})
    if (variants.value.length && !variant) {
      window.showGlobalToast?.('Обраний розмір недоступний', 'danger')
      return
    }

    // Перевірити склад і підрізати quantity, якщо треба
    const stock = parseInt(variant?.quantity ?? availableQty.value ?? 0) || 0
    if (stock > 0 && quantity.value > stock) {
      quantity.value = stock
      window.showGlobalToast?.(`На складі лише ${stock} шт.`, 'warning')
    }

    // Ціна
    const rawPrice   = (variant && 'price_override' in variant) ? variant.price_override : props.product.price
    const finalPrice = toNum(rawPrice)

    // Назва
    const productName =
      props.product?.translations?.find(ti => ti.locale === locale.value)?.name ||
      props.product?.translations?.find(ti => ti.locale === 'uk')?.name ||
      props.product?.translations?.[0]?.name ||
      props.product?.name || ''

    const currency = window.metaPixelCurrency || 'UAH'

    // Додати у кошик (Pinia)
    await cart.addToCart({
      id: variant?.id ?? props.product.id,
      product_id: props.product.id,
      variant_sku: variant?.variant_sku ?? null,
      name: productName,
      price: finalPrice,
      image: props.product.images?.[0]?.full_url || props.product.images?.[0]?.url || '',
      quantity: quantity.value,
      link: props.product.url,
      size: variant?.size ?? '',
      color: variant?.color ?? '',
    })

    // UI
    emit('added', productName)
    window.showGlobalToast?.('🛒  Товар додано в кошик', 'info')
    const cartEl = document.getElementById('shoppingCart')
    if (cartEl && window.bootstrap?.Offcanvas) new bootstrap.Offcanvas(cartEl).show()

    // Трекінг: тільки variant_sku
    const vSku = (variant?.variant_sku ?? '').toString().trim()
    if (vSku && typeof window.mpTrackATC === 'function') {
      window.mpTrackATC({
        variant_sku: vSku,
        price: finalPrice,
        quantity: quantity.value,
        name: productName,
        currency
      })
    }

    // Стан "Додано!" на короткий час
    justAdded.value = true
    setTimeout(() => { justAdded.value = false }, 1200)
  } finally {
    isAdding.value = false
  }
}
</script>
