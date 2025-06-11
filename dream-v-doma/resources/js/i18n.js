import { createI18n } from 'vue-i18n'
import ua from './lang/ua.json'
import ru from './lang/ru.json'

const lang = localStorage.getItem('lang') || 'ua'

export default createI18n({
  legacy: false,
  locale: lang,
  fallbackLocale: 'ua',
  messages: {
    ua,
    ru
  }
})
