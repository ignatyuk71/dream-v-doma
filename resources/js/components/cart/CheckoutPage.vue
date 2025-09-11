<template>
  <section class="container py-1">
    <div class="row g-3">

      <!-- ▶️ Права колонка: Промокод + Разом (мобільні: ВНИЗУ; desktop: справа sticky) -->
      <aside class="col-12 col-lg-5 order-2 order-lg-2 px-1">
        <div class="sticky-lg">
          <!-- Промокоди -->
          <div class="bg-white border rounded-4 p-4 p-md-3 shadow-sm mb-3">
            <div class="d-flex align-items-center justify-content-between">
              <h6 class="mb-0 fw-semibold">{{ $t('checkout.order.promo_title') || 'Промокоди' }}</h6>
              <a href="#" class="d-inline-flex align-items-center gap-2 text-decoration-none"
                 @click.prevent="showPromoInput = !showPromoInput">
                <i class="ci-plus"></i>
                <span>{{ $t('checkout.order.promo_add') || 'Додати' }}</span>
              </a>
            </div>

            <transition name="fade">
              <div v-if="showPromoInput && !promoApplied" class="d-flex mt-3 gap-2">
                <input type="text" v-model="promoCode" class="form-control" :placeholder="$t('checkout.order.promo_placeholder')" />
                <button class="btn btn-outline-success" @click="applyPromo">
                  {{ $t('checkout.order.promo_button') }}
                </button>
              </div>
            </transition>
            <div v-if="promoApplied" class="mt-2 small text-success">
              {{ $t('checkout.order.promo_applied') || 'Промокод застосовано' }}
            </div>
          </div>

          <!-- Разом (sticky на desktop) -->
          <div class="bg-white border rounded-4 p-4 p-md-3 shadow-sm">
            <h5 class="fw-bold mb-3">{{ $t('checkout.order.summary_title') }}</h5>

            <ul class="list-unstyled mb-3">
              <li class="d-flex justify-content-between py-1">
                <span>{{ itemsCountText }}</span>
                <span class="fw-semibold">{{ subtotal }} {{ $t('currency') }}</span>
              </li>

              <!-- Доставка -->
              <li class="d-flex justify-content-between align-items-center py-1">
                <span>{{ $t('checkout.order.delivery', { label: deliveryLabel }) }}</span>

                <template v-if="isFreeShipping">
                  <span class="badge rounded-pill bg-success-subtle text-success border text-uppercase px-2">
                    {{ $t('checkout.order.free') || 'FREE' }}
                  </span>
                </template>
                <template v-else>
                  <span class="fw-semibold">{{ deliveryCost }} {{ $t('currency') }}</span>
                </template>
              </li>

              <!-- Комісія післяплати (червоним) -->
              <li v-if="paymentType === 'cod'" class="d-flex justify-content-between py-1 text-danger">
                <span class="fw-semibold">{{ $t('checkout.order.cod_fee_short') || 'Комісія післяплати' }}</span>
                <span class="fw-bold">{{ codFee }} {{ $t('currency') }}</span>
              </li>

              <li v-if="bonuses > 0" class="d-flex justify-content-between py-1 text-success">
                <span>{{ $t('checkout.order.bonuses') }}</span>
                <span class="fw-semibold">-{{ bonuses }} {{ $t('currency') }}</span>
              </li>
            </ul>

            <!-- ✅ Повідомлення про безкоштовну доставку -->
            <div v-if="isFreeShipping" class="alert alert-success d-flex align-items-center gap-2 py-2 px-3 mb-3 rounded-3">
              <i class="ci-check-circle fs-lg"></i>
              <span>{{ $t('checkout.order.free_shipping_msg') || 'Вітаємо! Ваше замовлення відповідає умовам безкоштовної доставки.' }}</span>
            </div>

            <!-- Підсумок -->
            <div class="d-flex justify-content-between align-items-center border-top pt-3 mb-2">
              <span class="fs-6 fw-semibold">{{ $t('checkout.order.total') }}</span>
              <span class="fs-4 fw-bold">{{ total }} {{ $t('currency') }}</span>
            </div>

            <!-- Кнопка підтвердження -->
            <button class="btn btn-success w-100 py-2" @click="submitForm">
              {{ $t('checkout.order.button') }}
            </button>

            <div class="mt-3 small text-muted">
              {{ $t('checkout.order.terms_hint') || 'Підтверджуючи замовлення, я приймаю умови:' }}
              <ul class="mt-2 mb-0 ps-3">
                <li><a href="/policy" class="text-muted text-decoration-underline">{{ $t('policy') || 'політика конфіденційності' }}</a></li>
                <li><a href="/terms" class="text-muted text-decoration-underline">{{ $t('terms') || 'угода користувача' }}</a></li>
              </ul>
            </div>
          </div>

        </div>
      </aside>

      <!-- ◀️ Ліва колонка (головний контент) — ПЕРША на мобільному -->
      <div class="col-12 col-lg-7 order-1 order-lg-1 px-1">
        <!-- 1) Замовлення (товари) -->
        <div class="bg-white border rounded-4 p-2 p-md-3 shadow-sm mb-3">
          <div class="d-flex justify-content-between align-items-baseline mb-2">
            <h6 class="fw-bold mb-0">{{ $t('checkout.order.order_title') || 'Замовлення' }}</h6>
            <div class="text-muted small">
              {{ $t('checkout.order.on_amount') || 'на суму:' }}
              <span class="fw-semibold text-dark ms-1">{{ subtotal }} {{ $t('currency') }}</span>
            </div>
          </div>

          <div v-for="item in cart.items" :key="item.id" class="border rounded-3 p-2 mb-2">
            <div class="d-flex">
              <img
                :src="withStorage(item.image || item.image_url || item.product_image)"
                @error="(e)=> e.target.src = '/assets/img/placeholder.jpg'"
                class="rounded me-2"
                style="width:72px;height:72px;object-fit:cover"
                alt=""
              />
              <div class="flex-grow-1">
                <a :href="item.link" class="fw-medium text-body text-decoration-none d-block text-truncate-2">
                  {{ item.name }}
                </a>
                <div class="small text-muted mt-1" v-if="item.size">
                  <span v-if="item.size">
                    {{ $t('checkout.order.size') || 'Розмір' }}:
                    <span class="fw-bold text-body">{{ item.size }}</span>
                  </span>
                </div>

                <div class="small text-muted mt-1" v-if="item.color">
                  <span v-if="item.color">
                    {{ $t('product.color') || 'Колір' }}:
                    <span class="fw-bold text-body">{{ item.color }}</span>
                  </span>
                </div>
                <div class="small mt-1">
                  <span class="fw-semibold">{{ item.price }} {{ $t('currency') }}</span>
                  <span class="text-muted"> × {{ item.quantity }} <b>{{ $t('pcs') || 'од.' }}</b></span>
                </div>
              </div>
              <div class="ms-2 text-nowrap fw-semibold">
                {{ item.price * item.quantity }} {{ $t('currency') }}
              </div>
            </div>
          </div>

          <div class="text-end">
            <!-- додаткові дії за потреби -->
          </div>
        </div>

        <!-- 2) Доставка -->
        <div class="bg-white border rounded-4 p-2 p-md-3 shadow-sm mb-3">
          <div class="mb-3 text-center text-md-start">
            <img src="/public/assets/img/nova-poshta.svg" alt="Нова Пошта" class="mb-2" style="height: 40px;" />
            <div class="fw-semibold fs-6">
              {{ $t('checkout.delivery.title') }} — <span class="text-muted">{{ deliveryLabel }}</span>
            </div>
          </div>
          <div class="list-group">
            <label class="list-group-item d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <input class="form-check-input me-2" type="radio" value="branch" v-model="deliveryType" />
                <span>{{ $t('checkout.delivery.branch') }}</span>
              </div>
              <span class="badge bg-light text-dark fs-sm">
                {{ !isFreeShipping ? '80 грн' : $t('checkout.order.free') }}
              </span>
            </label>

            <label class="list-group-item d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <input class="form-check-input me-2" type="radio" value="postomat" v-model="deliveryType" />
                <span>{{ $t('checkout.delivery.postomat') }}</span>
              </div>
              <span class="badge bg-light text-dark fs-sm">
                {{ !isFreeShipping ? '80 грн' : $t('checkout.order.free') }}
              </span>
            </label>

            <label class="list-group-item d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <input class="form-check-input me-2" type="radio" value="courier" v-model="deliveryType" />
                <span>{{ $t('checkout.delivery.courier') }}</span>
              </div>
              <span class="badge bg-light text-dark fs-sm">
                {{ !isFreeShipping ? '115 грн' : $t('checkout.order.free') }}
              </span>
            </label>
          </div>
          <!-- місто -->
          <div class="mb-3 position-relative" style="z-index:1020">
            <label class="form-label fw-semibold">
              {{ $t('checkout.delivery.city_label') }} <span class="text-danger">*</span>
            </label>
            <small class="text-muted d-block mb-1">{{ $t('checkout.delivery.city_help') }}</small>

            <input id="city-input" type="text" class="form-control" v-model="city" autocomplete="off"
                   :placeholder="$t('checkout.delivery.city_placeholder')" />

            <div v-if="isLoadingCities" class="mt-2 d-flex align-items-center gap-2">
              <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
              <span class="text-muted small">{{ $t('checkout.delivery.loading_cities') }}</span>
            </div>
            <div v-if="!isLoadingCities && cityNotFound && city.length >= 3" class="mt-2 text-danger small">
              {{ $t('checkout.delivery.city_not_found') }}
            </div>

            <ul v-if="cityResults.length" class="list-group position-absolute start-0 end-0 mt-1 shadow-sm border rounded bg-white"
                style="max-height:250px;overflow-y:auto">
              <li v-for="(r,idx) in cityResults" :key="r.Ref+'-'+idx" class="list-group-item list-group-item-action"
                  @click="selectCity(r)" style="cursor:pointer">
                {{ r.Present }}
              </li>
            </ul>
          </div>

          <!-- відділення/поштомат -->
          <div class="mb-3 position-relative" v-if="deliveryType !== 'courier'" style="z-index:1010">
            <label class="form-label fw-semibold">
              {{ deliveryType === 'postomat' ? $t('checkout.delivery.postomat_label') : $t('checkout.delivery.warehouse_label') }}
              <span class="text-danger">*</span>
            </label>
            <small class="text-muted d-block mb-1">
              {{ deliveryType === 'postomat' ? $t('checkout.delivery.postomat_help') : $t('checkout.delivery.warehouse_help') }}
            </small>
            <input type="text" class="form-control" v-model="warehouseSearch" :disabled="!selectedCity"
                   autocomplete="off" :placeholder="$t('checkout.delivery.city_placeholder')" />
            <div v-if="isLoadingWarehouses" class="mt-2 d-flex align-items-center gap-2">
              <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
              <span class="text-muted small">{{ $t('checkout.delivery.loading_warehouses') }}</span>
            </div>
            <ul v-if="filteredWarehouses.length && warehouseSearch !== selectedWarehouse?.Description"
                class="list-group position-absolute start-0 end-0 mt-1 shadow-sm border rounded bg-white"
                style="max-height:250px;overflow-y:auto">
              <li v-for="w in filteredWarehouses.slice(0, 50)" :key="w.Ref"
                  class="list-group-item list-group-item-action" @click="selectWarehouseFromList(w)"
                  style="cursor:pointer">{{ w.Description }}</li>
            </ul>
          </div>

          <!-- адреса кур'єром -->
          <div class="mb-2" v-if="deliveryType === 'courier'">
            <label class="form-label fw-semibold">
              {{ $t('checkout.delivery.courier_address_label') }} <span class="text-danger">*</span>
            </label>
            <small class="text-muted d-block mb-1">{{ $t('checkout.delivery.courier_address_help') }}</small>
            <input type="text" class="form-control" v-model="courierAddress" :placeholder="$t('checkout.delivery.courier_address_help')" />
          </div>
        </div>

        <!-- 3) Оплата -->
        <div class="bg-white border rounded-4 p-2 p-md-3 shadow-sm mb-3">
          <h6 class="fw-bold mb-3">{{ $t('checkout.payment.label') }}</h6>
          <div class="d-flex flex-column gap-2">
            <label class="form-check opacity-50">
              <input class="form-check-input" type="radio" value="card" disabled />
              <span class="form-check-label">{{ $t('checkout.payment.card') }}</span>
            </label>
            <label class="form-check">
              <input class="form-check-input" type="radio" value="cod" v-model="paymentType" />
              <span class="form-check-label">
                {{ $t('checkout.payment.cod') }}
                <div v-if="paymentType === 'cod'" class="mt-1 small text-muted">
                  {{ $t('checkout.payment.cod_note', { subtotal: subtotal, deliveryCost: deliveryCost, codFee: codFee }) }}
                </div>
              </span>
            </label>
            <label class="form-check">
              <input class="form-check-input" type="radio" value="invoice" v-model="paymentType" />
              <span class="form-check-label">
                {{ $t('checkout.payment.invoice') }}
                <div v-if="paymentType === 'invoice'" class="mt-1 small text-muted">
                  {{ $t('checkout.payment.invoice_note', { subtotal: subtotal, deliveryCost: deliveryCost }) }}
                </div>
              </span>
            </label>
          </div>
        </div>

        <!-- 4) Одержувач -->
        <div class="bg-white border rounded-4 p-3 p-md-3 shadow-sm">
          <h6 class="fw-bold mb-3">{{ $t('checkout.contact.title') }}</h6>
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
            <input type="email" class="form-control" v-model="customerEmail"
                   :class="{ 'is-invalid': customerEmail && !isValidEmail(customerEmail) }" />
            <div v-if="customerEmail && !isValidEmail(customerEmail)" class="invalid-feedback">
              Введіть коректний email
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>
</template>

