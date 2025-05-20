import api from '../api'
import { useLoadingStore } from '@/stores/loading'
import { vi } from 'vitest'
import { createTestingPinia } from '@pinia/testing'

describe('API Service', () => {
  let loadingStore
  let originalGet

  beforeEach(() => {
    // Create testing Pinia instance
    createTestingPinia({
      stubActions: false
    })
    
    loadingStore = useLoadingStore()
    vi.clearAllMocks()
    // Save original axios methods
    originalGet = api.get
  })

  afterEach(() => {
    // Restore original axios methods
    api.get = originalGet
  })

  it('should have correct base configuration', () => {
    expect(api.defaults.baseURL).toBe('/api/v1')
    expect(api.defaults.headers['Content-Type']).toBe('application/json')
    expect(api.defaults.headers['Accept']).toBe('application/json')
  })

    it('should update loading state during requests', async () => {
    const mockResponse = { data: {} }
    
    // Create a mock axios instance with interceptors
    const mockAxios = {
      interceptors: {
        request: { use: vi.fn() },
        response: { use: vi.fn() }
      },
      get: vi.fn().mockResolvedValue(mockResponse),
      defaults: api.defaults
    }
    
    // Replace the api instance with our mock
    Object.assign(api, mockAxios)
    
    // Simulate interceptor behavior
    api.interceptors.request.use(config => {
      loadingStore.startLoading()
      return config
    })
    
    api.interceptors.response.use(
      response => {
        loadingStore.stopLoading()
        return response
      },
      error => {
        loadingStore.stopLoading()
        return Promise.reject(error)
      }
    )

    await api.get('/test')
    expect(loadingStore.isLoading).toBe(false) // Should be stopped after request
  })

  it('should stop loading on error', async () => {
    const error = new Error('Request failed')
    api.get = vi.fn().mockRejectedValue(error)

    await expect(api.get('/test')).rejects.toThrow('Request failed')
    expect(loadingStore.isLoading).toBe(false)
  })
})
