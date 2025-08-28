import { defineStore } from 'pinia'

// ===== Meta Pixel helpers ==============================================
function sanitizePrice(p) {
  const cleaned = String(p).replace(',', '.').replace(/[^\d.]/g, '')
  const num = parseFloat(cleaned)
  return Number.isFinite(num) ? Number(num.toFixed(2)) : 0
}

function sendMetaAddToCart(item) {
  try {
    const pid = String(item.sku || item.id || item.product_id)
    const qty = Number(item.quantity || 1)
    const price = sanitizePrice(item.price)
    const currency = window.metaPixelCurrency || 'UAH'

    const payload = {
      content_ids: [pid],
      content_type: 'product',
      contents: [{ id: pid, quantity: qty, item_price: price }],
      value: Number((price * qty).toFixed(2)),
      currency
    }

    console.log('[MetaPixel][store] AddToCart payload', payload)

    if (window.fbq) {
      window.fbq('track', 'AddToCart', payload)
      console.log('[MetaPixel][store] sent')
    } else {
      console.warn('[MetaPixel][store] fbq not found')
    }
  } catch (e) {
    console.error('[MetaPixel][store] error', e)
  }
}
// =======================================================================

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: []
    // структура елемента:
    // {
    //   id: variantId,       // ключ = id варіанта
    //   product_id: 123,     // базовий товар
    //   name: 'Назва товару',
    //   price: 500,
    //   quantity: 1,
    //   image: 'url',
    //   link: '/uk/product/slug',
    //   size: '36–37',
    //   color: 'Чорний'
    // }
  }),

  getters: {
    // Загальна сума
    subtotal: (state) =>
      state.items.reduce((sum, item) => {
        const price = sanitizePrice(item.price)
        const qty = Number(item.quantity || 0)
        return sum + price * qty
      }, 0),

    // Загальна кількість товарів
    itemCount: (state) =>
      state.items.reduce((count, item) => count + Number(item.quantity || 0), 0),
  },

  actions: {
    addToCart(product) {
      // product.id = variant.id
      const qty = Number(product.quantity || 1)
      const existing = this.items.find(i => i.id === product.id)

      if (existing) {
        existing.quantity = Number(existing.quantity || 1) + qty
      } else {
        this.items.push({ ...product, quantity: qty })
      }

      // Meta Pixel: централізовано після успішного додавання
      sendMetaAddToCart({ ...product, quantity: qty })
    },

    removeItem(id) {
      // id = variant.id
      this.items = this.items.filter(item => item.id !== id)
    },

    clearCart() {
      this.items = []
    },

    increment(id) {
      const item = this.items.find(i => i.id === id)
      if (item) item.quantity = Number(item.quantity || 1) + 1
    },

    decrement(id) {
      const item = this.items.find(i => i.id === id)
      if (item && Number(item.quantity) > 1) {
        item.quantity = Number(item.quantity) - 1
      }
    },
  },

  persist: true,
})
