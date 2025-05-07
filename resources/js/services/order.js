import axios from 'axios'
import { useAuthStore } from '@/stores/auth'

const createOrder = async (orderData) => {
  const authStore = useAuthStore()
  
  try {
    const response = await axios.post('/api/v1/orders', orderData, {
      headers: {
        Authorization: `Bearer ${authStore.token}`
      }
    })
    return response.data
  } catch (error) {
    if (error.response) {
      throw error.response.data
    }
    throw error
  }
}

export const useOrderApi = () => {
  return {
    createOrder
  }
}
