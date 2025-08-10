<template>
  <div>
    <ProductFilters
      :search="filters.search"
      :status="filters.status"
      :category="filters.category"
      :categories="categories"
      @update:search="filters.search = $event"
      @update:status="filters.status = $event"
      @update:category="filters.category = $event"
    />
    <ProductTable
      :products="products"
      @deleted="handleDeleted"
    />

    <div class="pagination-controls">
      <button :disabled="pagination.current_page === 1" @click="changePage(pagination.current_page - 1)">
        Назад
      </button>

      <span>Сторінка {{ pagination.current_page }} з {{ pagination.last_page }}</span>

      <button :disabled="pagination.current_page === pagination.last_page" @click="changePage(pagination.current_page + 1)">
        Вперед
      </button>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import ProductFilters from './sections/ProductFilters.vue'
import ProductTable from './sections/ProductTable.vue'

export default {
  components: { ProductFilters, ProductTable },
  data() {
    return {
      filters: {
        search: '',
        status: '',
        category: '',
        page: 1,  // поточна сторінка
      },
      categories: [],
      products: [],
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
      }
    }
  },
  watch: {
    filters: {
      handler: 'fetchProducts',
      deep: true
    }
  },
  mounted() {
    this.fetchCategories()
    this.fetchProducts()
  },
  methods: {
    changePage(page) {
      if (page >= 1 && page <= this.pagination.last_page) {
        this.filters.page = page
      }
    },
    fetchCategories() {
      axios.get('/api/category-select/uk')
        .then(res => {
          this.categories = res.data
        })
        .catch(() => {
          this.categories = []
        })
    },
    fetchProducts() {
      axios.get('/api/products/list', { params: this.filters })
        .then(res => {
          this.products = res.data.data
          this.pagination.current_page = res.data.current_page
          this.pagination.last_page = res.data.last_page
          this.pagination.per_page = res.data.per_page
          this.pagination.total = res.data.total
        })
        .catch(() => {
          this.products = []
        })
    },
    handleDeleted(deletedProductId) {
      // Вилучаємо видалений продукт зі списку
      this.products = this.products.filter(product => product.id !== deletedProductId)
      // За потреби, можна оновити пагінацію чи список із сервера
    }
  }
}
</script>

<style scoped>
.pagination-controls {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 12px;
  margin-top: 20px;
  font-family: Arial, sans-serif;
  font-weight: 600;
  font-size: 16px;
  color: #333;
}

.pagination-controls button {
  padding: 8px 16px;
  border: none;
  background-color: #6366f1; /* Фіолетовий колір */
  color: white;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.pagination-controls button:hover:not(:disabled) {
  background-color: #4f46e5; /* Темніший відтінок */
}

.pagination-controls button:disabled {
  background-color: #c7c7d9;
  cursor: not-allowed;
  color: #6b7280;
}

.pagination-controls span {
  font-weight: 700;
}
</style>
