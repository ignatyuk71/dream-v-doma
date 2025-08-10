import { defineStore } from 'pinia'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [], // { id, name, price, quantity, image, link }
  }),

  getters: {
    // Надійна загальна сума
    subtotal: (state) =>
      state.items.reduce((sum, item) => {
        const price = parseFloat(item.price) || 0
        const qty = item.quantity || 0
        return sum + price * qty
      }, 0),

    // Загальна кількість товарів
    itemCount: (state) =>
      state.items.reduce((count, item) => count + item.quantity, 0),
  },

  actions: {
    addToCart(product) {
      const existing = this.items.find(i => i.id === product.id)
      if (existing) {
        existing.quantity += product.quantity || 1
      } else {
        this.items.push({ ...product, quantity: product.quantity || 1 })
      }
    },

    removeItem(id) {
      this.items = this.items.filter(item => item.id !== id)
    },

    clearCart() {
      this.items = []
    },

    increment(id) {
      const item = this.items.find(i => i.id === id)
      if (item) item.quantity++
    },

    decrement(id) {
      const item = this.items.find(i => i.id === id)
      if (item && item.quantity > 1) {
        item.quantity--
      }
    },
  },

  persist: true,
})
