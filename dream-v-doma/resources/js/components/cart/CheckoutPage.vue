
<template>
  <section class="container py-5">
    <!-- Хлібні крихти -->
      <Breadcrumbs :items="breadcrumbs" />
    
    <div class="row g-4 mt-3">
     <!-- Форма доставки та оплати -->
    <div class="col-12 col-lg-7 px-1">
      <div class="bg-white border rounded-4 p-3 p-md-4 shadow-sm">

        <!-- 👤 Контактна інформація -->
        <div class="mb-4">
          <h5 class="fw-semibold mb-3">{{ $t('checkout.contact.title') }}</h5>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">{{ $t('checkout.contact.first_name') }} <span class="text-danger">*</span></label>
              <input type="text" class="form-control" v-model="firstName" :placeholder="$t('checkout.contact.placeholder_first_name')" required />
            </div>
            <div class="col-md-6">
              <label class="form-label">{{ $t('checkout.contact.last_name') }} <span class="text-danger">*</span></label>
              <input type="text" class="form-control" v-model="lastName" :placeholder="$t('checkout.contact.placeholder_last_name')" required />
            </div>
          </div>
          <div class="mt-3">
            <PhoneInput v-model="customerPhone" @validation="isPhoneValid = $event" />
          </div>
          <div class="mt-3">
            <label class="form-label">{{ $t('checkout.contact.email_optional') }}</label>
            <input
              type="email"
              class="form-control"
              v-model="customerEmail"
              :class="{ 'is-invalid': customerEmail && !isValidEmail(customerEmail) }"
            />
            <div v-if="customerEmail && !isValidEmail(customerEmail)" class="invalid-feedback">
              Введіть коректний email
            </div>
          </div>
        </div>

        <!-- 🚚 Доставка -->
        <div class="mb-4 text-center text-md-start">
          <img src="/public/assets/img/nova-poshta.svg" alt="Нова Пошта" class="mb-2" style="height: 40px;" />
          <div class="fw-semibold fs-6">
            {{ $t('checkout.delivery.title') }} — <span class="text-muted">{{ deliveryLabel }}</span>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label fw-semibold">{{ $t('checkout.delivery.method_label') }}</label>
          <div class="d-flex flex-wrap gap-3">
            <label class="form-check">
              <input class="form-check-input" type="radio" value="branch" v-model="deliveryType" />
              <span class="form-check-label">{{ $t('checkout.delivery.branch') }}</span>
            </label>
            <label class="form-check">
              <input class="form-check-input" type="radio" value="postomat" v-model="deliveryType" />
              <span class="form-check-label">{{ $t('checkout.delivery.postomat') }}</span>
            </label>
            <label class="form-check">
              <input class="form-check-input" type="radio" value="courier" v-model="deliveryType" />
              <span class="form-check-label">{{ $t('checkout.delivery.courier') }}</span>
            </label>
          </div>
        </div>

<!-- 📍 Місто -->
<div class="mb-4 position-relative" style="z-index: 5555;">
<label for="city-input" class="form-label fw-semibold">
  {{ $t('checkout.delivery.city_label') }} <span class="text-danger">*</span>
</label>
<small class="text-muted d-block mb-1">{{ $t('checkout.delivery.city_help') }}</small>

<input
  id="city-input"
  type="text"
  class="form-control form-control-lg"
  v-model="city"
  autocomplete="off"
  :placeholder="$t('checkout.delivery.city_placeholder')"
/>

<!-- 🔄 Спінер завантаження -->
<div v-if="isLoadingCities" class="mt-2 d-flex align-items-center gap-2">
  <div class="spinner-border spinner-border-sm text-secondary" role="status" />
  <span class="text-muted small">{{ $t('checkout.delivery.loading_cities') }}</span>
</div>

<!-- ❌ Якщо нічого не знайдено -->
<div
  v-if="!isLoadingCities && cityNotFound && city.length >= 3"
  class="mt-2 text-danger small"
>
  {{ $t('checkout.delivery.city_not_found') || 'Такого населеного пункту не знайдено' }}
</div>

