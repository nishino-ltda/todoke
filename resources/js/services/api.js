import axios from 'axios'
import { useLoadingStore } from '@/stores/loading'

// In development, use relative URLs to leverage Vite's proxy
// In production, use the VITE_API_URL from environment variables
const baseURL = import.meta.env.DEV 
  ? '/api/v1'
  : (import.meta.env.VITE_API_URL || '/api/v1')

const api = axios.create({
  baseURL,
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