<style scoped>
/* sticky тільки на ≥lg */
@media (min-width: 992px) {
  .sticky-lg { position: sticky; top: 16px; }
}
@media (max-width: 991.98px) {
  .sticky-lg { position: static; }
}
.list-group { max-height: 200px; overflow-y: auto; }
</style>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import axios from 'axios'
import PhoneInput from '@/components/shared/PhoneInput.vue'
import { useCartStore } from '@/stores/cart'
import { normalizePhone, isValidPhone } from '@/helpers/phone'

const { locale, t } = useI18n()

// ------------------------------
// Контактні дані
// ------------------------------
const firstName = ref('')
const lastName = ref('')
const customerPhone = ref('')
const isPhoneValid = ref(false)
const customerEmail = ref('')

// ------------------------------
// Доставка / Оплата
// ------------------------------
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

// ------------------------------
// Промокод
// ------------------------------
const promoCode = ref('')
const showPromoInput = ref(false)
const promoApplied = ref(false)
const bonuses = ref(0)

// ------------------------------
// Кошик
// ------------------------------
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

const subtotal = computed(() =>
  cart.items.reduce((sum, item) => sum + (item.price * item.quantity), 0)
)

const isFreeShipping = computed(() => subtotal.value >= 1000)

const deliveryCost = computed(() => {
  if (isFreeShipping.value) return 0
  switch (deliveryType.value) {
    case 'branch': return 80     // доставка у відділення
    case 'postomat': return 80   // доставка у поштомат
    case 'courier': return 115   // курʼєрська доставка
    default: return 0
  }
})

