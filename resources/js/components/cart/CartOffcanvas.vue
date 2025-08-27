<template>
  <!-- + data-bs-scroll, щоб не клинив скрол і не залипав body на мобілці -->
  <div
    class="offcanvas offcanvas-end pb-sm-2 px-sm-2"
    id="shoppingCart"
    tabindex="-1"
    aria-labelledby="shoppingCartLabel"
    data-bs-scroll="true"
    style="width: 500px"
  >
    <!-- Header -->
    <div class="offcanvas-header flex-column align-items-start py-3 pt-lg-4">
      <div class="d-flex align-items-center justify-content-between w-100 mb-3 mb-lg-4">
        <h4 class="offcanvas-title" id="shoppingCartLabel">{{ $t('shopping_cart') }}</h4>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>

      <div v-if="remainingAmount === 0" class="alert alert-success w-100 py-2 px-3 fs-sm d-flex align-items-center gap-2 mb-3 rounded-2">
        <i class="ci-check-circle fs-lg text-success"></i>
        <span>Вітаємо! Ваше замовлення відповідає умовам безкоштовної доставки.</span>
      </div>

      <div v-else class="w-100">
        <p class="fs-sm mb-2">
          {{ $t('buy_more_to_free_shipping') }}
          <span class="text-dark-emphasis fw-semibold">{{ remainingAmount }} грн</span>
        </p>

        <!-- Смуга прогресу -->
        <div class="progress mb-3" role="progressbar"
             aria-label="Free shipping progress"
             :aria-valuenow="Math.round(progress)"
             aria-valuemin="0"
             aria-valuemax="100">
          <div class="progress-bar fw-medium rounded-pill"
               :style="{ width: Math.round(progress) + '%' }">
            {{ Math.round(progress) }}%
          </div>
        </div>
      </div>
    </div>

    <!-- Items -->
    <div class="offcanvas-body d-flex flex-column gap-4 pt-2">
      <div v-if="items.length === 0" class="text-center text-muted">{{ $t('cart_empty') }}</div>

      <div v-for="item in items" :key="item.id" class="d-flex align-items-center">
        <img
          :src="publicUrl(item.image)"
          alt="Thumbnail"
          class="rounded shadow-sm"
          style="width:110px;height:110px;object-fit:cover;object-position:center;flex-shrink:0;"
        />

        <div class="w-100 min-w-0 ps-3">
          <h5 class="d-flex animate-underline mb-1">
            <a class="d-block fs-sm fw-medium text-truncate animate-target" :href="item.link">{{ item.name }}</a>
          </h5>

          <!-- Бейджики -->
          <div class="d-flex gap-2 mb-2">
            <span v-if="item.size" class="badge bg-light text-body border">Розмір: {{ item.size }}</span>
            <span v-if="item.color" class="badge bg-light text-body border">Колір: {{ item.color }}</span>
          </div>

          <!-- Ціна + кількість в один рядок -->
          <div class="d-flex align-items-center justify-content-between">
            <div class="h6 mb-0">{{ item.price }} грн</div>

            <div class="d-flex align-items-center">
              <div class="count-input rounded-2 me-2">
                <!-- простіше і надійніше: .prevent.stop -->
                <button
                  type="button"
                  class="btn btn-icon btn-sm"
                  data-decrement
                  aria-label="Decrement quantity"
                  @click.prevent.stop="decrement(item.id)"
                >
                  <i class="ci-minus"></i>
                </button>

                <input
                  type="number"
                  class="form-control form-control-sm"
                  :value="item.quantity"
                  min="1"
                  max="5"
                  readonly
                  inputmode="numeric"
                  aria-live="polite"
                />

                <button
                  type="button"
                  class="btn btn-icon btn-sm"
                  data-increment
                  aria-label="Increment quantity"
                  @click.prevent.stop="increment(item.id)"
                >
                  <i class="ci-plus"></i>
                </button>
              </div>

              <button
                type="button"
                class="btn-close fs-sm"
                @click.prevent.stop="removeItem(item.id)"
                :title="$t('remove_from_cart')"
                aria-label="Remove"
              ></button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="offcanvas-header flex-column align-items-start">
      <div class="d-flex align-items-center justify-content-between w-100 mb-3 mb-md-4">
        <span class="text-light-emphasis">{{ $t('subtotal') }}:</span>
        <span class="h6 mb-0">{{ subtotal }} грн</span>
      </div>
      <div class="d-flex gap-2 w-100">
        <button class="btn btn-outline-secondary btn-sm px-3 py-2 w-100" data-bs-dismiss="offcanvas">
          {{ $t('continue_shopping') }}
        </button>
        <a :href="`/${$i18n.locale}/checkout`" class="btn btn-dark btn-sm px-3 py-2 w-100">
          {{ $t('checkout_cart') }}
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useCartStore } from '@/stores/cart'

const cart = useCartStore()
const items = computed(() => cart.items)
const subtotal = computed(() => cart.subtotal)

const freeShippingFrom = 1000
const remainingAmount = computed(() => Math.max(0, freeShippingFrom - subtotal.value))
const progress = computed(() => Math.min(100, (subtotal.value / freeShippingFrom) * 100))

const increment = cart.increment
const decrement = cart.decrement
const removeItem = cart.removeItem

/**
 * JS-аналог Laravel asset(): будуємо абсолютний URL відносно поточного домену.
 */
const asset = (path) => {
  const clean = String(path || '').replace(/^\/+/, '')
  return new URL(clean, window.location.origin + '/').toString()
}

/**
 * publicUrl(path): повторює твою PHP-логіку $toPublicUrl
 * - пусто -> плейсхолдер
 * - абсолютні (http://, https://, //, data:, blob:) -> як є
 * - якщо вже storage/... -> asset('storage/...')
 * - прибираємо префікси public/ або app/public/
 * - інакше повертаємо як asset('storage/' + path)
 */
const publicUrl = (path) => {
  if (!path) {
    return asset('assets/img/placeholder.svg')
  }

  let p = String(path).trim()

  // абсолютні або спеціальні схеми (//, data:, blob:)
  if (/^(https?:)?\/\//i.test(p) || /^(data|blob):/i.test(p)) {
    return p
  }

  // знімаємо початкові слеші
  p = p.replace(/^\/+/, '')

  if (p.startsWith('storage/')) {
    return asset(p)
  }

  // прибрати "public/" або "app/public/"
  p = p.replace(/^(?:app\/)?public\//, '')

  return asset('storage/' + p)
}
</script>

<style scoped>
/* На вузьких екранах робимо офканвас на всю ширину */
@media (max-width: 575.98px) {
  .offcanvas.offcanvas-end[style] {
    width: 100% !important;
  }
}
</style>
