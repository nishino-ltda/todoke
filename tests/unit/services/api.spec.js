import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import api from '@/services/api'
import { useLoadingStore } from '@/stores/loading'

vi.mock('@/services/api', async (importOriginal) => {
  const actual = await importOriginal()
  return {
    default: {
      ...actual.default,
      defaults: {
        baseURL: '/api/v1',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      },
      interceptors: {
        request: {
          handlers: []
        },
        response: {
          handlers: []
        }
      }
    }
  }
})

describe('API Service', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('should have correct base URL in development', () => {
    import.meta.env.DEV = true
    expect(api.defaults.baseURL).toBe('/api/v1')
  })

  it('should have correct base URL in production', async () => {
    import.meta.env.DEV = false
    import.meta.env.VITE_API_URL = 'https://api.example.com'
    const { default: prodApi } = await import('@/services/api')
    expect(prodApi.defaults.baseURL).toBe('https://api.example.com')
  })

  it('should set loading state on request', async () => {
    const loadingStore = useLoadingStore()
    api.get('/test')
    expect(loadingStore.isLoading).toBe(true)
  })

  it('should clear loading state on successful response', async () => {
    const loadingStore = useLoadingStore()
    api.get = vi.fn().mockResolvedValue({ data: {} })
    await api.get('/test')
    expect(loadingStore.isLoading).toBe(false)
  })

  it('should clear loading state on error response', async () => {
    const loadingStore = useLoadingStore()
    api.get = vi.fn().mockRejectedValue(new Error('Test error'))
    await expect(api.get('/test')).rejects.toThrow('Test error')
    expect(loadingStore.isLoading).toBe(false)
  })

  it('should include proper headers', () => {
    expect(api.defaults.headers['Content-Type']).toBe('application/json')
    expect(api.defaults.headers['Accept']).toBe('application/json')
  })
})
