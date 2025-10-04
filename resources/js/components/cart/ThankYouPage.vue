<template>
  <div class="container-lg">
    <main class="content-wrapper" v-if="order">
      <div class="row row-cols-1 row-cols-lg-2 g-0 mx-auto" style="max-width: 1920px">

        <!-- Ліва колонка: інформація про замовлення -->
        <div class="col d-flex flex-column justify-content-center py-5 px-xl-4 px-xxl-5">
          <div
            class="w-100 pt-sm-2 pt-md-3 pt-lg-4 pb-lg-4 pb-xl-5 px-3 px-sm-4 pe-lg-0 ps-lg-5 mx-auto ms-lg-auto me-lg-4"
            style="max-width: 740px"
          >
            <!-- Заголовок -->
            <div class="d-flex align-items-sm-center border-bottom pb-4 pb-md-5">
              <div
                class="d-flex align-items-center justify-content-center bg-success text-white rounded-circle flex-shrink-0"
                style="width: 3rem; height: 3rem; margin-top: -.125rem"
              >
                <i class="ci-check fs-4"></i>
              </div>
              <div class="w-100 ps-3">
                <div class="fs-sm mb-1">Замовлення №{{ order.order_number }}</div>
                <h1 class="h4 mb-0">Дякуємо за ваше замовлення!</h1>
                <p class="text-muted mb-0">
                  Наш менеджер звʼяжеться з вами найближчим часом, щоб уточнити деталі замовлення.
                </p>
              </div>
            </div>

            <!-- Інфо клієнта / доставки -->
            <div class="d-flex flex-column gap-4 pt-3 pb-5 mt-3">
              <div class="d-flex flex-wrap gap-4 mb-4">
                <div>
                  <h6 class="h5 text-muted mb-1">Ім’я</h6>
                  <div class="fw-semibold">{{ order.customer?.name }}</div>
                </div>
                <div>
                  <h6 class="h5 text-muted mb-1">Телефон</h6>
                  <div class="fw-semibold">{{ order.customer?.phone }}</div>
                </div>
              </div>

              <div v-if="order.delivery">
                <h3 class="h5 mb-3">Доставка</h3>
                <p class="mb-0">
                  <template v-if="order.delivery.address && order.delivery.address !== '—'">
                    <span class="fw-semibold fs-6">{{ order.delivery.address }}</span><br>
                  </template>
                  <small class="text-muted">
                    {{ order.delivery.name && order.delivery.name !== '—' ? order.delivery.name : 'Кур’єрська доставка' }}
                  </small>
                </p>
              </div>

              <div>
                <h6 class="h5 text-muted mb-1">Очікувана доставка</h6>
                <div class="fs-sm">протягом 1–2 робочих днів</div>
              </div>
            </div>

            <!-- 
            <div class="bg-success rounded px-4 py-4" style="--cz-bg-opacity: .2">
              <div class="py-3">
                <h2 class="h5 text-center pb-2 mb-1">🎉 Вітаємо! Знижка 30% на наступну покупку!</h2>
                <p class="fs-sm text-center mb-4">Використайте цей купон зараз або знайдете його у своєму кабінеті.</p>
                <div class="d-flex gap-2 mx-auto" style="max-width: 500px">
                  <input type="text" class="form-control border-white border-opacity-10 w-100" id="couponCode" value="30%SALEOFF" readonly>
                  <button type="button" class="btn btn-dark" data-copy-text-from="#couponCode">Скопіювати</button>
                </div>
              </div>
            </div>Купон -->

            <p class="fs-sm pt-4 pt-md-5 mt-2 mt-sm-3 mt-md-0 mb-0">
              Потрібна допомога?
              <a class="fw-medium ms-2" :href="`/${$i18n.locale}/contact`">Звʼяжіться з нами</a>
            </p>
          </div>
        </div>

        <!-- Права колонка: склад замовлення -->
        <div class="col-12 col-lg-6 px-3 px-sm-4 px-xl-5 py-5">
          <div class="bg-white border rounded-4 p-3 p-md-4 shadow-sm mx-auto" style="max-width: 636px">
            <h5 class="fw-bold mb-4">Ваше замовлення</h5>

            <div
              v-for="item in order.items"
              :key="item.id"
              class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom"
            >
              <!-- Фото + деталі -->
              <div class="d-flex align-items-center">
                <img
                  :src="withStorage(item.image_url || item.product_image)"
                  @error="$event.target.src = '/assets/img/placeholder.jpg'"
                  class="rounded me-3"
                  style="width: 74px; height: 74px; object-fit: cover;"
                  alt="Фото товару"
                />
                <div>
                  <div class="fw-medium text-truncate" style="max-width: 360px">
                    {{ item.product_name }}
                  </div>

                  <!-- Розмір / Колір -->
                  <div class="small text-muted mt-1" v-if="item.size">
                    {{ $t('checkout.order.size') || 'Розмір' }}:
                    <span class="fw-bold text-body">{{ item.size }}</span>
                  </div>
                  <div class="small text-muted mt-1" v-if="item.color">
                    {{ $t('product.color') || 'Колір' }}:
                    <span class="fw-bold text-body">{{ item.color }}</span>
                  </div>

                  <!-- Кількість і ціна за одиницю -->
                  <div class="small mt-1">
                    <span class="text-muted">× {{ item.quantity }}</span>
                    <span class="ms-1">•</span>
                    <span class="fw-semibold ms-1">{{ money(item.price) }}</span>
                  </div>
                </div>
              </div>

              <!-- Сума по позиції -->
              <div class="fw-bold text-end" style="white-space: nowrap;">
                {{ money(lineTotal(item)) }}
              </div>
            </div>
          </div>
        </div>

      </div>
    </main>

    <div v-else class="container text-center py-5">
      <div class="spinner-border text-primary mb-3" role="status"></div>
      <p>Loading your order...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useI18n } from 'vue-i18n'
