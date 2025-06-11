import './helpers/toast.js'
import { createApp } from 'vue'
import CKEditor from '@ckeditor/ckeditor5-vue'
import i18n from './i18n'

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
import CartOffcanvas from './components/CartOffcanvas.vue'
import BackToTop from './components/BackToTop.vue'
import HomePage from './components/HomePage.vue' 
import CategorySwiper from './components/CategorySwiper.vue'
import SpecialOffers from './components/SpecialOffers.vue'
import LanguageSwitcher from './components/LanguageSwitcher.vue'

// ======== Глобальний контейнер для компонента #app ========
const appRoot = document.getElementById('app')
if (appRoot) {
    const app = createApp({})

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

    app.mount('#app')
}

// ======== Сторінка створення продукту ========
const createEl = document.getElementById('product-form')
if (createEl) {
    const categories = JSON.parse(createEl.dataset.categories)

    const createAppInstance = createApp(ProductForm, { categories })
    createAppInstance.use(CKEditor)
    createAppInstance.mount('#product-form')
}

// ======== Сторінка редагування продукту ========
const editEl = document.getElementById('product-edit')
if (editEl) {
    const product = JSON.parse(editEl.dataset.product)
    const categories = JSON.parse(editEl.dataset.categories)

    const editApp = createApp(ProductEditForm, { product, categories })
    editApp.use(CKEditor)
    editApp.mount('#product-edit')
}

// ======== Список продуктів ========
const productList = document.getElementById('product-list')
if (productList) {
    const products = JSON.parse(productList.dataset.products)
    createApp(ProductsIndex, { products }).mount('#product-list')
}

// ======== Toast повідомлення з localStorage ========
const saved = localStorage.getItem('toastMessage')
if (saved) {
    try {
        const parsed = JSON.parse(saved)
        window.showToast(parsed.message, parsed.type)
    } catch (e) {
        console.error('❌ Toast JSON parsing error', e)
    }
    localStorage.removeItem('toastMessage')
}
