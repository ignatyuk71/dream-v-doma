// app.js — AddToCart + Sticky + Lang + Offcanvas (shared Pinia)

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import i18n from './i18n.js'
import * as bootstrap from 'bootstrap'
window.bootstrap = bootstrap

import AddToCartProduct from './components/productPage/AddToCartProduct.vue'
import StickyAddToCartButton from './components/cart/StickyAddToCartButton.vue'
import LanguageSwitcher from './components/LanguageSwitcher.vue'
import CartOffcanvas from './components/cart/CartOffcanvas.vue'
import CartItems from './components/cart/CartItems.vue'
import StockProgress from './components/productPage/StockProgress.vue'
import CheckoutPage from './components/cart/CheckoutPage.vue'
import ThankYouPage from './components/cart/ThankYouPage.vue'

// ===== Глобальний toast =====
window.showGlobalToast = function (message = 'Успішно', color = 'success') {
  const id = 'global-toast-container'
  let c = document.getElementById(id)
  if (!c) { c = document.createElement('div'); c.id = id; document.body.appendChild(c) }
  c.innerHTML = `
    <div class="toast align-items-center text-bg-${color} border-0 show position-fixed top-0 start-50 translate-middle-x zindex-tooltip"
         role="alert" aria-live="assertive" aria-atomic="true" style="min-width:250px;margin-top:1rem;">
      <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Закрити"></button>
      </div>
    </div>`
  new bootstrap.Toast(c.querySelector('.toast'), { delay: 3000 }).show()
}

// ===== ОДИН спільний Pinia для всіх інстансів =====
const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)

// ===== Хелпер для монтування з i18n + СПІЛЬНИМ pinia =====
function mount(component, props, el) {
  const app = createApp(component, props || {})
  app.use(pinia).use(i18n).mount(el)
}

/* ===== Offcanvas монтуємо один раз (щоб існував #shoppingCart) ===== */
(function mountCartOffcanvas() {
  const host = document.getElementById('cart-offcanvas')
  if (!host || host.hasAttribute('data-mounted')) return
  mount(CartOffcanvas, {}, host)
  host.setAttribute('data-mounted', '1')
})()

/* ===== Sticky кнопка ===== */
const sticky = document.getElementById('sticky-add-to-cart')
if (sticky && !sticky.hasAttribute('data-mounted')) {
  const product = JSON.parse(sticky.dataset.product || '{}')
  mount(StickyAddToCartButton, { product }, sticky)
  sticky.setAttribute('data-mounted', '1')
}

/* ===== Кнопка у картці (ID #add-to-cart) ===== */
const elAddToCart = document.getElementById('add-to-cart')
if (elAddToCart && !elAddToCart.hasAttribute('data-mounted')) {
  const product = JSON.parse(elAddToCart.dataset.product || '{}')
  mount(AddToCartProduct, { product }, elAddToCart)
  elAddToCart.setAttribute('data-mounted', '1')
}

/* ===== (якщо ще десь використовуєш data-add-to-cart) ===== */
document.querySelectorAll('[data-add-to-cart]').forEach(el => {
  if (el.hasAttribute('data-mounted')) return
  const product = JSON.parse(el.dataset.product || '{}')
  mount(AddToCartProduct, { product }, el)
  el.setAttribute('data-mounted', '1')
})

/* ===== Language Switcher (новий безпечний варіант) ===== */
const langEl = document.getElementById('language-switcher')
if (langEl && !langEl.hasAttribute('data-mounted')) {
  const raw = document.getElementById('language-switcher-props')?.textContent || '{}'
  let props = {}
  try { props = JSON.parse(raw) } catch (e) { console.error('Bad JSON for LanguageSwitcher', e) }
  mount(LanguageSwitcher, props, langEl)
  langEl.setAttribute('data-mounted', '1')
}

// ...внизу, замість CartButton монтуємо CartItems:
document.querySelectorAll('[data-component="cart-button"]').forEach(el => {
  if (el.hasAttribute('data-mounted')) return
  const app = createApp(CartItems)
  app.use(pinia).use(i18n).mount(el)
  el.setAttribute('data-mounted', '1')
})

// Монтуємо StockProgress
const sp = document.getElementById('stock-progress')
if (sp && !sp.hasAttribute('data-mounted')) {
  mount(StockProgress, {}, sp)
  sp.setAttribute('data-mounted', '1')
}

// CheckoutPage
const checkoutHost = document.getElementById('checkout-page')
if (checkoutHost && !checkoutHost.hasAttribute('data-mounted')) {
  const localeProp = checkoutHost.dataset.locale || document.documentElement.lang || 'uk'
  mount(CheckoutPage, { locale: localeProp }, checkoutHost)
  checkoutHost.setAttribute('data-mounted', '1')
}

// ThankYouPage
const thankYouHost = document.getElementById('thank-you')
if (thankYouHost && !thankYouHost.hasAttribute('data-mounted')) {
  mount(ThankYouPage, {}, thankYouHost)
  thankYouHost.setAttribute('data-mounted', '1')
}
