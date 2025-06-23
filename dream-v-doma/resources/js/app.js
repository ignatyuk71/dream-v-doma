// ======= axios глобально =======
import axios from 'axios'
window.axios = axios
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// ======= Інші імпорти =======
import './helpers/toast.js'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import CKEditor from '@ckeditor/ckeditor5-vue'
import i18n from './i18n.js'
import * as bootstrap from 'bootstrap'
window.bootstrap = bootstrap
import 'swiper/swiper-bundle.css'

// ===== Компоненти =====
import Toast from './components/ui/Toast.vue'
import ProductsIndex from './components/products/ProductsIndex.vue'
import ProductForm from './components/products/ProductForm.vue'
import ProductEditForm from './components/products/ProductEditForm.vue'
import Test from './components/Test.vue'
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

// ======== Основний контейнер #app (глобальні частини сайту) ========
const appRoot = document.getElementById('app')
if (appRoot) {
    const app = createApp({})
    const pinia = createPinia()
    pinia.use(piniaPluginPersistedstate)

    app.use(pinia)
    app.use(i18n)

    // Глобальні компоненти
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

    app.mount('#app')
}

// ======== Сторінка категорії ========
const categoryPageEl = document.getElementById('category-page')
if (categoryPageEl) {
    const slug = categoryPageEl.dataset.slug
    createApp(CategoryPage, { slug })
        .use(i18n)
        .mount('#category-page')
}

// ======== Сторінка створення продукту ========
const createEl = document.getElementById('product-form')
if (createEl) {
    const categories = JSON.parse(createEl.dataset.categories)
    const app = createApp(ProductForm, { categories })
    app.use(CKEditor)
    app.use(i18n)
    app.mount('#product-form')
}

// ======== Сторінка редагування продукту ========
const editEl = document.getElementById('product-edit')
if (editEl) {
    const product = JSON.parse(editEl.dataset.product)
    const categories = JSON.parse(editEl.dataset.categories)
    const app = createApp(ProductEditForm, { product, categories })
    app.use(CKEditor)
    app.use(i18n)
    app.mount('#product-edit')
}

// ======== Список продуктів ========
const productList = document.getElementById('product-list')
if (productList) {
    const products = JSON.parse(productList.dataset.products)
    createApp(ProductsIndex, { products })
        .use(i18n)
        .mount('#product-list')
}

// ======== Сторінка товару ========
const productPageEl = document.getElementById('product-page')
if (productPageEl) {
    createApp(ProductPage)
        .use(i18n)
        .mount('#product-page')
}

// ======== Сторінка оформлення замовлення ========
const checkoutPage = document.getElementById('checkout-page')
if (checkoutPage) {
    const locale = checkoutPage.dataset.locale
    createApp(CheckoutPage, { locale })
        .use(i18n)
        .mount('#checkout-page')
}

// ======== Сторінка "Дякуємо за замовлення" ========
const thankYouEl = document.getElementById('thank-you')
if (thankYouEl) {
    createApp(ThankYouPage)
        .use(i18n)
        .mount('#thank-you')
}

// ======== Toast із localStorage (успішна дія після редіректу) ========
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
