<template>
  <button
    type="button"
    class="btn btn-lg fw-bold text-white w-100"
    style="background:#ff4365; border-radius:4px; font-size:1.1rem; padding:10px 0; min-width:110px;"
    @click="addToCart"
  >
    {{ $t('add_to_cart') }}
  </button>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import { useCartStore } from '@/stores/cart'

const props = defineProps({
  product: { type: Object, required: true }
})

const cart = useCartStore()
const { locale } = useI18n()

// простий хелпер для URL картинки
const normalizeUrl = (url) => {
  if (!url) return '/assets/img/placeholder.svg'
  if (url.startsWith('http')) return url
  let p = url.startsWith('/') ? url : '/' + url
  if (!p.startsWith('/storage/') && p.startsWith('/products/')) {
    p = '/storage' + p
  }
  return p
}

const addToCart = () => {
  const sizeSelect = document.querySelector('select[name="size"]')
  const selectedSize = sizeSelect?.value || ''

  if (props.product.variants?.length > 0 && !selectedSize) {
    window.showGlobalToast('Будь ласка, виберіть розмір!', 'warning')
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

  cart.addToCart({
    id: props.product.id,
    name: props.product.translations.find(t => t.locale === locale.value)?.name || props.product.name,
    price: props.product.price,
    image: normalizeUrl(props.product.images?.[0]?.full_url),
    quantity: 1,
    link: `/${locale.value}/product/${props.product.slug}`,
    size: selectedSize,
  })

  window.showGlobalToast('🛒  Товар додано в кошик', 'info')

  const cartEl = document.getElementById('shoppingCart')
  if (cartEl) {
    const bsOffcanvas = new bootstrap.Offcanvas(cartEl)
    bsOffcanvas.show()
  }
}
</script>