import { useCartStore } from '@/stores/cart'

const { t } = useI18n()
const cart = useCartStore()
const order = ref(null)

/* ---------- helpers (форматування чисел/валюти, підрахунок рядка, шлях до зображення) ---------- */
const toNumber = (v) => {
  if (typeof v === 'number') return v
  const n = parseFloat(String(v).replace(',', '.').replace(/[^\d.]/g, ''))
  return Number.isFinite(n) ? n : 0
}
const money     = (v) => `${toNumber(v).toFixed(2)} ${t('currency') || 'грн'}`
const lineTotal = (item) => toNumber(item.price) * toNumber(item.quantity)

const withStorage = (path) => {
  if (!path) return '/assets/img/placeholder.jpg'
  if (/^https?:\/\//i.test(path) || String(path).startsWith('//')) {
    try { path = new URL(path, window.location.origin).pathname } catch (_) {}
  }
  let p = String(path).replace(/^\/+/, '').replace(/^(?:app\/)?public\//, '')
  if (p.startsWith('storage/')) return '/' + p
  return '/storage/' + p
}

/* ---------- splitFullName: ділить повне ім'я на first/last (останнє слово — прізвище) ---------- */
function splitFullName(str = '') {
  const parts = String(str).trim().split(/\s+/).filter(Boolean)
  if (parts.length === 0) return { first_name: null, last_name: null }
  if (parts.length === 1)  return { first_name: parts[0], last_name: null }
  return { first_name: parts.slice(0, -1).join(' '), last_name: parts.at(-1) }
}

/* ================= Meta Pixel / GA4: Purchase ================= */
/**
 * trackPurchaseOnce: формує payload з замовлення та:
 *  - пушить подію purchase у GA4 (dataLayer)
 *  - викликає window.mpTrackPurchase(payload) для Meta Pixel/CAPI (з ретраями)
 * Подія відправляється один раз на сторінку.
 */
const trackPurchaseOnce = (() => {
  let sent = false
  return (ord) => {
    try {
      if (sent) return
      if (!ord || !Array.isArray(ord.items)) return
      if (window._mpFlags && window._mpFlags.purchase === false) return

      // Фільтруємо товари без sku
      const rawItems = ord.items || []
      const withSku  = rawItems.filter(i => String(i?.variant_sku ?? '').trim().length > 0)
      if (!withSku.length) return

      // Нормалізовані items для евентів
      const items = withSku.map(i => ({
        variant_sku: String(i.variant_sku),
        price: toNumber(i.price),
        quantity: Number(i.quantity || 1),
        name: i.product_name || i.name || ''
      }))

      // Витягуємо PII: якщо last_name порожній, а first_name містить пробіли — ділимо
      const customer = ord.customer || {}
      let first_name = (customer.first_name ?? '').trim() || null
      let last_name  = (customer.last_name  ?? '').trim()  || null

      if (!last_name && first_name && /\s/.test(first_name)) {
        const split = splitFullName(first_name)
        first_name = split.first_name
        last_name  = split.last_name
      }
      // Fallback: якщо окремих полів нема — ділимо повне name
      if ((!first_name || !last_name) && customer.name) {
        const fromFull = splitFullName(customer.name)
        first_name = first_name || fromFull.first_name
        last_name  = last_name  || fromFull.last_name
      }

      // Підсумки/валюта
      const computedItemsSum = items.reduce((s, x) => s + x.price * x.quantity, 0)
      const payload = {
        order_number: ord.order_number || ord.id || undefined,
        items,
        value: toNumber(
          (ord.total ?? ord.total_price ?? ord.total_amount ?? computedItemsSum)
        ),
        currency: ord.currency || window.metaPixelCurrency || 'UAH',
        shipping: toNumber(ord.shipping_cost ?? ord.delivery_cost ?? 0),
        tax: toNumber(ord.tax ?? 0),

        // PII → тільки у CAPI (Pixel не отримує PII)
        email: customer.email || null,
        phone: customer.phone || null,
        first_name,
        last_name,
        external_id: customer.id ? String(customer.id) : null
      }

      /* --- GA4 purchase (dataLayer) --- */
      try {
        const gaItems = items.map(i => ({
          item_id:   i.variant_sku,
          item_name: i.name || '',
          price:     i.price,
          quantity:  i.quantity
        }))
        const tx = String(payload.order_number || '')
        const gaGuard = tx ? 'ga4_purchase_' + tx : null
        if (!gaGuard || !localStorage.getItem(gaGuard)) {
          window.dataLayer = window.dataLayer || []
          window.dataLayer.push({
            event: 'purchase',
            ecommerce: {
              transaction_id: tx,
              value: payload.value,
              currency: payload.currency,
              shipping: payload.shipping,
              tax: payload.tax,
              items: gaItems
            }
          })
          if (gaGuard) localStorage.setItem(gaGuard, '1')
        }
      } catch (_) {}

      /* --- Meta Pixel / CAPI: викликаємо глобалку з ретраями --- */
      const tryCall = (attempt = 0) => {
        const exists = typeof window.mpTrackPurchase === 'function'
        if (exists) {
          window.mpTrackPurchase(payload)
          sent = true
        } else if (attempt < 120) {
          setTimeout(() => tryCall(attempt + 1), 80) // ~9.6s загалом
        }
      }
      tryCall()
    } catch (_) {}
  }
})()
/* ================================================================= */

/* ---------- onMounted: тягнемо замовлення та запускаємо purchase ---------- */
onMounted(async () => {
  const orderNumber = localStorage.getItem('lastOrderNumber')
  if (!orderNumber) {
    window.location.href = '/'
    return
  }

  try {
    const { data } = await axios.get(`/api/orders/${orderNumber}`)
    order.value = data

    // Відправляємо Purchase один раз
    trackPurchaseOnce(order.value)

    // Очистка локальних даних після успішного отримання
    localStorage.removeItem('lastOrderNumber')
    localStorage.removeItem('cart')
    localStorage.removeItem('thankyou')
    sessionStorage.removeItem('checkout')
    sessionStorage.clear()
    cart.clearCart?.()
  } catch (_) {}
})
</script>






