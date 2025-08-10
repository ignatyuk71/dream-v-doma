<template>
  <div class="filters mb-4 card p-3" style="border-radius:5px;">
    <div class="row align-items-end">
      <div class="col-md-3">
        <input
          type="text"
          class="form-control"
          placeholder="Пошук товару..."
          :value="search"
          @input="update('search', $event.target.value)"
        >
      </div>
      <div class="col-md-3">
        <select
          class="form-select"
          :value="status"
          @change="update('status', $event.target.value)"
        >
          <option value="">Статус</option>
          <option value="1">Опубліковано</option>
          <option value="0">Неактивний</option>
        </select>
      </div>
      <div class="col-md-3">
        <select
          class="form-select"
          :value="category"
          @change="update('category', $event.target.value)"
        >
          <option value="">Категорія</option>
          <option
            v-for="cat in categories"
            :key="cat.id"
            :value="cat.id"
          >
            {{ cat.name }}
          </option>
        </select>
      </div>
      <div class="col-md-3 text-end">
        <a href="/admin/products/create"
          class="d-inline-flex align-items-center px-4 py-2"
          style="background: linear-gradient(to right, #6366f1, #8b5cf6); border: none; border-radius: 12px; color: #fff; font-weight: 600; font-size: 15px; text-decoration: none; box-shadow: 0 4px 10px rgba(99, 102, 241, 0.25); transition: background 0.3s ease;">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor"
            class="bi bi-box2-heart me-2" viewBox="0 0 16 16">
            <path d="M4.684 1.447A1.5 1.5 0 0 1 5.934 1h4.132a1.5 1.5 0 0 1 1.25.447l2.25 2.25A1.5 1.5 0 0 1 14.934 4H1.066a1.5 1.5 0 0 1 1.368-.803l2.25-2.25z"/>
            <path d="M.5 5.5A.5.5 0 0 1 1 5h14a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H1a.5.5 0 0 1-.5-.5v-1zm3.92 2.996a2.326 2.326 0 0 1 1.611-.63c.49 0 .98.173 1.352.568.372-.395.862-.568 1.352-.568.588 0 1.165.242 1.61.63C10.784 8.864 11 9.418 11 9.996c0 .578-.216 1.132-.655 1.5l-2.58 2.15a.5.5 0 0 1-.63 0l-2.58-2.15A1.917 1.917 0 0 1 5 9.996c0-.578.216-1.132.655-1.5z"/>
          </svg>
          Додати товар
        </a>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProductFilters',
  props: {
    search: String,
    status: [String, Number],
    category: [String, Number],
    categories: {
      type: Array,
      required: true
    }
  },
  methods: {
    update(field, value) {
      this.$emit(`update:${field}`, value)
    }
  }
}
</script>
