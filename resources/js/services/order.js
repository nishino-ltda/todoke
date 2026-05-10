import axios from 'axios'
import { useAuthStore } from '@/stores/auth'

const createOrder = async (orderData) => {
  const authStore = useAuthStore()

  try {
    const response = await axios.post('/api/v1/orders', orderData, {
      headers: {
        Authorization: `Bearer ${authStore.token}`,
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
      },
    })
    return response.data
  } catch (error) {
    if (error.response) {
      throw error.response.data
    }
    throw error
  }
}

const submitOrder = async (payload) => {
  return createOrder(payload)
}

export const useOrderApi = () => {
  return {
    createOrder,
    submitOrder,
  }
}
