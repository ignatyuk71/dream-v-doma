<template>
  <div class="dropdown">
    <a class="nav-link dropdown-toggle d-flex align-items-center py-1 px-0"
       href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Language select">
      <div class="d-flex align-items-center">
        <img :src="currentFlag" :alt="currentLang.toUpperCase()" class="me-2" width="20">
        <span class="fw-medium text-dark-emphasis d-none d-sm-inline">{{ currentLangLabel }}</span>
      </div>
    </a>
    <ul class="dropdown-menu fs-sm" style="--cz-dropdown-spacer: .5rem">
      <li>
        <a class="dropdown-item" href="#" @click.prevent="changeLang('ua')">
          <img src="/public/assets/img/flags/ua.svg" class="flex-shrink-0 me-2" width="20" alt="Українська">
          Українська
        </a>
      </li>
      <li>
        <a class="dropdown-item" href="#" @click.prevent="changeLang('ru')">
          <img src="/public/assets/img/flags/ru.svg" class="flex-shrink-0 me-2" width="20" alt="русский">
          русский
        </a>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  data() {
    return {
      currentLang: window.location.pathname.split('/')[1] || 'ua',
      product: null,
      categoryTranslations: [],
      categorySlug: null
    }
  },
  computed: {
    currentFlag() {
      return `/assets/img/flags/${this.currentLang}.svg`
    },
    currentLangLabel() {
      return this.currentLang === 'ua' ? 'Українська' : 'русский'
    }
  },
  mounted() {
    // Продукт
    const el = document.getElementById('product-page')
    if (el && el.dataset.product) {
      try {
        this.product = JSON.parse(el.dataset.product)
        console.log('✅ Продукт зчитано:', this.product)
      } catch (e) {
        console.warn('❌ Не вдалося зчитати продукт', e)
      }
    }

    // Категорія
    const catEl = document.getElementById('category-page')
    if (catEl && catEl.dataset.translations) {
      try {
        this.categoryTranslations = JSON.parse(catEl.dataset.translations)
        this.categorySlug = catEl.dataset.slug
        console.log('✅ Категорія зчитана:', this.categorySlug)
      } catch (e) {
        console.warn('❌ Не вдалося зчитати категорію', e)
      }
    }
  },
  methods: {
    changeLang(lang) {
      // Якщо є продукт — редірект на правильний slug
      if (this.product?.translations) {
        const translated = this.product.translations.find(t => t.locale === lang)
        if (translated?.slug) {
          window.location.href = `/${lang}/product/${translated.slug}`
          return
        }
      }

      // Якщо є категорія — редірект на правильний slug
      if (this.categoryTranslations.length && this.categorySlug) {
        const translated = this.categoryTranslations.find(t => t.locale === lang)
        if (translated?.slug) {
          window.location.href = `/${lang}/category/${translated.slug}`
          return
        }
      }

      // Інакше просто змінюємо мову в URL
      const pathParts = window.location.pathname.split('/')
      pathParts[1] = lang
      const newUrl = pathParts.join('/') + window.location.search + window.location.hash
      window.location.href = newUrl
    }
  }
}
</script>
