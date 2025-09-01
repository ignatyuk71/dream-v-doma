// stores/cart.js (або cart.ts)
import { defineStore } from 'pinia'

/* ===== утиліти ===== */
const DEBUG = false
const log = (...a)=>DEBUG && console.log('[ATC]', ...a)

const num = v => {
  const x = parseFloat(String(v ?? 0).replace(',', '.').replace(/[^\d.\-]/g,''))
  return Number.isFinite(x) ? Number(x.toFixed(2)) : 0
}

/* id товару/варіанта для Meta (content_id) */
const pidOf = p =>
  String(p?.variant_sku ?? p?.sku ?? p?.product_variant_id ?? p?.id ?? p?.product_id ?? '')

/* ціна варіанта з пріоритетом; фолбек на product.price */
const priceOf = p => {
  const candidates = [
    p?.price_override,
    p?.variant_price,
    p?.variant?.price_override,
    p?.variant?.price,
    ...(Array.isArray(p?.variants) && p?.id ? (() => {
      const v = p.variants.find(v =>
        String(v.id) === String(p.id) ||
        (p.sku && v.sku === p.sku) ||
        (p.size && v.size === p.size)
      )
      return v ? [v.price_override, v.price] : []
    })() : []),
    p?.price
  ]
  for (const c of candidates) { const n = num(c); if (n > 0) return n }
  return num(p?.price)
}

export const useCartStore = defineStore('cart', {
  state: () => ({ items: [] }),

  getters: {
    subtotal: s => s.items.reduce((sum,i)=> sum + num(i.price)*Number(i.quantity||0), 0),
    itemCount: s => s.items.reduce((c,i)=> c + Number(i.quantity||0), 0),
  },

  actions: {
    async addToCart(p) {
      const qty  = Number(p.quantity || 1)
      const unit = priceOf(p)

      // 1) оновлюємо локальний кошик
      const existing = this.items.find(i => i.id === p.id)
      if (existing) existing.quantity = Number(existing.quantity || 1) + qty
      else this.items.push({ ...p, price: unit, quantity: qty })

      // 2) трекінг — лише через паршал (mpTrackATC)
      if (window._mpFlags?.atc === false) return

      const id       = pidOf(p)
      const currency = window.metaPixelCurrency || 'UAH'
      const name     = typeof p?.name === 'string' ? p.name : '' // якщо назва передана з Vue

      if (!id) { if (DEBUG) console.warn('[ATC] no content_id'); return }

      if (typeof window.mpTrackATC === 'function') {
        const payload = { sku: id, price: unit, quantity: qty, name, currency }
        log('mpTrackATC ->', payload)
        window.mpTrackATC(payload) // далі паршал відправляє Pixel+CAPI з одним event_id
      } else if (DEBUG) {
        console.warn('[ATC] window.mpTrackATC is not defined. Підключи partial meta-pixel-add-to-cart.blade.php')
      }
    },

    removeItem(id){ this.items = this.items.filter(i => i.id !== id) },
    clearCart(){ this.items = [] },
    increment(id){ const it=this.items.find(i=>i.id===id); if (it) it.quantity = Number(it.quantity||1)+1 },
    decrement(id){ const it=this.items.find(i=>i.id===id); if (it && Number(it.quantity)>1) it.quantity = Number(it.quantity)-1 },
  },

  persist: true,
})
