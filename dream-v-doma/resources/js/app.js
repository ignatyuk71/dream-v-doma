import axios from 'axios'
window.axios = axios
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

import './helpers/toast.js'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import i18n from './i18n.js'
import * as bootstrap from 'bootstrap'
window.bootstrap = bootstrap
import 'swiper/swiper-bundle.css'

// Компоненти
import Toast from './components/ui/Toast.vue'
import Topbar from './components/Topbar.vue'
import Navbar from './components/Navbar.vue'
import HeroBanner from './components/HeroBanner.vue'
import FeaturedProducts from './components/FeaturedProducts.vue'
import FooterComponent from './components/Footer.vue'
import CartOffcanvas from './components/cart/CartOffcanvas.vue'
import BackToTop from './components/BackToTop.vue'
import HomePage from './components/HomePage.vue'
import CategorySwiper from './components/CategorySwiper.vue'
import SpecialOffers from './components/SpecialOffers.vue'
import LanguageSwitcher from './components/LanguageSwitcher.vue'
import ProductPage from './components/productPage/ProductPage.vue'
import CheckoutPage from './components/cart/CheckoutPage.vue'
import ThankYouPage from './components/cart/ThankYouPage.vue'
import FrontendToast from './components/ui/FrontendToast.vue'
import Breadcrumbs from '@/components/shared/Breadcrumbs.vue'
import CategoryPage from './components/category/CategoryPage.vue'
import StockProgress from './components/productPage/StockProgress.vue'
import AddToCartProduct from './components/productPage/AddToCartProduct.vue'
import CartItems from './components/cart/CartItems.vue'
import StickyAddToCartButton from './components/cart/StickyAddToCartButton.vue'

// Основний контейнер
const appRoot = document.getElementById('app')
if (appRoot) {
  const app = createApp({})
  const pinia = createPinia()
  pinia.use(piniaPluginPersistedstate)
  app.use(pinia)
  app.use(i18n)

  app.component('Toast', Toast)
  app.component('topbar', Topbar)
  app.component('navbar', Navbar)
  app.component('hero-banner', HeroBanner)
  app.component('featured-products', FeaturedProducts)
  app.component('footer-component', FooterComponent)
  app.component('cart-offcanvas', CartOffcanvas)
  app.component('back-to-top', BackToTop)
  app.component('category-swiper', CategorySwiper)
  app.component('special-offers', SpecialOffers)
  app.component('home-page', HomePage)
  app.component('language-switcher', LanguageSwitcher)
  app.component('frontend-toast', FrontendToast)
  app.component('cart-items', CartItems)

  app.mount('#app')
}

// CartOffcanvas
const elCartOffcanvas = document.getElementById('cart-offcanvas')
if (elCartOffcanvas) {
  createApp(CartOffcanvas).use(i18n).mount('#cart-offcanvas')
}

// Toast (глобальний)
window.showGlobalToast = function (message = 'Успішно', color = 'success') {
  const container = document.getElementById('global-toast-container')
  if (!container) return

  container.innerHTML = `
    <div class="toast align-items-center text-bg-${color} border-0 show position-fixed top-0 start-50 translate-middle-x zindex-tooltip"
         role="alert" aria-live="assertive" aria-atomic="true" style="min-width: 250px; margin-top: 1rem;">
      <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Закрити"></button>
      </div>
    </div>
  `
  const toastEl = container.querySelector('.toast')
  new bootstrap.Toast(toastEl, { delay: 3000 }).show()
}

// AddToCart (основна кнопка під ціною)
const elAddToCart = document.getElementById('add-to-cart')
if (elAddToCart) {
  const props = JSON.parse(elAddToCart.dataset.product || '{}')
  createApp(AddToCartProduct, { product: props }).use(i18n).mount(elAddToCart)
}



const elStickyAddToCart = document.getElementById('sticky-add-to-cart')
if (elStickyAddToCart) {
  const props = JSON.parse(elStickyAddToCart.dataset.product || '{}')
  createApp(StickyAddToCartButton, { product: props })
    .use(i18n) // <<< ДОДАЙ це!
    .mount(elStickyAddToCart)
}

// Language Switcher
const el = document.querySelector('[data-component="language-switcher"]')
if (el) {
  const props = JSON.parse(el.dataset.props || '{}')
  createApp(LanguageSwitcher, props).mount(el)
}

// Stock Progress
const elStockProgress = document.getElementById('stock-progress')
if (elStockProgress) {
  const props = JSON.parse(elStockProgress.dataset.product || '{}')
  createApp(StockProgress, { product: props }).use(i18n).mount(elStockProgress)
}

// Категорії
const categoryPageEl = document.getElementById('category-page')
if (categoryPageEl) {
  const slug = categoryPageEl.dataset.slug
  createApp(CategoryPage, { slug }).use(i18n).mount('#category-page')
}

// Список продуктів
const productList = document.getElementById('product-list')
if (productList) {
  const products = JSON.parse(productList.dataset.products)
  createApp(ProductsIndex, { products }).use(i18n).mount('#product-list')
}

// Checkout
const checkoutPage = document.getElementById('checkout-page')
if (checkoutPage) {
  const locale = checkoutPage.dataset.locale
  createApp(CheckoutPage, { locale }).use(i18n).mount('#checkout-page')
}

// Thank you page
const thankYouEl = document.getElementById('thank-you')
if (thankYouEl) {
  createApp(ThankYouPage).use(i18n).mount('#thank-you')
}

// Toast після редіректу
const savedToast = localStorage.getItem('toastMessage')
if (savedToast) {
  try {
    const parsed = JSON.parse(savedToast)
    window.showToast(parsed.message, parsed.type)
  } catch (e) {
    console.error('❌ Toast JSON parsing error', e)
  }
  localStorage.removeItem('toastMessage')
}