<!-- 📋 Результати міст -->
<ul
  v-if="cityResults.length"
  class="list-group position-absolute start-0 end-0 mt-1 shadow-sm border rounded bg-white"
  style="z-index: 5555; max-height: 250px; overflow-y: auto;"
>
<li
    v-for="(result, index) in cityResults"
    :key="result.Ref + '-' + index"
    class="list-group-item list-group-item-action"
    @click="selectCity(result)"
    style="cursor: pointer;"
  >
    {{ result.Present }}
  </li>
</ul>
</div>



        <!-- 📦 Відділення або поштомат -->
    <div class="mb-4 position-relative" v-if="deliveryType !== 'courier'" style="z-index: 5552;">
      <label class="form-label fw-semibold">
        {{ deliveryType === 'postomat' ? $t('checkout.delivery.postomat_label') : $t('checkout.delivery.warehouse_label') }}
        <span class="text-danger">*</span>
      </label>
      <small class="text-muted d-block mb-1">
        {{
          deliveryType === 'postomat'
            ? $t('checkout.delivery.postomat_help')
            : $t('checkout.delivery.warehouse_help')
        }}
      </small>

      <!-- 🔽 Поле відділення -->
      <input
        type="text"
        class="form-control form-control-lg"
        v-model="warehouseSearch"
        autocomplete="off"
        :disabled="!selectedCity"
        :placeholder="$t('checkout.delivery.city_placeholder')"
      />

      <!-- ⏳ Спінер під інпутом -->
      <div v-if="isLoadingWarehouses" class="mt-2 d-flex align-items-center gap-2">
        <div class="spinner-border spinner-border-sm text-secondary" role="status" />
        <span class="text-muted small">{{ $t('checkout.delivery.loading_warehouses') }}</span>
      </div>

      <!-- 📋 Список відділень -->
      <ul
        v-if="filteredWarehouses.length && warehouseSearch !== selectedWarehouse?.Description"
        class="list-group position-absolute start-0 end-0 mt-1 shadow-sm border rounded bg-white"
        style="max-height: 250px; overflow-y: auto;"
      >
        <li
          v-for="w in filteredWarehouses.slice(0, 50)"
          :key="w.Ref"
          class="list-group-item list-group-item-action"
          @click="selectWarehouseFromList(w)"
          style="cursor: pointer;"
        >
          {{ w.Description }}
        </li>
      </ul>
    </div>


    <!-- 🚚 Кур'єрська доставка -->
    <div class="mb-4" v-if="deliveryType === 'courier'">
      <label class="form-label fw-semibold">
        {{ $t('checkout.delivery.courier_address_label') }} <span class="text-danger">*</span>
      </label>
      <small class="text-muted d-block mb-1">{{ $t('checkout.delivery.courier_address_help') }}</small>
      <input
        type="text"
        class="form-control form-control-lg"
        v-model="courierAddress"
        :placeholder="$t('checkout.delivery.courier_address_help')"
      />
    </div>

        <!-- 💰 Оплата -->
        <div class="mb-4">
          <label class="form-label fw-semibold">
            {{ $t('checkout.payment.label') }} <span class="text-danger">*</span>
          </label>
          <div class="d-flex flex-column gap-3">
            <label class="form-check opacity-50">
              <input class="form-check-input" type="radio" value="card" disabled />
              <span class="form-check-label">{{ $t('checkout.payment.card') }}</span>
            </label>
            <label class="form-check">
              <input class="form-check-input" type="radio" value="cod" v-model="paymentType" />
              <span class="form-check-label">
                {{ $t('checkout.payment.cod') }}
                <div v-if="paymentType === 'cod'" class="mt-2 small text-muted">
                  {{ $t('checkout.payment.cod_note', { subtotal: subtotal, deliveryCost: deliveryCost, codFee: codFee }) }}
                </div>
              </span>
            </label>
            <label class="form-check">
              <input class="form-check-input" type="radio" value="invoice" v-model="paymentType" />
              <span class="form-check-label">
                {{ $t('checkout.payment.invoice') }}
                <div v-if="paymentType === 'invoice'" class="mt-2 small text-muted">
                  {{ $t('checkout.payment.invoice_note', { subtotal: subtotal, deliveryCost: deliveryCost }) }}
                </div>
              </span>
            </label>
          </div>
        </div>

        <!-- Кнопка -->
        <div class="text-end">
          <button class="btn btn-danger btn-lg px-4" @click="submitForm">
            {{ $t('checkout.order.button') }}
          </button>
        </div>
      </div>
    </div>


     <!-- 🧾 Зведення замовлення -->
    <aside class="col-12 col-lg-5 px-1">
      <div class="bg-white border rounded-4 p-2 p-md-4 shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="fw-bold mb-0">{{ $t('checkout.order.summary_title') }}</h5>
        </div>

        <div v-for="item in cart.items" :key="item.id" class="d-flex mb-3 border-bottom pb-3">
          <img :src="item.image" class="rounded" style="width: 64px; height: 64px; object-fit: cover;" />
          <div class="ms-3 flex-grow-1">
            <div class="fw-medium">{{ item.name }}</div>
            <small class="text-muted">{{ $t('checkout.order.quantity', { quantity: item.quantity }) }}</small>
          </div>
          <div class="text-end fw-bold">{{ item.price * item.quantity }} {{ $t('currency') }}</div>
        </div>

        <div v-if="isFreeShipping" class="alert alert-success small d-flex align-items-center gap-2 mb-3 rounded-3 py-2 px-3">
            <i class="ci-check-circle fs-lg"></i>
            <span>Вітаємо! Ваше замовлення відповідає умовам безкоштовної доставки.</span>
          </div>
          

        <ul class="list-unstyled border-bottom pb-3 mb-3">
          <li class="d-flex justify-content-between py-1">
            <span>{{ $t('checkout.order.amount') }}</span>
            <span class="fw-bold">{{ subtotal }} {{ $t('currency') }}</span>
          </li>
          <li class="py-1">
            <div class="d-flex justify-content-between">
              <span>{{ $t('checkout.order.delivery', { label: deliveryLabel }) }}</span>
              <span class="fw-bold">{{ deliveryCost }} {{ $t('currency') }}</span>
            </div>
            <div v-if="paymentType === 'cod'" class="text-danger small mt-1">
              {{ $t('checkout.order.cod_fee', { codFee: codFee }) }}
            </div>
          </li>
          <li class="d-flex justify-content-between py-1" v-if="bonuses > 0">
            <span>{{ $t('checkout.order.bonuses') }}</span>
            <span class="fw-bold text-success">-{{ bonuses }} {{ $t('currency') }}</span>
          </li>
        </ul>

        <div v-if="!promoApplied" class="mb-3">
          <a href="#" @click.prevent="showPromoInput = !showPromoInput" class="text-success text-decoration-underline fw-medium">
            {{ $t('checkout.order.promo_code_label') }}
          </a>
          <div v-if="showPromoInput" class="d-flex mt-2 gap-2">
            <input type="text" v-model="promoCode" class="form-control" :placeholder="$t('checkout.order.promo_placeholder')" />
            <button class="btn btn-outline-success" @click="applyPromo">
              {{ $t('checkout.order.promo_button') }}
            </button>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
          <span class="fs-5 fw-semibold">{{ $t('checkout.order.total') }}</span>
          <span class="fs-3 fw-bold">{{ total }} {{ $t('currency') }}</span>
        </div>
      </div>
    </aside>

    </div>
  </section>
