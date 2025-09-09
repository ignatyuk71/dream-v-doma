<template>
  <button
    type="button"
    class="btn btn-lg btn-primary w-100 animate-slide-end"
    @click="goToOptions"
  >
    <i class="ci-shopping-cart fs-base animate-target me-2"></i>
    {{ $t('add_to_cart') }}
  </button>
</template>

<script setup>
import { onMounted } from 'vue'

/**
 * Налаштовувані селектори:
 * - optionsSelector — контейнер блоку з опціями/кнопкою (куди скролимо)
 * - sizeSelector    — селект розміру (кому ставимо фокус і підсвічення)
 */
const props = defineProps({
  optionsSelector: { type: String, default: '#product-options' },
  sizeSelector:    { type: String, default: 'select[name="size"]' }
})

let optionsEl = null
let sizeEl = null

onMounted(() => {
  optionsEl = document.querySelector(props.optionsSelector) || null
  sizeEl    = document.querySelector(props.sizeSelector) || null
})

const goToOptions = () => {
  // 1) Скрол до блоку з опціями (або до селекта, якщо його знайдено)
  const target = sizeEl || optionsEl
  if (target?.scrollIntoView) {
    target.scrollIntoView({ behavior: 'smooth', block: 'center' })
  } else {
    // fallback — на самий верх сторінки
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }

  // 2) Фокус на селект розміру, легке підсвічення
  if (sizeEl) {
    sizeEl.focus({ preventScroll: true })
    sizeEl.classList.add('flash-outline')
    // якщо була раніше помилка валідації — приберемо
    sizeEl.classList.remove('is-invalid')
    setTimeout(() => sizeEl.classList.remove('flash-outline'), 1500)
  }
}
</script>

<style scoped>
/* м’яке підсвічення селекта після скролу */
.flash-outline {
  outline: 3px solid rgba(99, 102, 241, 0.35); /* індиго */
  transition: outline-color .4s ease;
}
</style>
