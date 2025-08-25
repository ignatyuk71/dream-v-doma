import { createI18n } from 'vue-i18n'
import uk from './lang/uk.json'
import ru from './lang/ru.json'

const urlLocale = window.location.pathname.split('/')[1]
const supportedLocales = ['uk', 'ru']
const defaultLocale = 'uk'

const lang = supportedLocales.includes(urlLocale) ? urlLocale : defaultLocale
localStorage.setItem('lang', lang)

export default createI18n({
  legacy: false,
  locale: lang,
  fallbackLocale: 'uk',
  messages: { uk, ru }
})