const codFee = 26 // комісія післяплати


const total = computed(() => {
  let sum = subtotal.value + deliveryCost.value - bonuses.value
  if (paymentType.value === 'cod') sum += codFee
  return Math.max(0, Math.round(sum))
})

const itemsCountText = computed(() => {
  const units = cart.items.reduce((acc, it) => acc + (it.quantity || 0), 0)
  const one = t('checkout.order.item_one') || 'товар'
  const many = t('checkout.order.item_many') || 'товарів'
  return `${units} ${units === 1 ? one : many}`
})

const deliveryCostText = computed(() =>
  deliveryCost.value === 0 ? (t('free') || 'Безкоштовно') : `${deliveryCost.value} ${t('currency')}`
)

const filteredWarehouses = computed(() => {
  if (!warehouseSearch.value) return warehouses.value
  const search = warehouseSearch.value.toLowerCase()
  return warehouses.value.filter(w => w.Description.toLowerCase().includes(search))
})

/** ✅ Робочий промокод:
 *  BONUS100  → -100 грн
 *  WINPRICE  → -10% від суми товарів (subtotal)
 */
function applyPromo() {
  const code = (promoCode.value || '').trim().toUpperCase()
  if (!code) return

  if (promoApplied.value) {
    window.showGlobalToast?.('Промокод вже застосовано', 'info')
    return
  }

  let discount = 0
  if (code === 'BONUS100') {
    discount = 100
  } else if (code === 'WINPRICE') {
    discount = Math.floor(subtotal.value * 0.10)
  } else {
    window.showGlobalToast?.('Невірний промокод', 'warning')
    return
  }

  const maxPossible = Math.max(0, subtotal.value + deliveryCost.value)
  bonuses.value = Math.min(discount, maxPossible)

  promoApplied.value = true
  window.showGlobalToast?.('Промокод застосовано', 'success')
}

