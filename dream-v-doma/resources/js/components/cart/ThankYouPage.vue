<template>
  <div class="container-lg">
    <Breadcrumbs :items="breadcrumbs" />
  <main class="content-wrapper" v-if="order">
    <div class="row row-cols-1 row-cols-lg-2 g-0 mx-auto" style="max-width: 1920px">

      <!-- Left column: Order info -->
      <!-- Колонка з інформацією про замовлення -->
    <div class="col d-flex flex-column justify-content-center py-5 px-xl-4 px-xxl-5">
      <div class="w-100 pt-sm-2 pt-md-3 pt-lg-4 pb-lg-4 pb-xl-5 px-3 px-sm-4 pe-lg-0 ps-lg-5 mx-auto ms-lg-auto me-lg-4" style="max-width: 740px">
        
        <!-- Заголовок з номером замовлення -->
        <div class="d-flex align-items-sm-center border-bottom pb-4 pb-md-5">
          <div class="d-flex align-items-center justify-content-center bg-success text-white rounded-circle flex-shrink-0" style="width: 3rem; height: 3rem; margin-top: -.125rem">
            <i class="ci-check fs-4"></i>
          </div>
          <div class="w-100 ps-3">
            <div class="fs-sm mb-1">Замовлення №{{ order.order_number }}</div>
            <h1 class="h4 mb-0">Дякуємо за ваше замовлення!</h1>
            <p class="text-muted mb-0">Наш менеджер звʼяжеться з вами найближчим часом, щоб уточнити деталі замовлення.</p>
          </div>
        </div>

        <!-- Інформація про клієнта та доставку -->
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

            <!-- Блок доставки -->
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


        <!-- Купон -->
        <div class="bg-success rounded px-4 py-4" style="--cz-bg-opacity: .2">
          <div class="py-3">
            <h2 class="h5 text-center pb-2 mb-1">🎉 Вітаємо! Знижка 30% на наступну покупку!</h2>
            <p class="fs-sm text-center mb-4">Використайте цей купон зараз або знайдете його у своєму кабінеті.</p>
            <div class="d-flex gap-2 mx-auto" style="max-width: 500px">
              <input type="text" class="form-control border-white border-opacity-10 w-100" id="couponCode" value="30%SALEOFF" readonly>
              <button type="button" class="btn btn-dark" data-copy-text-from="#couponCode">Скопіювати</button>
            </div>
          </div>
        </div>

        <!-- Підказка -->
        <p class="fs-sm pt-4 pt-md-5 mt-2 mt-sm-3 mt-md-0 mb-0">
          Потрібна допомога?
          <a class="fw-medium ms-2" href="/contact">Звʼяжіться з нами</a>
        </p>
      </div>
    </div>


          <!-- Right column: Order items (gray background) -->
        <!-- Права колонка: Моє замовлення -->
        <div class="col-12 col-lg-6 px-3 px-sm-4 px-xl-5 py-5">
          <div class="bg-white border rounded-4 p-3 p-md-4 shadow-sm mx-auto" style="max-width: 636px">
            <h5 class="fw-bold mb-4">Ваше замовлення</h5>

            <div
              v-for="item in order.items"
              :key="item.id"
              class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom"
            >
              <!-- Фото + назва + кількість -->
              <div class="d-flex align-items-center">
                <img
                  :src="item.product_image"
                  class="rounded me-3"
                  style="width: 64px; height: 64px; object-fit: cover;"
                  alt="Фото товару"
                />
                <div>
                  <div class="fw-medium">{{ item.product_name }}</div>
                  <small class="text-muted">x{{ item.quantity }}</small>
                </div>
              </div>

              <!-- Ціна -->
              <div class="fw-bold text-end" style="white-space: nowrap;">
                {{ item.quantity * item.price }} грн
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
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import Breadcrumbs from '@/components/shared/Breadcrumbs.vue'
import { useI18n } from 'vue-i18n'
import { useCartStore } from '@/stores/cart'

const { locale, t } = useI18n()
const cart = useCartStore()
const order = ref(null)

const breadcrumbs = computed(() => [
  { text: t('home'), href: `/${locale.value}` },
  { text: t('checkout.breadcrumbs.thank_you'), active: true }
])

onMounted(async () => {
  const orderNumber = localStorage.getItem('lastOrderNumber')

  if (!orderNumber) {
    window.location.href = '/' // редірект, якщо сторінку відкрили вручну
    return
  }

  try {
    const { data } = await axios.get(`/api/orders/${orderNumber}`)
    order.value = data

       // 💥 Очистити конкретні ключі
    localStorage.removeItem('lastOrderNumber')
    localStorage.removeItem('cart')
    localStorage.removeItem('thankyou')
    sessionStorage.removeItem('checkout')

    // 💥 Якщо використовуєш sessionStorage більше — очисти все:
    sessionStorage.clear()

    // 💥 Очистити Pinia store
    cart.clearCart?.()

  } catch (error) {
    console.error('❌ Помилка при запиті замовлення:', error)
    //window.location.href = '/' // або /404
  }
})
</script>
