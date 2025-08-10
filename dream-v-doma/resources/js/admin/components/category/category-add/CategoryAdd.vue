<template>
  <div>
    <!-- Верхня панель із кнопками -->
    <CategoryHeader
      :title="'Додати категорію'"
      :publish-label="'Опублікувати категорію'"
      @submit="saveCategory"
      @cancel="goBack"
      @draft="saveDraft"
    />

    <!-- Назви та Slug (дві мови) -->
    <CategoryTitles :form="form" :errors="errors" @clear-error="clearError" />

    <!-- SEO блок -->
    <CategorySEO :form="form" :errors="errors" @clear-error="clearError" />

    <!-- Parent & Status -->
    <CategoryParent
      :form="form"
      :categories="categories"
      @update:form="val => { form.parent_id = val.parent_id; form.status = val.status }"
    />

  </div>
</template>

<script>
import axios from 'axios'
import CategoryHeader from './sections/CategoryHeader.vue'
import CategoryTitles from './sections/CategoryTitles.vue'
import CategorySEO from './sections/CategorySEO.vue'
import CategoryParent from './sections/CategoryParent.vue'

export default {
  name: 'CategoryAdd',
  components: {
    CategoryHeader,
    CategoryTitles,
    CategorySEO,
    CategoryParent,
  },
  props: {
    categories: { type: Array, default: () => [] }
  },
  data() {
    return {
      form: {
        name_uk: '',
        slug_uk: '',
        meta_title_uk: '',
        meta_description_uk: '',

        name_ru: '',
        slug_ru: '',
        meta_title_ru: '',
        meta_description_ru: '',

        parent_id: null,
        status: 1,

        description: {
          uk: [],
          ru: [],
        },
      },
      errors: {},
      loading: false,
    }
  },
  methods: {
    clearError(field) {
      this.errors[field] = null
    },
    async saveCategory() {
      // Простий фронт чек
      if (!this.form.name_uk?.trim() || !this.form.name_ru?.trim()) {
        alert('Заповніть всі обов’язкові поля (назва укр/рос)');
        return;
      }

      // Лог для дебага
      console.log('Дані форми перед відправкою:', this.form);

      this.loading = true;
      try {
        const res = await axios.post('/admin/categories', this.form);
        alert('Категорію успішно створено!');
        this.errors = {};
        // Якщо треба — зроби перехід
        // window.location.href = '/admin/categories';
      } catch (error) {
        if (error.response?.data?.errors) {
          this.errors = error.response.data.errors;
        } else {
          alert('Невідома помилка при збереженні!');
        }
      } finally {
        this.loading = false;
      }
    },
    saveDraft() {
      alert('Чернетка збережена!');
    },
    goBack() {
      window.history.back();
    }
  }
}
</script>
