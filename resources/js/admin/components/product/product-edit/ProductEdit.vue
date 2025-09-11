<template>
  <div class="p-4">
    <ProductHeader @submit="submitForm" />

    <div class="row">
      <div class="col-md-8">
        <ProductTitles :form="form" :errors="errors" />
        <ProductSeo :form="form" :errors="errors" />
        <ProductImages v-model="form.images" />
      </div>
      <div class="col-md-4">
        <ProductPrice
          :form="form"
          :errors="errors"
          @update:form="updateForm"
        />
        <ProductCategory
          v-model="form.categories"
          :categories="categoryOptions"
          :errors="errors"
        />
        <ProductSizeGuide v-model="form.size_guide_id" :errors="errors" />
      </div>
    </div>

    <ProductDescription v-model="form.description" :product-id="form.id || ''" />
    <ProductVariants v-model="form.variants" :errors="errors" />
    <ProductAttributes v-model="form.attributes" :errors="errors" />
    <ProductColors v-model="form.colors" :product-id="form.id || ''" :errors="errors" />
  </div>

  <!-- ✅ Повноекранний оверлей зі спінером -->
  <GlobalLoadingOverlay
    :active="loading || saving"
    :label="loading ? 'Завантажуємо форму…' : 'Публікуємо товар…'"
    :hint="saving ? 'Будь ласка, не закривайте сторінку' : ''"
  />
</template>

<script>
import axios from 'axios'
import GlobalLoadingOverlay from '@/admin/components/common/GlobalLoadingOverlay.vue'

import ProductHeader from './sections/ProductHeader.vue'
import ProductTitles from './sections/ProductTitles.vue'
import ProductSeo from './sections/ProductSeo.vue'
import ProductImages from './sections/ProductImages.vue'
import ProductDescription from './sections/ProductDescription.vue'
import ProductPrice from './sections/ProductPrices.vue'
import ProductCategory from './sections/ProductCategory.vue'
import ProductSizeGuide from './sections/ProductSizeGuide.vue'
import ProductVariants from './sections/ProductVariants.vue'
import ProductAttributes from './sections/ProductAttributes.vue'
import ProductColors from './sections/ProductColors.vue'

export default {
  name: 'ProductEdit',
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
    GlobalLoadingOverlay
  },
  props: {
    productId: { type: [Number, String], required: true }
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
        status: false,
        is_popular: false,
        categories: [],
        variants: [],
        attributes: [],
        colors: [],
        description: { uk: [], ru: [] },
        size_guide_id: '',
        images: []
      },
      errors: {},
      categoryOptions: [],
      productOptions: [],
      loading: true,
      saving: false // ✅ додано для оверлею
    }
  },
  async created() {
    await this.fetchCategories()
    await this.fetchProduct()
    await this.fetchProductOptions()
    this.loading = false
  },
  methods: {
    updateForm(newForm) {
      if (newForm.categories) {
        newForm.categories = [...new Set(newForm.categories)]
      }
      this.form = { ...this.form, ...newForm }
    },
    async fetchProduct() {
      try {
        const { data } = await axios.get(`/admin/products/${this.productId}/edit`)
        const p = data.product

        const attrs = data.attributes || { uk: [], ru: [] }
        const ukTranslation = p.translations.find(t => t.locale === 'uk') || {}
        const ruTranslation = p.translations.find(t => t.locale === 'ru') || {}

        Object.assign(this.form, {
          id: p.id,
          name_uk: ukTranslation.name || '',
          slug_uk: ukTranslation.slug || '',
          meta_title_uk: ukTranslation.meta_title || '',
          meta_description_uk: ukTranslation.meta_description || '',
          name_ru: ruTranslation.name || '',
          slug_ru: ruTranslation.slug || '',
          meta_title_ru: ruTranslation.meta_title || '',
          meta_description_ru: ruTranslation.meta_description || '',
          old_price: p.old_price,
          price: p.price,
          sku: p.sku,
          barcode: p.barcode,
          quantity_in_stock: p.quantity_in_stock,
          status: Boolean(p.status),
          categories: p.categories ? p.categories.map(c => c.id) : [],
          size_guide_id: p.size_guide_id,
          images: p.images || [],
          variants: p.variants || [],
          attributes: attrs,
          colors: p.colors || [],
          description: {
            uk: ukTranslation.description ? JSON.parse(ukTranslation.description) : [],
            ru: ruTranslation.description ? JSON.parse(ruTranslation.description) : []
          }
        })
      } catch (e) {
        alert('Помилка завантаження продукту')
      }
    },
    async fetchCategories() {
      try {
        const { data } = await axios.get('/api/category-select/uk')
        this.categoryOptions = data
      } catch (e) {
        this.categoryOptions = []
      }
    },
    async fetchProductOptions() {
      try {
        const response = await axios.get('/admin/products/list')
        const data = response.data
        this.productOptions = Array.isArray(data) ? data : (data.products || [])
      } catch (e) {
        this.productOptions = []
      }
    },
    async submitForm() {
      if (this.saving) return
      this.errors = {}
      this.form.categories = [...new Set(this.form.categories)]

      const formData = new FormData()
      formData.append('form', JSON.stringify(this.form))

      ;(this.form.images || []).forEach((img) => {
        if (img.file) {
          formData.append('images[]', img.file)
        }
      })

      const imagesMeta = (this.form.images || []).map(img => ({
        position: img.position,
        is_main: img.is_main,
        url: img.url || null
      }))
      formData.append('images_meta', JSON.stringify(imagesMeta))

      try {
        this.saving = true // ✅ показати оверлей

        await axios.post(
          `/admin/products/${this.productId}?_method=PUT`,
          formData,
          { headers: { 'Content-Type': 'multipart/form-data' } }
        )

        alert('Товар успішно оновлено!')
        window.location.href = '/admin/products'
      } catch (err) {
        if (err.response && err.response.status === 422) {
          this.errors = Object.fromEntries(
            Object.entries(err.response.data.errors).map(([key, val]) => [key, val[0]])
          )
        } else {
          alert('Сталася помилка при збереженні!')
        }
      } finally {
        this.saving = false // ✅ сховати оверлей
      }
    }
  }
}
</script>
