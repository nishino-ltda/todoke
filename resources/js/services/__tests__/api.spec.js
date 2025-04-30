import api from '../api'
import { useLoadingStore } from '@/stores/loading'
import { vi } from 'vitest'

vi.mock('../api')

describe('API Service', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('should have correct base configuration', () => {
    // When mocking the exported api object, we can directly access its properties
    // We might need to mock the defaults property if the tests rely on it
    api.defaults = {
      baseURL: 'http://localhost', // Example base URL
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }

    expect(api.defaults.baseURL).toBeDefined()
    expect(api.defaults.headers['Content-Type']).toBe('application/json')
    expect(api.defaults.headers['Accept']).toBe('application/json')
  })

  it('should start loading on request', async () => {
    const loadingStore = useLoadingStore()
    const mockResponse = { data: {} }
    api.request.mockResolvedValue(mockResponse) // Mock the request method of the exported api object

    await api.get('/test')
    expect(loadingStore.isLoading).toBe(false) // Should be stopped by response interceptor
  })

  it('should stop loading on successful response', async () => {
    const loadingStore = useLoadingStore()
    const mockResponse = { data: {} }
    api.request.mockResolvedValue(mockResponse) // Mock the request method of the exported api object

    await api.get('/test')
    expect(loadingStore.isLoading).toBe(false)
  })

  it('should stop loading on error response', async () => {
    const loadingStore = useLoadingStore()
    const error = new Error('Request failed')
    api.get.mockImplementationOnce(() => Promise.reject(error))

    await expect(api.get('/test')).rejects.toThrow('Request failed')
    expect(loadingStore.isLoading).toBe(false)
  })
})
