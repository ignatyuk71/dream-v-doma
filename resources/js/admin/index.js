import axios from 'axios'
import { createApp } from 'vue'
import ProductForm from './components/product/product-add/ProductForm.vue'
import ProductIndex from './components/product/product-index/ProductIndex.vue'
import ProductEdit from './components/product/product-edit/ProductEdit.vue'
import CategoryIndex from './components/category/category-index/CategoryIndex.vue'
import CategoryEdit from './components/category/category-edit/CategoryEdit.vue'
import CategoryAdd from './components/category/category-add/CategoryAdd.vue'
import './index.css'

// CSRF токен
axios.defaults.headers.common['X-CSRF-TOKEN'] =
  document.querySelector('meta[name="csrf-token"]').getAttribute('content')

// ProductForm (додавання)
if (document.getElementById('app')) {
  const app = createApp({})
  app.component('product-form', ProductForm)
  app.mount('#app')
}

// ProductIndex (список)
if (document.getElementById('product-index-app')) {
  const productIndexApp = createApp({})
  productIndexApp.component('product-index', ProductIndex)
  productIndexApp.mount('#product-index-app')
}

// ProductEdit (редагування)
if (document.getElementById('product-edit-app')) {
  const productEditApp = createApp({})
  productEditApp.component('product-edit', ProductEdit)
  productEditApp.mount('#product-edit-app')
}

// CategoryIndex (категорії)
// Якщо контейнер існує — монтуємо компонент
if (document.getElementById('category-index-app')) {
  const app = createApp({})
  app.component('category-index', CategoryIndex)
  app.mount('#category-index-app')
}


if (document.getElementById('category-edit-app')) {
  const app = createApp({})
  app.component('category-edit', CategoryEdit)
  app.mount('#category-edit-app')
}


// --- CategoryAdd (Додавання категорії) ---
if (document.getElementById('category-add-app')) {
  const categoryAddApp = createApp({})
  categoryAddApp.component('category-add', CategoryAdd)
  categoryAddApp.mount('#category-add-app')
}


