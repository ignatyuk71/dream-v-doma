import axios from 'axios'
import { defineStore } from 'pinia'

/* ===== мінімальні утиліти ===== */
const DEBUG = false
const log = (...a)=>DEBUG && console.log('[ATC]', ...a)
const num = v => {
  const x = parseFloat(String(v ?? 0).replace(',', '.').replace(/[^\d.]/g,''))
  return Number.isFinite(x) ? Number(x.toFixed(2)) : 0
}
const getCookie = n => document.cookie.split('; ').find(r=>r.startsWith(n+'='))?.split('=')[1] || null
const fbp = () => {
  const fromCookie = getCookie('_fbp')
  if (fromCookie) return fromCookie
  let gen = localStorage.getItem('fbp_generated')
  if (!gen) {
    gen = `fb.1.${Math.floor(Date.now()/1000)}.${Math.floor(Math.random()*1e10)}`
    localStorage.setItem('fbp_generated', gen)
  }
  return gen
}
const fbc = () => getCookie('_fbc')

/* id товару/варіанта для Meta */
const pidOf = p => String(p?.variant_sku ?? p?.sku ?? p?.product_variant_id ?? p?.id ?? p?.product_id ?? '')

/* ціна ВАРІАНТА з пріоритетом; фолбек на product.price */
const priceOf = p => {
  const candidates = [
    p?.price_override,
    p?.variant_price,
    p?.variant?.price_override,
    p?.variant?.price,
    ...(Array.isArray(p?.variants) && p?.id
      ? (() => {
          const v = p.variants.find(v =>
            String(v.id) === String(p.id) ||
            (p.sku && v.sku === p.sku) ||
            (p.size && v.size === p.size)
          )
          return v ? [v.price_override, v.price] : []
        })()
      : []),
    p?.price
  ]
  for (const c of candidates) { const n = num(c); if (n > 0) return n }
  return num(p?.price) // може бути 0 — тоді це видно у звіті
}

/* Pixel */
const sendPixelATC = (payload, eventId) => {
  if (window._mpFlags?.atc === false) return
  if (typeof window.fbq !== 'function') return
  window.fbq('track', 'AddToCart', payload, { eventID: eventId })
}

/* CAPI */
const sendCapiATC = async payload => {
  try { await axios.post('/api/track/atc', payload) }
  catch (e) { if (DEBUG) console.warn('CAPI ATC', e?.response?.data || e?.message) }
}

export const useCartStore = defineStore('cart', {
  state: () => ({ items: [] }),

  getters: {
    subtotal: s => s.items.reduce((sum,i)=> sum + num(i.price)*Number(i.quantity||0), 0),
    itemCount: s => s.items.reduce((c,i)=> c + Number(i.quantity||0), 0),
  },

  actions: {
    async addToCart(p) {
      const qty = Number(p.quantity || 1)
      const unit = priceOf(p)

      const existing = this.items.find(i => i.id === p.id)
      if (existing) existing.quantity = Number(existing.quantity || 1) + qty
      else this.items.push({ ...p, price: unit, quantity: qty }) // зберігаємо РЕАЛЬНУ ціну варіанта

      if (window._mpFlags?.atc === false) return

      const id = pidOf(p)
      const currency = window.metaPixelCurrency || 'UAH'
      const contents = [{ id, quantity: qty, item_price: unit }]
      const value = Number((unit * qty).toFixed(2))
      const eventId = `atc-${id}-${Date.now()}`

      const pixelPayload = { content_ids:[id], content_type:'product', contents, value, currency }
      log('send', { eventId, pixelPayload })

      sendPixelATC(pixelPayload, eventId)

      await sendCapiATC({
        event_id: eventId,
        event_time: Math.floor(Date.now()/1000),
        event_source_url: window.location.href,
        value, currency, contents,
        fbp: fbp(), fbc: fbc(),
      })
    },

    removeItem(id){ this.items = this.items.filter(i => i.id !== id) },
    clearCart(){ this.items = [] },
    increment(id){ const it=this.items.find(i=>i.id===id); if (it) it.quantity = Number(it.quantity||1)+1 },
    decrement(id){ const it=this.items.find(i=>i.id===id); if (it && Number(it.quantity)>1) it.quantity = Number(it.quantity)-1 },
  },

  persist: true,
})