</template>


<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { useI18n } from 'vue-i18n'
import axios from 'axios'
import Breadcrumbs from '@/components/shared/Breadcrumbs.vue'
import PhoneInput from '@/components/shared/PhoneInput.vue'
import { useCartStore } from '@/stores/cart'
import { normalizePhone, isValidPhone } from '@/helpers/phone'
import Swal from 'sweetalert2'

const { locale, t } = useI18n()

// Хлібні крихти
const breadcrumbs = [
{ text: t('home'), href: `/${locale.value}` },
{ text: t('checkout_breadcrumbs'), active: true }
]

// Контактні дані
const firstName = ref('')
const lastName = ref('')
const customerPhone = ref('')
const isPhoneValid = ref(false)
const customerEmail = ref('')

// Доставка / Оплата
const deliveryType = ref('branch')
const paymentType = ref('')
const city = ref('')
const cityResults = ref([])
const selectedCity = ref(null)
const isCitySelected = ref(false)
const isCityProgrammaticChange = ref(false)
const isLoadingCities = ref(false)
let lastCityQuery = ''
const warehouses = ref([])
const isLoadingWarehouses = ref(false)
const warehouseSearch = ref('')
const selectedWarehouse = ref(null)
const courierAddress = ref('')

// Промокод
const promoCode = ref('')
const showPromoInput = ref(false)
const promoApplied = ref(false)
const bonuses = ref(0)

