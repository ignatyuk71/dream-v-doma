<template>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="fw-bold">🏥️ Продукти</h2>
      <a href="/admin/products/create" class="btn btn-outline-primary btn-sm">
        ➕ Додати продукт
      </a>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th style="width: 100px">SKU</th>
              <th>Назва</th>
              <th style="width: 100px"> Ціна </th>
              <th style="width: 80px"> Статус </th>
              <th style="width: 80px"> Кількість </th>
              <th style="width: 100px"> Дата </th>
              <th class="text-end" style="width: 100px"> Дії </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="product in products"
              :key="product.id"
              @click="toggleVariants(product.id)"
              class="product-row"
              style="cursor: pointer"
            >
              <td>{{ product.sku }}</td>
              <td>
                <div class="d-flex align-items-center gap-3">
                  <img
                    v-if="mainImage(product)"
                    :src="mainImage(product)"
                    class="rounded border"
                    style="width: 48px; height: 48px; object-fit: cover"
                  />
                  <div v-else class="bg-secondary rounded" style="width: 48px; height: 48px"></div>
                  <div class="fw-semibold">
                    {{ product.translations[0]?.name || '—' }}
                  </div>
                </div>
              </td>
              <td>{{ formatPrice(product.price) }} грн</td>
              <td>
                <span :class="['badge', product.status ? 'bg-success' : 'bg-danger']">
                  {{ product.status ? 'ON' : 'OFF' }}
                </span>
              </td>
              <td>{{ product.quantity_in_stock || 0 }}</td>
              <td>{{ formatDate(product.created_at) }}</td>
              <td class="text-end">
                <a :href="`/admin/products/${product.id}/edit`" class="btn btn-sm btn-outline-primary me-2"> ✏️ </a>
                <form
                    :id="`delete-form-${product.id}`"
                    :action="`/admin/products/${product.id}`"
                    method="POST"
                    class="d-inline"
                  >
                    <input type="hidden" name="_method" value="DELETE" />
                    <input type="hidden" name="_token" :value="csrf" />
                    <button type="button" class="btn btn-sm btn-outline-danger" @click="confirmDelete(product.id)">🗑️</button>
                  </form>

              </td>
            </tr>
            <tr
              v-for="product in products"
              :key="'v-' + product.id"
              :id="'variants-' + product.id"
              class="variant-row"
              style="display: none; background-color: #fff"
            >
              <td colspan="7" class="px-4 pb-3">
                <div class="small text-muted mb-2">Варіації товару:</div>
                <div class="d-flex flex-column gap-2">
                  <div
                    v-for="variant in product.variants"
                    :key="variant.id"
                    class="d-flex justify-content-between border rounded p-2 small bg-light flex-wrap"
                  >
                    <div><span>🔯 РОЗМІР:</span> <strong>{{ variant.size }}</strong></div>
                    <div><span>🎨 КОЛІР:</span> <strong>{{ variant.color }}</strong></div>
                    <div><span>💰 ЦІНА:</span> <strong>{{ variant.price_override || '—' }} грн</strong></div>
                    <div><span>📦 КІЛЬКІСТЬ:</span> <strong>{{ variant.quantity }}</strong></div>
                  </div>
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
import { showToast } from '../helpers/toast.js'

const props = defineProps({
  products: Array,
})

const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]')
const csrf = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : ''

const mainImage = (product) => {
  const img = product.images?.find(i => i.is_main)
  return img?.full_url || null
}

const toggleVariants = (id) => {
  document.querySelectorAll('.variant-row').forEach((el) => {
    if (!el.id.includes(id)) el.style.display = 'none'
  })
  const row = document.getElementById('variants-' + id)
  if (row) {
    row.style.display = row.style.display === 'table-row' ? 'none' : 'table-row'
  }
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
const formatDate = (date) => {
  return new Date(date).toLocaleDateString('uk-UA')
}

const formatPrice = (price) => {
  return Number(price).toFixed(2)
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
