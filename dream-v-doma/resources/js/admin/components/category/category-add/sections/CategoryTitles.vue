<template>
  <div class="card mb-4 p-3">
    <h6 class="fw-bold mb-3">Назви та Slug (Категорія)</h6>

    <div class="mb-3">
      <label class="form-label">Назва (укр)</label>
      <input
        type="text"
        class="form-control"
        :class="{ 'is-invalid': errors?.name_uk }"
        v-model="form.name_uk"
        @input="clearError('name_uk')"
        autocomplete="off"
      >
      <div v-if="errors?.name_uk" class="invalid-feedback">
        {{ errors.name_uk }}
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Назва (рос)</label>
      <input
        type="text"
        class="form-control"
        :class="{ 'is-invalid': errors?.name_ru }"
        v-model="form.name_ru"
        @input="clearError('name_ru')"
        autocomplete="off"
      >
      <div v-if="errors?.name_ru" class="invalid-feedback">
        {{ errors.name_ru }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CategoryTitles',
  props: {
    form: { type: Object, required: true },
    errors: { type: Object, default: () => ({}) }
  },
  emits: ['clear-error'],
  methods: {
    clearError(field) {
      this.$emit('clear-error', field)
    }
  }
}
</script>
