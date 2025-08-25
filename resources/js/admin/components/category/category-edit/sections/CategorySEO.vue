<template>
    <div class="card mb-4 p-3">
      <h6 class="fw-bold mb-3">SEO</h6>
  
      <!-- Українська -->
      <div class="mb-3">
        <label class="form-label">Meta title (укр)</label>
        <input
          type="text"
          class="form-control"
          :class="{ 'is-invalid': errors?.meta_title_uk }"
          v-model="form.meta_title_uk"
          @input="clearError('meta_title_uk')"
        >
        <div v-if="errors?.meta_title_uk" class="invalid-feedback">
          {{ errors.meta_title_uk }}
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Meta description (укр)</label>
        <textarea
          class="form-control"
          rows="2"
          :class="{ 'is-invalid': errors?.meta_description_uk }"
          v-model="form.meta_description_uk"
          @input="clearError('meta_description_uk')"
        ></textarea>
        <div v-if="errors?.meta_description_uk" class="invalid-feedback">
          {{ errors.meta_description_uk }}
        </div>
      </div>
  
      <!-- Російська -->
      <div class="mb-3">
        <label class="form-label">Meta title (рос)</label>
        <input
          type="text"
          class="form-control"
          :class="{ 'is-invalid': errors?.meta_title_ru }"
          v-model="form.meta_title_ru"
          @input="clearError('meta_title_ru')"
        >
        <div v-if="errors?.meta_title_ru" class="invalid-feedback">
          {{ errors.meta_title_ru }}
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Meta description (рос)</label>
        <textarea
          class="form-control"
          rows="2"
          :class="{ 'is-invalid': errors?.meta_description_ru }"
          v-model="form.meta_description_ru"
          @input="clearError('meta_description_ru')"
        ></textarea>
        <div v-if="errors?.meta_description_ru" class="invalid-feedback">
          {{ errors.meta_description_ru }}
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'CategorySEO',
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
  