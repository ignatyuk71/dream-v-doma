<template>
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a :href="`/${currentLocale}`">
            {{ $t('home') }}
          </a>
        </li>
        <li v-if="category" class="breadcrumb-item">
          <a :href="`/${currentLocale}/category/${category.slug}`">
            {{ category.name }}
          </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
          {{ productName }}
        </li>
      </ol>
  </template>
  
  <script>
  export default {
    props: {
      product: {
        type: Object,
        required: true
      },
      currentLocale: {
        type: String,
        required: true
      }
    },
    computed: {
      productName() {
        const translation = this.product.translations?.find(t => t.locale === this.currentLocale)
        return translation?.name || this.product.translations?.[0]?.name || '...'
      },
      category() {
        const cat = this.product.categories?.[0]
        if (!cat || !cat.translations) return null
  
        const translation = cat.translations.find(t => t.locale === this.currentLocale)
        return {
          name: translation?.name || 'Категорія',
          slug: cat.slug
        }
      }
    }
  }
  </script>
  