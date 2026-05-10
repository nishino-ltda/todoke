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

  const subtotal = computed(() => total.value)
  const deliveryFee = computed(() => 5.00)
  const totalWithDelivery = computed(() => subtotal.value + deliveryFee.value)

  function addItem(product) {
    const existing = items.value.find(item =>
      item.id === product.id &&
      JSON.stringify(item.selectedAddons) === JSON.stringify(product.selectedAddons)
    )
    if (existing) {
      existing.quantity++
    } else {
      items.value.push({
        ...product,
        quantity: 1,
        selectedAddons: product.selectedAddons || []
      })
    }
  }

  function incrementQuantity(index) {
    if (items.value[index]) {
      items.value[index].quantity++
    }
  }

  function decrementQuantity(index) {
    if (items.value[index]) {
      if (items.value[index].quantity > 1) {
        items.value[index].quantity--
      } else {
        items.value.splice(index, 1)
      }
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

  async function submitOrder(orderData) {
    try {
      const response = await fetch('/api/v1/orders', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        body: JSON.stringify(orderData)
      });
      
      const data = await response.json();
      
      if (!response.ok) {
        // If we have validation errors
        if (response.status === 422 && data.errors) {
          const error = new Error('Validation failed');
          error.response = { data };
          throw error;
        }
        
        throw new Error(data.message || 'Failed to submit order');
      }
      
      return { success: true, data };
    } catch (error) {
      console.error('Error submitting order:', error);
      throw error;
    }
  }

  return {
    items,
    count,
    total,
    subtotal,
    deliveryFee,
    totalWithDelivery,
    addItem,
    incrementQuantity,
    decrementQuantity,
    removeItem,
    clearCart,
    submitOrder,
  }
})
