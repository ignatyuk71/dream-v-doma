import './helpers/toast.js'
import { createApp } from 'vue'
import Toast from './components/Toast.vue'
import CKEditor from '@ckeditor/ckeditor5-vue'
import ProductsIndex from './components/ProductsIndex.vue'
import ProductForm from './components/ProductForm.vue'
import ProductEditForm from './components/ProductEditForm.vue'
import Test from './components/Test.vue'

// ======== Глобальний контейнер для Toast ========
const app = createApp({})
app.component('Toast', Toast)
app.mount('#app')

// ======== Сторінка створення продукту ========
const createEl = document.getElementById('product-form')
if (createEl) {
    const categories = JSON.parse(createEl.dataset.categories)

    const createAppInstance = createApp(ProductForm, {
        categories
    })

    createAppInstance.use(CKEditor)
    createAppInstance.mount('#product-form')
}
// Підключення списку продуктів
const productList = document.getElementById('product-list')
if (productList) {
  const products = JSON.parse(productList.dataset.products)
  createApp(ProductsIndex, { products }).mount('#product-list')
}

// ======== Сторінка редагування продукту ========
const editEl = document.getElementById('product-edit')
if (editEl) {
    const product = JSON.parse(editEl.dataset.product)
    const categories = JSON.parse(editEl.dataset.categories)

    const editApp = createApp(ProductEditForm, {
        product,
        categories
    })

    editApp.use(CKEditor)
    editApp.mount('#product-edit')
}

// ======== Показати toast Показує випадаюче вікно ========
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
