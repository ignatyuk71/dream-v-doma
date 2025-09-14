<template>
  <div class="d-flex flex-wrap gap-2 mb-4 w-100">
    <!-- Лічильник кількості (1…10) -->
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

    <!-- У кошик / Немає в наявності -->
    <div class="flex-grow-1">
      <!--
        • Стає сірою і disabled, якщо товару немає (isInStock === false)
        • Текст і іконка змінюються динамічно: “У кошик” ↔ “Немає в наявності”
        • title / aria / tabindex також підлаштовуються
      -->
      <button
        type="button"
        :class="['btn','btn-lg', isInStock ? 'btn-primary' : 'btn-secondary','w-100','animate-slide-end']"
        :disabled="!isInStock"
        :aria-disabled="!isInStock"
        :title="btnLabel"
        :tabindex="isInStock ? 0 : -1"
        @click="addToCart"
      >
        <i :class="['fs-base','animate-target','me-2', btnIcon]"></i>
        {{ btnLabel }}
      </button>
    </div>
  </div>
</template>

<script setup>
/**
 * Кнопка “Додати в кошик” з перевіркою наявності.
 * Логіка наявності:
 *   1) Якщо вибрано розмір і є відповідний варіант — беремо його quantity.
 *   2) Якщо варіант не знайдено, але варіанти існують — сумуємо їх quantity.
 *   3) Якщо варіантів немає — беремо загальний склад: props.product.stock_total або window.productStockTotal.
 * Якщо підсумок = 0 → кнопка сіра, текст “Немає в наявності”, клік блокується.
 */

import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useCartStore } from '@/stores/cart'

/* Події компонента */
const emit  = defineEmits(['added'])

/* Пропси: очікуємо payload з Blade (url, price, translations, variants[], stock_total?) */
const props = defineProps({
  product: { type: Object, required: true }
})

/* Сервіси */
const { t, locale } = useI18n()
const cart = useCartStore()

/* Кількість */
const quantity = ref(1)

/* Варіанти: з props або з window як фолбек */
const variants = computed(() => {
  if (Array.isArray(props.product?.variants)) return props.product.variants
  if (Array.isArray(window.productVariants)) return window.productVariants
  return []
})

/* ===================== УТИЛІТИ ===================== */

// Приведення ціни до числа з 2 знаками
const toNum = (v) => {
  const s = String(v ?? '').replace(',', '.').replace(/[^\d.\-]/g, '')
  const n = parseFloat(s)
  return Number.isFinite(n) ? Number(n.toFixed(2)) : 0
}

// Керування кількістю
const increment = () => { if (quantity.value < 10) quantity.value++ }
const decrement = () => { if (quantity.value > 1) quantity.value-- }

// Зчитати вибраний розмір із зовнішнього <select name="size">
const getSelectedSize = () => {
  const el = document.querySelector('select[name="size"]')
  return el?.value?.toString() ?? ''
}

// Знайти варіант під розмір
const getMatchedVariant = (size) => {
  if (!size) return null
  return variants.value.find(v => (v?.size ?? '') === size) || null
}

/* ===================== НАЯВНІСТЬ ===================== */

const currentStock = ref(0)

/** Перерахунок складу за правилами вище */
const recalcStock = () => {
  const selectedSize = getSelectedSize()
  const matched = getMatchedVariant(selectedSize)

  if (matched) {
    currentStock.value = Number(matched.quantity ?? 0)
    return
  }

  if (variants.value.length) {
    currentStock.value = variants.value.reduce((acc, v) => acc + (parseInt(v?.quantity) || 0), 0)
    return
  }

  const fallback = Number(props.product?.stock_total ?? window.productStockTotal ?? 0)
  currentStock.value = Number.isFinite(fallback) ? fallback : 0
}

/** Є в наявності чи ні — керує станом кнопки та її текстом */
const isInStock = computed(() => (currentStock.value || 0) > 0)

/* Слухач змін зовнішнього селекта, щоб кнопка/текст одразу оновлювались */
let sizeSelectEl = null
onMounted(() => {
  sizeSelectEl = document.querySelector('select[name="size"]') || null
  sizeSelectEl?.addEventListener('change', recalcStock)
  recalcStock() // стартовий розрахунок
})
onUnmounted(() => {
  sizeSelectEl?.removeEventListener('change', recalcStock)
  sizeSelectEl = null
})

/* Текст/іконка кнопки залежно від наявності */
const btnLabel = computed(() => (isInStock.value ? t('add_to_cart') : t('product.out_of_stock')))
const btnIcon  = computed(() => (isInStock.value ? 'ci-shopping-cart' : 'ci-slash'))

/* ===================== ДОДАТИ В КОШИК ===================== */
const addToCart = async () => {
  // Якщо немає — блокуємо
  if (!isInStock.value) {
    window.showGlobalToast?.(t('product.out_of_stock'), 'danger')
    return
  }

  // Має бути обраний розмір
  const sizeSelect = document.querySelector('select[name="size"]')
  const selectedSize = getSelectedSize()
  if (!selectedSize) {
    window.showGlobalToast?.('Будь ласка, виберіть розмір!', 'warning')
    sizeSelect?.classList.add('is-invalid'); sizeSelect?.focus()
    return
  }
  sizeSelect?.classList.remove('is-invalid')

  // Знайти варіант
  const matchedVariant = getMatchedVariant(selectedSize)
  if (!matchedVariant) {
    window.showGlobalToast?.('Обраний розмір недоступний', 'danger')
    recalcStock()
    return
  }

  // Додаткова перевірка складу по варіанту
  const stock = Number(matchedVariant.quantity ?? 0)
  if (stock <= 0) {
    window.showGlobalToast?.(t('product.out_of_stock'), 'danger')
    recalcStock()
    return
  }
  if (quantity.value > stock) {
    quantity.value = stock
    window.showGlobalToast?.(`На складі лише ${stock} шт.`, 'warning')
  }

  // Ціна: override або базова
  const rawPrice   = matchedVariant.price_override ?? props.product.price
  const finalPrice = toNum(rawPrice)

  // Локалізована назва
  const productName =
    props.product?.translations?.find(ti => ti.locale === locale.value)?.name ||
    props.product?.translations?.find(ti => ti.locale === 'uk')?.name ||
    props.product?.translations?.[0]?.name ||
    props.product?.name || ''

  const currency = window.metaPixelCurrency || 'UAH'

  // Додаємо у кошик (Pinia)
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

  // UI
  emit('added', productName)
  window.showGlobalToast?.('🛒  Товар додано в кошик', 'info')
  const cartEl = document.getElementById('shoppingCart')
  if (cartEl && window.bootstrap?.Offcanvas) new bootstrap.Offcanvas(cartEl).show()

  // Трек AddToCart: лише variant_sku як content_id
  const vSku = (matchedVariant.variant_sku ?? '').toString().trim()
  if (!vSku) {
    window.showGlobalToast?.('⚠️ Відсутній артикул варіанта (variant_sku). Подія трекінгу пропущена.', 'warning')
    return
  }
  if (typeof window.mpTrackATC === 'function') {
    window.mpTrackATC({
      variant_sku: vSku,
      price: finalPrice,
      quantity: quantity.value,
      name: productName,
      currency
    })
  }
}
</script>
