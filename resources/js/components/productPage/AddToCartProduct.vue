<template>
  <div class="d-flex flex-wrap gap-2 mb-4 w-100">
    <!-- Лічильник кількості (1…10 або менше, якщо мало на складі) -->
    <div class="count-input">
      <button
        type="button"
        class="btn btn-icon btn-lg"
        data-decrement
        :disabled="quantity <= 1 || isOutOfStock"
        aria-label="Decrement quantity"
        @click="(e)=>{ e.preventDefault(); e.stopPropagation(); e.stopImmediatePropagation && e.stopImmediatePropagation(); if (quantity > 1 && !isOutOfStock) decrement() }"
        @keydown.enter.prevent.stop="(e)=>{ e.stopImmediatePropagation && e.stopImmediatePropagation(); if (quantity > 1 && !isOutOfStock) decrement() }"
        @keydown.space.prevent.stop="(e)=>{ e.stopImmediatePropagation && e.stopImmediatePropagation(); if (quantity > 1 && !isOutOfStock) decrement() }"
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
        :disabled="quantity >= maxAllowedQty || isOutOfStock"
        aria-label="Increment quantity"
        @click="(e)=>{ e.preventDefault(); e.stopPropagation(); e.stopImmediatePropagation && e.stopImmediatePropagation(); if (quantity < maxAllowedQty && !isOutOfStock) increment() }"
        @keydown.enter.prevent.stop="(e)=>{ e.stopImmediatePropagation && e.stopImmediatePropagation(); if (quantity < maxAllowedQty && !isOutOfStock) increment() }"
        @keydown.space.prevent.stop="(e)=>{ e.stopImmediatePropagation && e.stopImmediatePropagation(); if (quantity < maxAllowedQty && !isOutOfStock) increment() }"
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
      <button
        type="button"
        :class="['btn','btn-lg','w-100','animate-slide-end', isOutOfStock ? 'btn-secondary' : 'btn-primary']"
        :disabled="isOutOfStock"
        @click="addToCart"
      >
        <i class="ci-shopping-cart fs-base animate-target me-2"></i>
        {{ $t('add_to_cart') }}
      </button>
    </div>
  </div>
</template>

<script setup>
/**
 * ✅ Що змінено:
 * - Додаємо реактивний стан наявності (isOutOfStock) за обраним розміром або загальним залишком.
 * - Кнопка "У кошик" стає сірою (btn-secondary) і disabled, якщо кількість = 0.
 * - Плюс/мінус теж блокуються при відсутності стоку; max для лічильника = фактичний залишок (але не більше 10).
 * - При зміні розміру автоматично перераховується доступна кількість; quantity підрізається до доступної.
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
const quantity = ref(1)

// Розмір із зовнішнього <select name="size"> (реактивно підписуємось)
const selectedSize = ref('')
let sizeEl = null

/* ---- джерело варіантів:
 * 1) якщо у пропсах прийшли variants — беремо їх;
 * 2) інакше спробуємо window.productVariants (фолбек із Blade).
 */
const variants = computed(() => {
  if (Array.isArray(props.product?.variants)) return props.product.variants
  if (Array.isArray(window.productVariants)) return window.productVariants
  return []
})

/* ---- агрегована кількість по всіх варіантах ---- */
const variantsTotal = computed(() =>
  variants.value.reduce((acc, v) => acc + (parseInt(v?.quantity ?? 0) || 0), 0)
)

/* ---- отримати вибраний розмір із DOM ---- */
const readSelectedSize = () => {
  const el = document.querySelector('select[name="size"]')
  sizeEl = el
  selectedSize.value = el?.value?.toString() ?? ''
}

/* ---- найти варіант за вибраним розміром ---- */
const matchedVariant = computed(() => {
  const sz = selectedSize.value
  if (!sz) return null
  return variants.value.find(v => (v?.size ?? '') === sz) || null
})

/* ---- скільки доступно зараз (пріоритет: конкретний варіант → сума варіантів → stock_total) ---- */
const availableQty = computed(() => {
  if (variants.value.length) {
    if (matchedVariant.value) return parseInt(matchedVariant.value.quantity ?? 0) || 0
    // якщо розмір не обрано — показуємо доступність за сумою, аби заблокувати кнопку коли все по нулях
    return variantsTotal.value
  }
  return parseInt(props.product?.stock_total ?? props.product?.quantity_in_stock ?? 0) || 0
})

/* ---- прапор: немає в наявності ---- */
const isOutOfStock = computed(() => (availableQty.value || 0) <= 0)

