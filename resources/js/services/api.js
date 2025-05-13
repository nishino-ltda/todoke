import axios from 'axios'
import { useLoadingStore } from '@/stores/loading'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Request interceptor
api.interceptors.request.use(config => {
  const loadingStore = useLoadingStore()
  loadingStore.startLoading()
  return config
})

// Response interceptor
api.interceptors.response.use(
  response => {
    const loadingStore = useLoadingStore()
    loadingStore.stopLoading()
    return response
  },
  error => {
    const loadingStore = useLoadingStore()
    loadingStore.stopLoading()
    return Promise.reject(error)
  }
)

export default api
