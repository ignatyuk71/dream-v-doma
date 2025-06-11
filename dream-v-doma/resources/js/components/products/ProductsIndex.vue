<template>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="fw-bold">🛍️ Продукти</h2>
      <a href="/admin/products/create" class="btn btn-primary">
        + Додати продукт
      </a>
    </div>

    <div class="card shadow-sm border-0">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead class="table-light">
            <tr>
              <th><input type="checkbox" disabled /></th>
              <th>Продукт</th>
              <th>Категорія</th>
              <th>Stock</th>
              <th>SKU</th>
              <th>Ціна</th>
              <th>К-сть</th>
              <th>Статус</th>
              <th class="text-end">Дії</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="product in products" :key="product.id">
              <td><input type="checkbox" /></td>
              <td>
                <div class="d-flex align-items-center">
                  <img
                    v-if="mainImage(product)"
                    :src="mainImage(product)"
                    class="rounded me-2"
                    style="width: 40px; height: 40px; object-fit: cover"
                  />
                  <div>
                    <div class="fw-semibold">{{ product.translations[0]?.name || '—' }}</div>
                    <small class="text-muted">{{ product.sku }}</small>
                  </div>
                </div>
              </td>
              <td>
                <span class="badge bg-light text-dark">{{ product.category?.name || '—' }}</span>
              </td>
              <td>
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" :checked="product.status" disabled />
                </div>
              </td>
              <td>{{ product.sku }}</td>
              <td>{{ formatPrice(product.price) }} грн</td>
              <td>{{ product.quantity_in_stock || 0 }}</td>
              <td>
                <span
                  class="badge"
                  :class="{
                    'bg-success': product.status,
                    'bg-warning': !product.status && product.quantity_in_stock > 0,
                    'bg-danger': product.quantity_in_stock === 0
                  }"
                >
                  {{ product.status ? 'Publish' : (product.quantity_in_stock === 0 ? 'Inactive' : 'Scheduled') }}
                </span>
              </td>
              <td class="text-end">
                <div class="btn-group">
                  <a :href="`/admin/products/${product.id}/edit`" class="btn btn-sm btn-outline-secondary" title="Edit">✏️</a>

                  <div class="dropdown">
                    <button
                      class="btn btn-sm btn-outline-secondary dropdown-toggle"
                      type="button"
                      data-bs-toggle="dropdown"
                      aria-expanded="false"
                    >
                      ⋯
                    </button>
                    <ul class="dropdown-menu">
                      <li>
                        <a class="dropdown-item" href="#" @click.prevent="downloadProduct(product.id)">
                          <i class="bi bi-download me-2"></i> 📥 Download
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item text-danger" href="#" @click.prevent="confirmDelete(product.id)">
                          <i class="bi bi-trash me-2"></i> 🗑️ Delete
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="#" @click.prevent="duplicateProduct(product.id)">
                          <i class="bi bi-files me-2"></i> 📄 Duplicate
                        </a>
                      </li>
                    </ul>
                  </div>

                  <form :id="`delete-form-${product.id}`" :action="`/admin/products/${product.id}`" method="POST" class="d-none">
                    <input type="hidden" name="_method" value="DELETE" />
                    <input type="hidden" name="_token" :value="csrf" />
                  </form>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { showToast } from '../../helpers/toast.js'

const props = defineProps({
  products: Array,
})

const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]')
const csrf = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : ''

const mainImage = (product) => {
  const img = product.images?.find(i => i.is_main)
  return img?.full_url || null
}

const confirmDelete = (id) => {
  if (confirm('Ви впевнені, що хочете видалити цей продукт?')) {
    localStorage.setItem('toastMessage', JSON.stringify({
      message: '🗑️ Продукт успішно видалено!',
      type: 'success',
    }))
    document.getElementById(`delete-form-${id}`).submit()
  }
}

const formatPrice = (price) => {
  return Number(price).toFixed(2)
}

const downloadProduct = (id) => {
  showToast(`⬇️ Завантаження продукту #${id}`, 'info')
  // Тут можна реалізувати реальне завантаження, якщо потрібно
}

const duplicateProduct = (id) => {
  showToast(`📄 Продукт #${id} продубльовано`, 'success')
  // Тут можеш реалізувати запит на дублювання, якщо є бекендова логіка
}

onMounted(() => {
  const saved = localStorage.getItem('toastMessage')
  if (saved) {
    try {
      const parsed = JSON.parse(saved)
      showToast(parsed.message, parsed.type)
    } catch (e) {
      console.error('Toast parse error:', e)
    }
    localStorage.removeItem('toastMessage')
  }
})
</script>

<style scoped>
.table td, .table th {
  vertical-align: middle;
}
.table-responsive {
  overflow: visible !important;
}

.dropdown-menu {
  z-index: 1050;
}
</style>