/* ---- максимально дозволена кількість для лічильника ---- */
const maxAllowedQty = computed(() => {
  // якщо є залишок — обмежуємось мін(10, залишок), інакше 10 (але кнопка буде disabled)
  const cap = availableQty.value > 0 ? availableQty.value : 10
  return Math.min(10, cap)
})

/* ---- утиліти ---- */
const toNum = (v) => {
  const s = String(v ?? '').replace(',', '.').replace(/[^\d.\-]/g, '')
  const n = parseFloat(s)
  return Number.isFinite(n) ? Number(n.toFixed(2)) : 0
}

// Керування кількістю
const increment = () => { if (!isOutOfStock.value && quantity.value < maxAllowedQty.value) quantity.value++ }
const decrement = () => { if (!isOutOfStock.value && quantity.value > 1) quantity.value-- }

/* ---- синхронізуємо selectedSize з DOM ---- */
onMounted(() => {
  readSelectedSize()
  if (sizeEl) sizeEl.addEventListener('change', readSelectedSize, { passive: true })
})
onBeforeUnmount(() => {
  if (sizeEl) sizeEl.removeEventListener('change', readSelectedSize)
})

/* ---- якщо доступний залишок зменшився — підрізаємо quantity ---- */
watch(availableQty, (qty) => {
  if (qty <= 0) {
    quantity.value = 1
  } else if (quantity.value > qty) {
    quantity.value = Math.max(1, qty)
  }
})

/* ---- допоміжні ф-ції ---- */
const getMatchedVariant = (size) => {
  if (!size) return null
  return variants.value.find(v => (v?.size ?? '') === size) || null
}

/* ---- головна дія: додати до кошика + трекінг ---- */
const addToCart = async () => {
  // 0) якщо взагалі немає залишків — просто попереджаємо
  if (isOutOfStock.value) {
    window.showGlobalToast?.(t('product.out_of_stock') || 'Немає в наявності', 'warning')
    return
  }

  // 1) Переконаймось, що обрано розмір (коли є варіанти)
  const selected = selectedSize.value
  if (variants.value.length && !selected) {
    window.showGlobalToast?.('Будь ласка, виберіть розмір!', 'warning')
    sizeEl?.classList.add('is-invalid'); sizeEl?.focus()
    return
  }
  sizeEl?.classList.remove('is-invalid')

  // 2) Знайти відповідний варіант
  const variant = getMatchedVariant(selected) ?? (variants.value.length ? null : {})
  if (variants.value.length && !variant) {
    window.showGlobalToast?.('Обраний розмір недоступний', 'danger')
    return
  }

  // 2.1) Перевірити склад і підрізати quantity, якщо треба
  const stock = parseInt(variant?.quantity ?? availableQty.value ?? 0) || 0
  if (stock > 0 && quantity.value > stock) {
    quantity.value = stock
    window.showGlobalToast?.(`На складі лише ${stock} шт.`, 'warning')
  }

  // 3) Зібрати дані позиції (ціна: override або базова ціна продукту)
  const rawPrice   = (variant && 'price_override' in variant) ? variant.price_override : props.product.price
  const finalPrice = toNum(rawPrice)

  // Локалізована назва товару
  const productName =
    props.product?.translations?.find(ti => ti.locale === locale.value)?.name ||
    props.product?.translations?.find(ti => ti.locale === 'uk')?.name ||
    props.product?.translations?.[0]?.name ||
    props.product?.name || ''

  const currency = window.metaPixelCurrency || 'UAH'

  // 4) Додати у кошик (Pinia store)
  await cart.addToCart({
    id: variant?.id ?? props.product.id,          // якщо без варіантів — fallback id продукту
    product_id: props.product.id,
    variant_sku: variant?.variant_sku ?? null,    // збережемо для відображення та трекінгу
    name: productName,
    price: finalPrice,
    image: props.product.images?.[0]?.full_url || props.product.images?.[0]?.url || '',
    quantity: quantity.value,
    link: props.product.url,
    size: variant?.size ?? '',
    color: variant?.color ?? '',
  })

  // 5) UI
  emit('added', productName)
  window.showGlobalToast?.('🛒  Товар додано в кошик', 'info')
  const cartEl = document.getElementById('shoppingCart')
  if (cartEl && window.bootstrap?.Offcanvas) new bootstrap.Offcanvas(cartEl).show()

  // 6) Трекінг AddToCart: відправляємо ЧИСТО variant_sku як content_id (якщо є)
  const vSku = (variant?.variant_sku ?? '').toString().trim()
  if (!vSku) {
    // Якщо без варіантів або sku відсутній — пропускаємо трек AddToCart
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
