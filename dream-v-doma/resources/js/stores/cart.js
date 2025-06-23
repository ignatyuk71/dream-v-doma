import { defineStore } from 'pinia'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [],
  }),

  getters: {
    subtotal: (state) =>
      state.items.reduce((sum, item) => sum + item.price * item.quantity, 0),

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
      this.items = this.items.filter(i => i.id !== id)
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
      if (item && item.quantity > 1) item.quantity--
    },
  },

  persist: true,
})
