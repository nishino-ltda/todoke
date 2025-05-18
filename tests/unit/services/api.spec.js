import api from '@/services/api'
import { useLoadingStore } from '@/stores/loading'
import { setActivePinia, createPinia } from 'pinia'
import { describe, it, expect, beforeEach, vi } from 'vitest'

describe('API Service', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('should have correct base URL in development', () => {
    import.meta.env.DEV = true
    expect(api.defaults.baseURL).toBe('/api/v1')
  })

  it('should have correct base URL in production', () => {
    import.meta.env.DEV = false
    import.meta.env.VITE_API_URL = 'https://api.example.com'
    const prodApi = require('@/services/api').default
    expect(prodApi.defaults.baseURL).toBe('https://api.example.com')
  })

  it('should set loading state on request', async () => {
    const loadingStore = useLoadingStore()
    const mockAdapter = vi.fn().mockResolvedValue({ data: {} })
    api.interceptors.request.handlers[0].fulfilled({}, mockAdapter)

    expect(loadingStore.isLoading).toBe(true)
  })

  it('should clear loading state on successful response', async () => {
    const loadingStore = useLoadingStore()
    loadingStore.startLoading()
    
    const mockAdapter = vi.fn().mockResolvedValue({ data: {} })
    await api.interceptors.response.handlers[0].fulfilled({}, mockAdapter)

    expect(loadingStore.isLoading).toBe(false)
  })

  it('should clear loading state on error response', async () => {
    const loadingStore = useLoadingStore()
    loadingStore.startLoading()
    
    const mockAdapter = vi.fn().mockRejectedValue(new Error('Test error'))
    await api.interceptors.response.handlers[0].rejected(new Error('Test error'), mockAdapter)

    expect(loadingStore.isLoading).toBe(false)
  })

  it('should include proper headers', () => {
    expect(api.defaults.headers['Content-Type']).toBe('application/json')
    expect(api.defaults.headers['Accept']).toBe('application/json')
  })
})
