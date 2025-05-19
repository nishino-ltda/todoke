import { createPinia, setActivePinia } from 'pinia'
import { useAuthStore } from '../auth'
import { vi } from 'vitest'
import api from '@/services/api'

describe('Auth Store', () => {
  beforeEach(() => {
    // Create a fresh Pinia instance and make it active
    setActivePinia(createPinia())
    localStorage.clear()
  })

  afterEach(() => {
    vi.restoreAllMocks()
  })

  it('should initialize with default state', () => {
    const store = useAuthStore()
    expect(store.user).toBeNull()
    expect(store.token).toBeNull()
    expect(store.isAuthenticated).toBe(false)
  })

  it('should set auth data', () => {
    const store = useAuthStore()
    const authData = {
      user: { id: 1, name: 'Test User' },
      token: 'test-token'
    }

    store.setAuth(authData)
    expect(store.user).toEqual(authData.user)
    expect(store.token).toBe(authData.token)
    expect(store.isAuthenticated).toBe(true)
    expect(localStorage.getItem('token')).toBe(authData.token)
  })

  it('should clear auth data on logout', () => {
    const store = useAuthStore()
    store.setAuth({
      user: { id: 1, name: 'Test User' },
      token: 'test-token'
    })

    store.logout()
    expect(store.user).toBeNull()
    expect(store.token).toBeNull()
    expect(store.isAuthenticated).toBe(false)
    expect(localStorage.getItem('token')).toBeNull()
  })

  describe('login', () => {
    it('should set auth data on successful login', async () => {
      const store = useAuthStore()
      const credentials = { email: 'test@example.com', password: 'password' }
      const mockResponse = {
        data: {
          user: { id: 1, name: 'Test User' },
          token: 'test-token'
        }
      }

      vi.spyOn(api, 'post').mockResolvedValue(mockResponse)

      await store.login(credentials)
      expect(store.user).toEqual(mockResponse.data.user)
      expect(store.token).toBe(mockResponse.data.token)
      expect(store.isAuthenticated).toBe(true)
    })

    it('should clear auth data on failed login', async () => {
      const store = useAuthStore()
      const credentials = { email: 'test@example.com', password: 'wrong' }

      vi.spyOn(api, 'post').mockRejectedValue(new Error('Invalid credentials'))

      await expect(store.login(credentials)).rejects.toThrow('Invalid credentials')
      expect(store.user).toBeNull()
      expect(store.token).toBeNull()
      expect(store.isAuthenticated).toBe(false)
    })
  })

  describe('register', () => {
    it('should set auth data on successful registration', async () => {
      const store = useAuthStore()
      const userData = { name: 'Test User', email: 'test@example.com', password: 'password' }
      const mockResponse = {
        data: {
          user: { id: 1, name: 'Test User' },
          token: 'test-token'
        }
      }

      vi.spyOn(api, 'post').mockResolvedValue(mockResponse)

      await store.register(userData)
      expect(store.user).toEqual(mockResponse.data.user)
      expect(store.token).toBe(mockResponse.data.token)
      expect(store.isAuthenticated).toBe(true)
    })

    it('should clear auth data on failed registration', async () => {
      const store = useAuthStore()
      const userData = { name: 'Test User', email: 'invalid', password: 'password' }

      vi.spyOn(api, 'post').mockRejectedValue(new Error('Validation failed'))

      await expect(store.register(userData)).rejects.toThrow('Validation failed')
      expect(store.user).toBeNull()
      expect(store.token).toBeNull()
      expect(store.isAuthenticated).toBe(false)
    })
  })
})
