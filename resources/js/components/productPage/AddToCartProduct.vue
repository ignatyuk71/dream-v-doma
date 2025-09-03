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
/**
 * Компонент "Кнопка додати в кошик":
 * - Читає вибраний розмір із <select name="size"> (верстка поза компонентом).
 * - Знаходить відповідний варіант із переданих product.variants (або window.productVariants).
 * - Додає позицію в кошик (Pinia store).
 * - Відправляє трекінг AddToCart ЧЕРЕЗ глобалку window.mpTrackATC,
 *   причому content_id = ТІЛЬКИ variant_sku (жодних id/sku продукту).
 */

import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useCartStore } from '@/stores/cart'

/* ---- вхідні дані та сервіси ---- */
const emit  = defineEmits(['added'])
const props = defineProps({ product: { type: Object, required: true } })
const { locale } = useI18n()
const cart = useCartStore()

/* ---- локальний стан ---- */
const quantity = ref(1)

/* ---- джерело варіантів:
 * 1) якщо у пропсах прийшли variants — беремо їх;
 * 2) інакше спробуємо window.productVariants (фолбек із Blade).
 */
const variants = computed(() => {
  if (Array.isArray(props.product?.variants)) return props.product.variants
  if (Array.isArray(window.productVariants)) return window.productVariants
  return []
})

/* ---- утиліти ---- */
// Безпечне приведення ціни до числа з 2 знаками
const toNum = (v) => {
  const s = String(v ?? '').replace(',', '.').replace(/[^\d.\-]/g, '')
  const n = parseFloat(s)
  return Number.isFinite(n) ? Number(n.toFixed(2)) : 0
}

// Керування кількістю
const increment = () => { if (quantity.value < 10) quantity.value++ }
const decrement = () => { if (quantity.value > 1) quantity.value-- }

// Зчитати вибраний розмір із селекта поза компонентом
const getSelectedSize = () => {
  const el = document.querySelector('select[name="size"]')
  return el?.value?.toString() ?? ''
}

// Знайти варіант за розміром (легко розширити, якщо додасте фільтр за кольором)
const getMatchedVariant = (size) => {
  if (!size) return null
  return variants.value.find(v => (v?.size ?? '') === size) || null
}

/* ---- головна дія: додати до кошика + трекінг ---- */
const addToCart = async () => {
  // 1) Переконаймось, що обрано розмір
  const sizeSelect = document.querySelector('select[name="size"]')
  const selectedSize = getSelectedSize()
  if (!selectedSize) {
    window.showGlobalToast?.('Будь ласка, виберіть розмір!', 'warning')
    sizeSelect?.classList.add('is-invalid'); sizeSelect?.focus()
    return
  }
  sizeSelect?.classList.remove('is-invalid')

  // 2) Знайти відповідний варіант
  const matchedVariant = getMatchedVariant(selectedSize)
  if (!matchedVariant) {
    window.showGlobalToast?.('Обраний розмір недоступний', 'danger')
    return
  }

  // 2.1) Перевірити склад (якщо бекенд повертає quantity по варіанту)
  const stock = Number(matchedVariant.quantity ?? 0)
  if (stock > 0 && quantity.value > stock) {
    quantity.value = stock
    window.showGlobalToast?.(`На складі лише ${stock} шт.`, 'warning')
  }

  // 3) Зібрати дані позиції (ціна: override або базова ціна продукту)
  const rawPrice   = matchedVariant.price_override ?? props.product.price
  const finalPrice = toNum(rawPrice)

  // Локалізована назва товару
  const productName =
    props.product?.translations?.find(t => t.locale === locale.value)?.name ||
    props.product?.translations?.find(t => t.locale === 'uk')?.name ||
    props.product?.translations?.[0]?.name ||
    props.product?.name || ''

  const currency = window.metaPixelCurrency || 'UAH'

  // 4) Додати у кошик (Pinia store)
  await cart.addToCart({
    id: matchedVariant.id,
    product_id: props.product.id,
    variant_sku: matchedVariant.variant_sku ?? null, // збережемо для відображення та трекінгу
    name: productName,
    price: finalPrice,
    image: props.product.images?.[0]?.full_url || props.product.images?.[0]?.url || '',
    quantity: quantity.value,
    link: props.product.url,
    size: matchedVariant.size,
    color: matchedVariant.color ?? '',
  })

  // 5) UI: тост + відкрити офканвас кошика (якщо доступний)
  emit('added', productName)
  window.showGlobalToast?.('🛒  Товар додано в кошик', 'info')
  const cartEl = document.getElementById('shoppingCart')
  if (cartEl && window.bootstrap?.Offcanvas) new bootstrap.Offcanvas(cartEl).show()

  // 6) Трекінг AddToCart:
  //    відправляємо ЧИСТО variant_sku як content_id.
  const vSku = (matchedVariant.variant_sku ?? '').toString().trim()
  if (!vSku) {
    // Якщо не згенерувався або не прийшов — не шлемо помилкові ідентифікатори.
    window.showGlobalToast?.('⚠️ Відсутній артикул варіанта (variant_sku). Подія трекінгу пропущена.', 'warning')
    return
  }

  // Викликаємо глобалку з Blade-паршалу (вона дублює подію у Pixel+CAPI з єдиним event_id)
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
