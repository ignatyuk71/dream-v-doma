<template>
  <div class="p-4">
    <!-- Заголовок -->
    <ProductHeader @submit="submitForm" />

    <div class="row">
      <!-- Ліва колонка -->
      <div class="col-md-8">
        <ProductTitles :form="form" :errors="errors" />
        <ProductSeo :form="form" :errors="errors" />
        <ProductImages v-model="form.images" />
      </div>
      <!-- Права колонка -->
      <div class="col-md-4">
        <ProductPrice :form="form" :errors="errors" />
        <ProductCategory v-model="form.categories" :categories="categoryOptions" :errors="errors" />
        <ProductSizeGuide v-model="form.size_guide_id" :errors="errors" />
      </div>
    </div>
    <ProductDescription v-model="form.description" :errors="errors" />
    <ProductVariants v-model="form.variants" :errors="errors" />
    <ProductAttributes v-model="form.attributes" :errors="errors" /> 
    <ProductColors v-model="form.colors" :productList="productOptions" :errors="errors" />
  </div>
</template>

<script>
import axios from 'axios';
import ProductHeader from './sections/ProductHeader.vue';
import ProductTitles from './sections/ProductTitles.vue';
import ProductSeo from './sections/ProductSeo.vue';
import ProductImages from './sections/ProductImages.vue';
import ProductDescription from './sections/ProductDescription.vue';
import ProductPrice from './sections/ProductPrices.vue';
import ProductCategory from './sections/ProductCategory.vue';
import ProductSizeGuide from './sections/ProductSizeGuide.vue';
import ProductVariants from './sections/ProductVariants.vue';
import ProductAttributes from './sections/ProductAttributes.vue';
import ProductColors from './sections/ProductColors.vue';

export default {
  name: 'ProductForm',
  components: {
    ProductHeader,
    ProductTitles,
    ProductSeo,
    ProductImages,
    ProductDescription,
    ProductPrice,
    ProductCategory,
    ProductSizeGuide,
    ProductVariants,
    ProductAttributes,
    ProductColors,
  },
  data() {
    return {
      form: {
        name_uk: '',
        slug_uk: '',
        name_ru: '',
        slug_ru: '',
        meta_title_uk: '',
        meta_description_uk: '',
        meta_title_ru: '',
        meta_description_ru: '',
        price: '',
        old_price: '',
        sku: '',
        barcode: '',
        quantity_in_stock: '',
        is_available: false,
        is_popular: false,
        categories: [],
        variants: [],
        attributes: [],
        colors: [],
        description: { uk: [], ru: [] },  // опис з картинками!
        size_guide_id: '', 
        images: []
      },
      errors: {},
      categoryOptions: [
        { id: 1, name: 'Домашні тапочки' },
        { id: 2, name: 'Тапочки з хутром' },
        { id: 3, name: 'Модні тапочки' },
        { id: 4, name: 'Спортивні тапочки' },
      ],
      productOptions: [
        { id: 1, name: 'Товар 1' },
        { id: 2, name: 'Товар 2' },
        { id: 3, name: 'Товар 3' },
        { id: 4, name: 'Товар 4' },
      ]
    }
  },
  methods: {
    async submitForm() {
  this.errors = {}; // Очищуємо перед відправкою
  const formData = new FormData();
  formData.append('form', JSON.stringify(this.form));

  (this.form.images || []).forEach((img) => {
    if (img.file) {
      formData.append('images[]', img.file);
    }
  });

  formData.append('images_meta', JSON.stringify((this.form.images || []).map(img => ({
    position: img.position,
    is_main: img.is_main
  }))));

try {
        await axios.post('/admin/products', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
      } catch (err) {
        if (err.response && err.response.status === 422) {
          // Сюди потраплять помилки
          this.errors = Object.fromEntries(
            Object.entries(err.response.data.errors).map(([key, val]) => [key, val[0]])
          );
        } else {
          alert('Сталася помилка при збереженні!');
        }
      }
    }
  }
}
</script>

<style scoped>
.desc-img-preview {
  width: 200px;
  height: 200px;
  object-fit: cover;
  display: block;
  margin-top: 8px;
  border-radius: 8px;
}
</style>
