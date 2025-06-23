import { createI18n } from 'vue-i18n'
import ua from './lang/ua.json'
import ru from './lang/ru.json'

const urlLocale = window.location.pathname.split('/')[1]
const supportedLocales = ['ua', 'ru']
const defaultLocale = 'ua'

const lang = supportedLocales.includes(urlLocale) ? urlLocale : defaultLocale
localStorage.setItem('lang', lang)

export default createI18n({
  legacy: false,
  locale: lang,
  fallbackLocale: 'ua',
  messages: { ua, ru }
})
