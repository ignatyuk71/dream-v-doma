<template>
  <button
    type="button"
    class="btn btn-lg fw-bold text-white w-100"
    style="background:#ff4365; border-radius:4px; font-size:1.1rem; padding:10px 0; min-width:110px;"
    @click="addToCart"
  >
    {{ $t('add_to_cart') }}
  </button>

  <!-- DEBUG-маркер (тимчасово). Побачиш червоний текст — значить цей компонент підключений -->
  <p style="color:red; margin-top:6px;">DEBUG BUTTON</p>
</template>

<script setup>
import { onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useCartStore } from '@/stores/cart'

const props = defineProps({
  product: { type: Object, required: true }
})

const cart = useCartStore()
const { locale } = useI18n()

const MP = '[MetaPixel]'

console.log(MP, 'component loaded', {
  ts: new Date().toISOString(),
  productId: props.product?.id,
  sku: props.product?.sku
})

onMounted(() => {
  console.log(MP, 'mounted; fbq?', typeof window.fbq, 'currency', window.metaPixelCurrency)
})

const addToCart = () => {
  console.log(MP, 'click', {
    id: props.product?.id,
    sku: props.product?.sku,
    rawPrice: props.product?.price
  })

  // === Перевірка вибору розміру ===
  const sizeSelect = document.querySelector('select[name="size"]')
  const selectedSize = sizeSelect?.value || ''
  if (props.product.variants?.length > 0 && !selectedSize) {
    console.warn(MP, 'size is required but not selected')
    window.showGlobalToast?.('Будь ласка, виберіть розмір!', 'warning')
    if (sizeSelect) {
      sizeSelect.classList.add('is-invalid')
      sizeSelect.focus()
      sizeSelect.scrollIntoView({ behavior: 'smooth', block: 'center' })
    }
    return
  }
  if (sizeSelect && sizeSelect.classList.contains('is-invalid')) {
    sizeSelect.classList.remove('is-invalid')
  }

  // === Додаємо товар у кошик ===
  cart.addToCart({
    id: props.product.id, // variant.id
    name: props.product.translations?.find(t => t.locale === locale.value)?.name || props.product.name,
    price: props.product.price,
    image: props.product.images?.[0]?.full_url || '',
    quantity: 1,
    link: `/${locale.value}/product/${props.product.slug}`,
    size: selectedSize,
    // sku можна додати, якщо є: sku: props.product.sku
  })
  console.log(MP, 'added to local cart — store will send AddToCart via fbq()')
  
  // Тост про успіх
  window.showGlobalToast?.('🛒  Товар додано в кошик', 'info')

  // Відкрити офканвас корзини (якщо є)
  const cartEl = document.getElementById('shoppingCart')
  if (cartEl && window.bootstrap?.Offcanvas) {
    const bsOffcanvas = new window.bootstrap.Offcanvas(cartEl)
    bsOffcanvas.show()
  }
}
</script>
