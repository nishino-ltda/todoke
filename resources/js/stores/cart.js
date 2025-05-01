import { defineStore } from 'pinia'
import { ref, computed, watchEffect } from 'vue'

export const useCartStore = defineStore('cart', () => {
  const items = ref([])
  
  // Load initial state from localStorage
  try {
    const saved = localStorage.getItem('cart')
    if (saved) {
      items.value = JSON.parse(saved)
    }
  } catch (e) {
    console.error('Failed to load cart from localStorage', e)
  }

  // Save state to localStorage whenever items change
  watchEffect(() => {
    try {
      localStorage.setItem('cart', JSON.stringify(items.value))
    } catch (e) {
      console.error('Failed to save cart to localStorage', e)
    }
  })

  const count = computed(() => items.value.length)
  const total = computed(() => 
    items.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
  )

  function addItem(product) {
    const existing = items.value.find(item => item.id === product.id)
    if (existing) {
      existing.quantity++
    } else {
      items.value.push({ ...product, quantity: 1 })
    }
  }

  function removeItem(productId) {
    const index = items.value.findIndex(item => item.id === productId)
    if (index !== -1) {
      items.value.splice(index, 1)
    }
  }

  function clearCart() {
    items.value = []
  }

  return { 
    items,
    count,
    total,
    addItem,
    removeItem,
    clearCart
  }
})