// ------------------------------
// Нова Пошта: міста / відділення
// ------------------------------
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

async function selectCity(cityItem) {
  selectedCity.value = cityItem
  isCityProgrammaticChange.value = true
  city.value = cityItem.Present
  isCitySelected.value = true
  cityResults.value = []

  // reset складу/поштомату
  selectedWarehouse.value = null
  warehouseSearch.value = ''
  warehouses.value = []

  await loadWarehouses(cityItem)
  await nextTick()
  isCityProgrammaticChange.value = false
}

async function loadWarehouses(cityItem = null) {
  const cityToUse = cityItem || selectedCity.value
  if (!cityToUse || !cityToUse.DeliveryCity) {
    console.warn('🚫 DeliveryCity не вказано, не можемо завантажити відділення')
    isLoadingWarehouses.value = false
    return
  }

  const ref = cityToUse.DeliveryCity
  isLoadingWarehouses.value = true
  try {
    const res = await axios.get('/nova-poshta/warehouses', { params: { ref } })
    warehouses.value = res.data || []
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
  // ❗ FIX: НЕ очищаємо warehouses — інакше повторний вибір неможливий
  // warehouses.value = []
}

// Зміна типу доставки → оновити відділення
watch(deliveryType, async () => {
  // ❗ FIX: повний ресет вибору пункту при зміні типу
  selectedWarehouse.value = null
  warehouseSearch.value = ''
  if (selectedCity.value) {
    await loadWarehouses()
  }
})

let debounceTimeout = null
const cityNotFound = ref(false)

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
      await searchCity()
      cityNotFound.value = cityResults.value.length === 0
    } else {
      cityResults.value = []
      cityNotFound.value = false
    }
  }, 1000)
})

