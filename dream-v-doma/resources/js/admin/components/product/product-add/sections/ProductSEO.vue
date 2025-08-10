<template>
    <div class="card mb-4 p-3">
      <h6 class="fw-bold mb-3">SEO-оптимізація</h6>
  
      <div class="mb-3">
        <label class="form-label">Meta Title (укр)</label>
        <input type="text" class="form-control" v-model="form.meta_title_uk">
      </div>
  
      <div class="mb-3">
        <label class="form-label">Meta Description (укр)</label>
        <textarea class="form-control" rows="2" v-model="form.meta_description_uk"></textarea>
      </div>
  
      <div class="mb-3">
        <label class="form-label">Meta Title (рос)</label>
        <input type="text" class="form-control" v-model="form.meta_title_ru">
      </div>
  
      <div class="mb-3">
        <label class="form-label">Meta Description (рос)</label>
        <textarea class="form-control" rows="2" v-model="form.meta_description_ru"></textarea>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'ProductSeo',
    props: {
      form: {
        type: Object,
        required: true
      }
    }
  }
  </script>
  