// Кошик
const cart = useCartStore()

function isValidEmail(email) {
return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
}

const deliveryLabel = computed(() => {
switch (deliveryType.value) {
  case 'branch': return 'У відділення'
  case 'postomat': return 'У поштомат'
  case 'courier': return 'Кур’єром'
  default: return 'Не вибрано'
}
})

const isFreeShipping = computed(() => subtotal.value >= 1000)

const deliveryCost = computed(() => {
  if (subtotal.value >= 1000) return 0
  switch (deliveryType.value) {
    case 'branch': return 60
    case 'postomat': return 55
    case 'courier': return 90
    default: return 0
  }
})

const subtotal = computed(() => cart.items.reduce((sum, item) => sum + item.price * item.quantity, 0))
const codFee = 26
const total = computed(() => {
let sum = subtotal.value + deliveryCost.value - bonuses.value
if (paymentType.value === 'cod') sum += codFee
return sum
})

const filteredWarehouses = computed(() => {
if (!warehouseSearch.value) return warehouses.value
const search = warehouseSearch.value.toLowerCase()
return warehouses.value.filter(w => w.Description.toLowerCase().includes(search))
})

function applyPromo() {
if (promoCode.value === 'BONUS100') {
  bonuses.value = 100
  promoApplied.value = true
}
}

// 🔍 Завантаження міст
async function searchCity() {
const normalizeCity = (name) => {
  if (!name) return ''
  return name.charAt(0).toUpperCase() + name.slice(1).toLowerCase()
}

const currentQuery = normalizeCity(city.value)
lastCityQuery = currentQuery

if (currentQuery.length < 3) {
  cityResults.value = []
  return
}

isLoadingCities.value = true
try {
  const res = await axios.get('/nova-poshta/cities', { params: { q: currentQuery } })

  // ✅ Лише якщо користувач ще не змінив запит
  if (lastCityQuery === normalizeCity(city.value)) {
    cityResults.value = res.data
  }
} catch (e) {
  cityResults.value = []
  console.error('❌ Axios помилка при пошуку міста:', e)
} finally {
  isLoadingCities.value = false
}
}


// ✅ Вибір міста
async function selectCity(cityItem) {
selectedCity.value = cityItem
isCityProgrammaticChange.value = true
city.value = cityItem.Present
isCitySelected.value = true
cityResults.value = []
selectedWarehouse.value = null
warehouseSearch.value = ''
warehouses.value = []
await loadWarehouses(cityItem)
await nextTick()
isCityProgrammaticChange.value = false
}

// ✅ Завантаження відділень
async function loadWarehouses(cityItem = null) {
  const cityToUse = cityItem || selectedCity.value
  if (!cityToUse || !cityToUse.DeliveryCity) {
    console.warn('🚫 DeliveryCity не вказано, не можемо завантажити відділення')
    isLoadingWarehouses.value = false
    return
  }

  const ref = cityToUse.DeliveryCity

  console.log('📦 Запит усіх типів відділень:', { ref })

  isLoadingWarehouses.value = true
  try {
    const res = await axios.get('/nova-poshta/warehouses', { params: { ref } }) // ← без type
    warehouses.value = res.data
    console.log('📦 Отримано відділень:', res.data.length)
  } catch (e) {
    warehouses.value = []
    console.error('❌ Помилка завантаження відділень:', e)
  } finally {
    isLoadingWarehouses.value = false
  }
}



function selectWarehouseFromList(warehouse) {
selectedWarehouse.value = warehouse
warehouseSearch.value = warehouse.Description
warehouses.value = []
}

// 🔁 Зміна типу доставки
watch(deliveryType, async () => {
if (selectedCity.value) {
  await loadWarehouses()
}
})



