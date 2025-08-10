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
        <a class="dropdown-item" href="#" @click.prevent="changeLang('uk')">
          <img src="/public/assets/img/flags/uk.svg" class="flex-shrink-0 me-2" width="20" alt="Українська">
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
  props: {
    category: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      currentLang: window.location.pathname.split('/')[1] || 'uk'
    }
  },
  computed: {
    currentFlag() {
      return `/assets/img/flags/${this.currentLang}.svg`
    },
    currentLangLabel() {
      return this.currentLang === 'uk' ? 'Українська' : 'русский'
    }
  },
  methods: {
    changeLang(lang) {
      // DEBUG: показуємо всі переклади
      console.log('category.translations:', this.category?.translations);

      if (this.category?.translations?.length) {
        const translated = this.category.translations.find(t => t.locale === lang)
        // DEBUG: показуємо знайдений переклад
        console.log('Шукаємо slug для мови:', lang, '| Знайшли:', translated);

        if (translated?.slug) {
          window.location.href = `/${lang}/${translated.slug}`
          return
        }
      }

      // fallback: просто змінити lang у url
      const pathParts = window.location.pathname.split('/')
      pathParts[1] = lang
      const newUrl = pathParts.join('/') + window.location.search + window.location.hash
      window.location.href = newUrl
    }
  },
  mounted() {
    // DEBUG: показати всі дані при завантаженні компонента
    console.log('Category prop при mount:', this.category);
  }
}
</script>
