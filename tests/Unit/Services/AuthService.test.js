import { describe, it, expect, vi, beforeEach } from 'vitest'
import authService from '@/services/auth'
import api from '@/services/api'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import { useLogStore } from '@/stores/log'

vi.mock('@/services/api', async (importOriginal) => {
  const actual = await importOriginal()
  return {
    ...actual,
    default: {
      post: vi.fn()
    }
  }
})

describe('AuthService', () => {
  let authStore, logStore

  beforeEach(() => {
    setActivePinia(createPinia())
    
    // Create actual Pinia stores with spies
    authStore = useAuthStore()
    authStore.setAuth = vi.fn()
    authStore.clearAuth = vi.fn()
    
    logStore = useLogStore()
    logStore.log = vi.fn()
    
    vi.clearAllMocks()
  })

  describe('login', () => {
    it('successfully logs in and updates auth store', async () => {
      const mockResponse = { data: { token: 'test-token', user: { id: 1 } } }
      api.post.mockResolvedValue(mockResponse)

      const credentials = { email: 'test@example.com', password: 'password' }
      const result = await authService.login(credentials)

      expect(api.post).toHaveBeenCalledWith('/login', credentials)
      expect(authStore.setAuth).toHaveBeenCalledWith(mockResponse.data)
      expect(logStore.log).toHaveBeenCalledWith('🔐 Attempting login')
      expect(logStore.log).toHaveBeenCalledWith('✅ Login successful')
      expect(result).toEqual(mockResponse.data)
    })

    it('handles login failure', async () => {
      const error = new Error('Login failed')
      api.post.mockRejectedValue(error)
      const logStore = useLogStore()

      await expect(authService.login({})).rejects.toThrow('Login failed. Please check your credentials.')
      expect(logStore.log).toHaveBeenCalledWith('❌ Login failed', error)
    })
  })

  describe('register', () => {
    it('successfully registers and updates auth store', async () => {
      const mockResponse = { data: { token: 'test-token', user: { id: 1 } } }
      api.post.mockResolvedValue(mockResponse)
      const authStore = useAuthStore()
      const logStore = useLogStore()

      const userData = { name: 'Test', email: 'test@example.com', password: 'password' }
      const result = await authService.register(userData)

      expect(api.post).toHaveBeenCalledWith('/register', userData)
      expect(authStore.setAuth).toHaveBeenCalledWith(mockResponse.data)
      expect(logStore.log).toHaveBeenCalledWith('📝 Attempting registration')
      expect(logStore.log).toHaveBeenCalledWith('✅ Registration successful')
      expect(result).toEqual(mockResponse.data)
    })

    it('handles registration failure', async () => {
      const error = new Error('Registration failed')
      api.post.mockRejectedValue(error)
      const logStore = useLogStore()

      await expect(authService.register({})).rejects.toThrow('Registration failed. Please try again.')
      expect(logStore.log).toHaveBeenCalledWith('❌ Registration failed', error)
    })
  })

  describe('logout', () => {
    it('successfully logs out and clears auth store', async () => {
      api.post.mockResolvedValue({})
      const authStore = useAuthStore()
      const logStore = useLogStore()

      await authService.logout()

      expect(api.post).toHaveBeenCalledWith('/logout')
      expect(authStore.clearAuth).toHaveBeenCalled()
      expect(logStore.log).toHaveBeenCalledWith('🚪 Attempting logout')
      expect(logStore.log).toHaveBeenCalledWith('✅ Logout successful')
    })

    it('handles logout failure', async () => {
      const error = new Error('Logout failed')
      api.post.mockRejectedValue(error)
      const logStore = useLogStore()

      await expect(authService.logout()).rejects.toThrow('Logout failed. Please try again.')
      expect(logStore.log).toHaveBeenCalledWith('❌ Logout failed', error)
    })
  })

  describe('refreshToken', () => {
    it('successfully refreshes token and updates auth store', async () => {
      const mockResponse = { data: { token: 'new-token', user: { id: 1 } } }
      api.post.mockResolvedValue(mockResponse)
      const authStore = useAuthStore()
      const logStore = useLogStore()

      const result = await authService.refreshToken()

      expect(api.post).toHaveBeenCalledWith('/refresh-token')
      expect(authStore.setAuth).toHaveBeenCalledWith(mockResponse.data)
      expect(logStore.log).toHaveBeenCalledWith('🔄 Attempting token refresh')
      expect(logStore.log).toHaveBeenCalledWith('✅ Token refresh successful')
      expect(result).toEqual(mockResponse.data)
    })

    it('handles token refresh failure', async () => {
      const error = new Error('Token refresh failed')
      api.post.mockRejectedValue(error)
      const logStore = useLogStore()

      await expect(authService.refreshToken()).rejects.toThrow('Session expired. Please login again.')
      expect(logStore.log).toHaveBeenCalledWith('❌ Token refresh failed', error)
    })
  })
})