watch(warehouseSearch, async (val) => {
  if (!val) {
    selectedWarehouse.value = null
    // 🔒 Підстраховка: якщо місто вибране, а список пустий — підвантажити
    if (selectedCity.value && !warehouses.value.length && !isLoadingWarehouses.value) {
      await loadWarehouses()
    }
    return
  }
  if (selectedWarehouse.value && val === selectedWarehouse.value.Description) {
    return
  }
  selectedWarehouse.value = null
})

// ------------------------------
// Валідації / сабміт
// ------------------------------
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

function openCart() {
  const el = document.getElementById('shoppingCart')
  if (el && window.bootstrap) {
    try { new window.bootstrap.Offcanvas(el).show() } catch (_) {}
  } else {
    window.location.href = `/${locale.value}/cart`
  }
}

async function submitForm() {
  if (!cart.items.length) {
    alert('🛑 Товарів у кошику немає. Ви будете перенаправлені на головну сторінку.')
    window.location.href = `/${locale.value}`
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
    const lang = document.documentElement.lang || 'ua'
    localStorage.setItem('lastOrderNumber', orderNumber)
    window.location.href = `/${lang}/thank-you`
  } catch (error) {
    console.error('❌ Помилка при створенні замовлення:', error)
    if (error.response?.data) {
      alert('Помилка: ' + JSON.stringify(error.response.data))
    } else {
      alert('Помилка сервера, спробуйте пізніше')
    }
  }
}

// ------------------------------
// Хелпер зображення через /storage
// ------------------------------
const withStorage = (path) => {
  if (!path) return '/assets/img/placeholder.jpg'
  if (/^https?:\/\//i.test(path) || String(path).startsWith('//')) {
    try { path = new URL(path, window.location.origin).pathname } catch (_) {}
  }
  let p = String(path).replace(/^\/+/, '').replace(/^(?:app\/)?public\//, '')
  if (p.startsWith('storage/')) return '/' + p
  return '/storage/' + p
}

// =====================================================================
// InitiateCheckout — виклик глобальної функції з Blade-паршалу
// =====================================================================
const callInitiateCheckout = (() => {
  let sent = false
  return function () {
    if (sent) return

    const items = (cart.items || [])
      .filter(i => (i?.variant_sku ?? '').toString().trim())
      .map(i => ({
        variant_sku: i.variant_sku,
        price: i.price,
        quantity: i.quantity,
        name: i.name
      }))

    if (!items.length) return

    const currency = window.metaPixelCurrency || 'UAH'

    const tryCall = (attempt = 0) => {
      if (typeof window.mpTrackIC === 'function') {
        window.mpTrackIC({ items, currency })
        sent = true
      } else if (attempt < 120) {
        setTimeout(() => tryCall(attempt + 1), 80)
      } else {
        console.warn('[IC] mpTrackIC is not available')
      }
    }

    tryCall()
  }
})()

onMounted(() => {
  callInitiateCheckout()
})

watch(
  () => cart.items.map(i => `${i.variant_sku}:${i.quantity}:${i.price}`).join('|'),
  (fp, prev) => {
    if (!prev && fp) callInitiateCheckout()
  },
  { immediate: false }
)
</script>
