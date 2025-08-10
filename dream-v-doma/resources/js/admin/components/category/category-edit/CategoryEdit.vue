<template>
  <div>
    <!-- Верхня панель із кнопками -->
    <CategoryHeader
      :title="headerTitle"
      :publish-label="isEdit ? 'Оновити категорію' : 'Опублікувати категорію'"
      @submit="saveCategory"
      @cancel="goBack"
      @draft="saveDraft"
    />

    <!-- Назви та Slug (дві мови) -->
    <CategoryTitles :form="form" :errors="errors" @clear-error="clearError" />

    <!-- SEO блок -->
    <CategorySEO :form="form" :errors="errors" @clear-error="clearError" />

    <!-- Parent & Status в одному блоці -->
    <CategoryParent
      :form="form"
      :categories="categories"
      :current-id="category?.id || null"
      @update:form="val => { form.parent_id = val.parent_id; form.status = val.status }"
    />

    <!-- Опис категорії (блоки, дві мови) -->
    <CategoryDescription
      v-model="form.description"
      :category-id="category?.id || null"
    />
  </div>
</template>

<script>
import axios from 'axios'
import CategoryHeader from './sections/CategoryHeader.vue'
import CategoryTitles from './sections/CategoryTitles.vue'
import CategorySEO from './sections/CategorySEO.vue'
import CategoryDescription from './sections/CategoryDescription.vue'
import CategoryParent from './sections/CategoryParent.vue'

export default {
  name: 'CategoryEdit',
  components: {
    CategoryHeader,
    CategoryTitles,
    CategorySEO,
    CategoryDescription,
    CategoryParent,
  },
  props: {
    category: { type: Object, default: null },
    categories: { type: Array, default: () => [] }
  },
  data() {
  const ukTranslation = this.category?.translations?.find(t => t.locale === 'uk') || {};
  const ruTranslation = this.category?.translations?.find(t => t.locale === 'ru') || {};

  return {
    form: {
      name_uk: ukTranslation.name || '',
      slug_uk: ukTranslation.slug || '',
      meta_title_uk: ukTranslation.meta_title || '',
      meta_description_uk: ukTranslation.meta_description || '',

      name_ru: ruTranslation.name || '',
      slug_ru: ruTranslation.slug || '',
      meta_title_ru: ruTranslation.meta_title || '',
      meta_description_ru: ruTranslation.meta_description || '',

      parent_id: this.category?.parent_id || null,
      status: typeof this.category?.status !== 'undefined' ? Number(this.category.status) : 1,

      // Витягуємо description з перекладів, якщо є:
      description: {
        uk: ukTranslation.description ? JSON.parse(ukTranslation.description) : [],
        ru: ruTranslation.description ? JSON.parse(ruTranslation.description) : [],
      },
    },
    errors: {}
  }
},
  computed: {
    isEdit() {
      return !!this.category
    },
    headerTitle() {
      return this.isEdit ? 'Редагувати категорію' : 'Додати категорію'
    }
  },
  methods: {
    clearError(field) {
      this.errors[field] = null
    },
    async saveCategory() {
      try {
        await axios.put(`/admin/categories/${this.category.id}`, this.form);
        alert('Категорію успішно оновлено!');
        this.errors = {};
      } catch (error) {
        console.error('Помилка збереження категорії:', error);
        if (error.response && error.response.data.errors) {
          this.errors = error.response.data.errors;
        } else {
          alert('Невідома помилка при збереженні!');
        }
      }
    },
    saveDraft() {
      alert('Чернетка збережена!')
    },
    goBack() {
      window.history.back()
    }
  }
}
</script>
