<template>
  <div class="dropdown position-relative">
    <!-- Тригер -->
    <button
      type="button"
      class="nav-link dropdown-toggle d-flex align-items-center py-1 px-0"
      :aria-expanded="open.toString()"
      aria-haspopup="true"
      aria-label="Language select"
      @click="toggle"
      @keydown.down.prevent="open = true"
      @keydown.esc.prevent="open = false"
    >
      <img :src="flagUrl(currentLang)" :alt="currentLang.toUpperCase()" class="me-2" width="20" />
      <span class="fw-medium text-dark-emphasis d-none d-sm-inline">{{ currentLangLabel }}</span>
    </button>

    <!-- Меню (керуємо самі) -->
    <ul
      class="dropdown-menu dropdown-menu-end fs-sm"
      :class="{ show: open }"
      role="menu"
    >
      <li>
        <button type="button" class="dropdown-item d-flex align-items-center gap-2" @click="choose('uk')">
          <img :src="flagUrl('uk')" width="20" alt="Українська" />
          <span>Українська</span>
        </button>
      </li>
      <li>
        <button type="button" class="dropdown-item d-flex align-items-center gap-2" @click="choose('ru')">
          <img :src="flagUrl('ru')" width="20" alt="русский" />
          <span>русский</span>
        </button>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  // очікує payload як у тебе: { category: { translations: [{locale, slug}...] } | null }
  props: { category: { type: Object, default: null } },
  data() {
    const seg = (window.location.pathname.split('/')[1] || '').toLowerCase()
    return {
      currentLang: ['uk','ru'].includes(seg) ? seg : 'uk',
      open: false
    }
  },
  computed: {
    currentLangLabel() { return this.currentLang === 'uk' ? 'Українська' : 'русский' }
  },
  methods: {
    flagUrl(code) { return `/assets/img/flags/${code}.svg` }, // картинки лежать у /public/assets/img/flags
    toggle() { this.open = !this.open },
    closeOnOutside(e) { if (!this.$el.contains(e.target)) this.open = false },
    choose(lang) {
      this.open = false
      const t = this.category?.translations?.find(x => x.locale === lang)
      if (t?.slug) { window.location.href = `/${lang}/${t.slug}`; return }
      const parts = window.location.pathname.split('/')
      parts[1] = lang
      window.location.href = parts.join('/') + window.location.search + window.location.hash
    }
  },
  mounted() {
    document.addEventListener('click', this.closeOnOutside, true)
    document.addEventListener('keydown', e => { if (e.key === 'Escape') this.open = false })
  },
  beforeUnmount() {
    document.removeEventListener('click', this.closeOnOutside, true)
  }
}
</script>

<style scoped>
/* На випадок, якщо десь інший CSS переб’є — гарантуємо показ */
.dropdown-menu.show { display: block; }
</style>
