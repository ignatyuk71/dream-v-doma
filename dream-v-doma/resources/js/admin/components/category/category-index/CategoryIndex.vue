<template>
  <div>
    <!-- Верхній блок із кнопкою -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0 fw-bold">Категорії</h4>
      <a
        href="/admin/categories/create"
        class="d-inline-flex align-items-center px-4 py-2"
        style="background: linear-gradient(to right, #6366f1, #8b5cf6); border: none; border-radius: 12px; color: #fff; font-weight: 600; font-size: 15px; text-decoration: none; box-shadow: 0 4px 10px rgba(99, 102, 241, 0.15); transition: background 0.3s;"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor"
          class="bi bi-folder-plus me-2" viewBox="0 0 16 16">
          <path d="M9.828 4a.5.5 0 0 0-.354.146l-.646.647-2-2A1 1 0 0 0 5.586 2H2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H9.828zM2 3h3.586a1 1 0 0 1 .707.293l2 2H14a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm7 7a.5.5 0 0 1 .5-.5h1v-1a.5.5 0 0 1 1 0v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1-.5-.5z"/>
        </svg>
        Додати категорію
      </a>
    </div>

    <CategoryTable
      :categories="categories"
      @reload="loadCategories"
    />
  </div>
</template>

<script>
import CategoryTable from './sections/CategoryTable.vue'
import axios from 'axios'

export default {
  components: { CategoryTable },
  data() {
    return {
      categories: []
    }
  },
  methods: {
    async loadCategories() {
      const res = await axios.get('/api/categories/list-admin')
      this.categories = res.data
    }
  },
  mounted() {
    this.loadCategories()
  }
}
</script>

<style scoped>

a[style*="linear-gradient"]:hover {
  filter: brightness(1.07);
  box-shadow: 0 6px 18px rgba(99,102,241,.17);
}

</style>