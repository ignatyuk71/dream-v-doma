<template>
  <div class="card mb-4 p-3">
    <h6 class="fw-bold mb-3">Назви та Slug</h6>

    <div class="mb-3">
      <label class="form-label">Назва (укр)</label>
      <input type="text"
             class="form-control"
             :class="{ 'is-invalid': errors.name_uk }"
             name="name_uk"
             v-model="form.name_uk"
             @input="clearError('name_uk')"
      >
      <div v-if="errors.name_uk" class="invalid-feedback">
        {{ errors.name_uk }}
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Назва (рос)</label>
      <input type="text"
             class="form-control"
             :class="{ 'is-invalid': errors.name_ru }"
             name="name_ru"
             v-model="form.name_ru"
             @input="clearError('name_ru')"
      >
      <div v-if="errors.name_ru" class="invalid-feedback">
        {{ errors.name_ru }}
      </div>
    </div>

  </div>
</template>

<script>
export default {
  name: 'ProductTitles',
  props: {
    form: Object,
    errors: { type: Object, default: () => ({}) }
  },
  methods: {
    clearError(field) {
      // якщо поле не пусте, прибираємо помилку
      if (this.form[field]) {
        this.$emit('update:errors', { ...this.errors, [field]: null })
        // або якщо errors передаються як ref:
        this.errors[field] = null
      }
    }
  }
}
</script>
