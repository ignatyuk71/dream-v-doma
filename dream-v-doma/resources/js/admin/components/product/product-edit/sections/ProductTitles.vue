<template>
  <div class="card mb-4 p-3">
    <h6 class="fw-bold mb-3">Назви та Slug</h6>

    <div class="mb-3">
      <label class="form-label">Назва (укр)</label>
      <input
        type="text"
        class="form-control"
        :class="{ 'is-invalid': errors?.name_uk }"
        name="name_uk"
        v-model="form.name_uk"
        @input="clearError('name_uk')"
      >
      <div v-if="errors?.name_uk" class="invalid-feedback">
        {{ errors.name_uk }}
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Slug (укр)</label>
      <input
        type="text"
        class="form-control"
        :class="{ 'is-invalid': errors?.slug_uk }"
        name="slug_uk"
        v-model="form.slug_uk"
        @input="clearError('slug_uk')"
      >
      <div v-if="errors?.slug_uk" class="invalid-feedback">
        {{ errors.slug_uk }}
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Назва (рос)</label>
      <input
        type="text"
        class="form-control"
        :class="{ 'is-invalid': errors?.name_ru }"
        name="name_ru"
        v-model="form.name_ru"
        @input="clearError('name_ru')"
      >
      <div v-if="errors?.name_ru" class="invalid-feedback">
        {{ errors.name_ru }}
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Slug (рос)</label>
      <input
        type="text"
        class="form-control"
        :class="{ 'is-invalid': errors?.slug_ru }"
        name="slug_ru"
        v-model="form.slug_ru"
        @input="clearError('slug_ru')"
      >
      <div v-if="errors?.slug_ru" class="invalid-feedback">
        {{ errors.slug_ru }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProductTitles',
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