let debounceTimeout = null
const cityNotFound = ref(false)

// 🔁 Обробка ручного вводу в поле міста
watch(city, (val, oldVal = '') => {
if (val.length < oldVal.length && val.length !== 0) return

const selectedName = selectedCity.value?.Present || ''

if (val !== selectedName) {
  selectedCity.value = null
  selectedWarehouse.value = null
  warehouses.value = []
}

if (isCitySelected.value || isCityProgrammaticChange.value) {
  isCitySelected.value = false
  return
}

if (debounceTimeout) clearTimeout(debounceTimeout)

debounceTimeout = setTimeout(async () => {
  if (val.length >= 4) {
    console.log('🔍 Введене місто:', val)
    console.log('📤 Відправляється запит до API:', `/nova-poshta/cities?q=${val}`)

    await searchCity()

    // ⛔ Якщо нічого не знайдено
    cityNotFound.value = cityResults.value.length === 0
    if (cityNotFound.value) {
      console.warn('⚠️ Місто не знайдено:', val)
    } else {
      console.log('✅ Знайдено міст:', cityResults.value.length)
    }
  } else {
    cityResults.value = []
    cityNotFound.value = false
  }
}, 1000)
})



// 🔁 Зміна поля пошуку відділення
watch(warehouseSearch, (val) => {
if (!val) {
  selectedWarehouse.value = null
  return
}

if (
  selectedWarehouse.value &&
  val === selectedWarehouse.value.Description
) {
  return
}

selectedWarehouse.value = null
})

function validateForm() {
if (!firstName.value.trim()) return t('checkout.contact.first_name') + ' ' + t('checkout.validation.required')
if (!lastName.value.trim()) return t('checkout.contact.last_name') + ' ' + t('checkout.validation.required')
if (!isValidPhone(normalizePhone(customerPhone.value))) return t('checkout.validation.invalid_phone')
if (!selectedCity.value) return t('checkout.validation.select_city')
if (!paymentType.value) return t('checkout.validation.select_payment')
if (deliveryType.value === 'courier' && !courierAddress.value.trim()) return t('checkout.validation.enter_address')
if (deliveryType.value !== 'courier' && !selectedWarehouse.value) return t('checkout.validation.select_warehouse')
return null
}

async function submitForm() {
// 🛑 Перевірка на порожній кошик
if (!cart.items.length) {
  alert('🛑 Товарів у кошику немає. Ви будете перенаправлені на головну сторінку.');
  window.location.href = `/${locale.value}` // або `/ua`, якщо ти не використовуєш i18n.locale
  return
}

const error = validateForm()
if (error) {
  alert('❌ ' + error)
  return
}


const payload = {
  type: deliveryType.value,
  payment_type: paymentType.value,
  city: selectedCity.value,
  warehouse: selectedWarehouse.value?.Ref || '',
  courier_address: courierAddress.value,
  cartItems: cart.items,
  delivery_cost: deliveryCost.value,
  total: total.value,
  promo: promoCode.value,
  bonuses: bonuses.value,
  first_name: firstName.value,
  last_name: lastName.value,
  phone: normalizePhone(customerPhone.value),
  email: customerEmail.value || null,
  np_description: deliveryType.value === 'courier'
    ? selectedCity.value?.Present ?? ''
    : selectedWarehouse.value?.Description ?? '',
  np_address: deliveryType.value === 'courier'
    ? selectedCity.value?.Description ?? ''
    : selectedWarehouse.value?.ShortAddress ?? ''
}

try {
  const response = await axios.post('/api/orders', payload)
  const orderNumber = response.data.order_number
  const locale = document.documentElement.lang || 'ua'
  localStorage.setItem('lastOrderNumber', orderNumber)
  window.location.href = `/${locale}/thank-you`
} catch (error) {
  console.error('❌ Помилка при створенні замовлення:', error)
  if (error.response?.data) {
    alert('Помилка: ' + JSON.stringify(error.response.data))
  } else {
    alert('Помилка сервера, спробуйте пізніше')
  }
}
}
</script>



<style scoped>
.list-group {
  max-height: 200px;
  overflow-y: auto;
}
</style>
