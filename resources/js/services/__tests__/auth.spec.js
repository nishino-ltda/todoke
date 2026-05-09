import authService from '../auth'
import api from '../api'
import { useAuthStore } from '../../stores/auth.js'
import { useLogStore } from '../../stores/log.js'
import { vi } from 'vitest'
import { createTestingPinia } from '@pinia/testing'

describe('Auth Service', () => {
  let authStore
  let logStore
  let mockResponse

  beforeEach(() => {
    createTestingPinia()
    authStore = useAuthStore()
    logStore = useLogStore()
    vi.clearAllMocks()
    
    mockResponse = {
      data: {
        token: 'test-token',
        user: { id: 1, name: 'Test User' }
      }
    }

    // Mock auth store methods
    authStore.setAuth = vi.fn()
    authStore.clearAuth = vi.fn()
    authStore.token = null
    authStore.user = null
  })

  describe('login', () => {
    it('should call API with credentials', async () => {
      api.post = vi.fn()
        .mockResolvedValueOnce(mockResponse)
        .mockResolvedValueOnce({})
      const credentials = { email: 'test@example.com', password: 'password' }
      
      await authService.login(credentials)
      
      expect(api.post).toHaveBeenCalledWith('/auth/login', credentials)
    })

    it('should set token and user on success', async () => {
      api.post = vi.fn()
        .mockResolvedValueOnce(mockResponse)
        .mockResolvedValueOnce({})
      const credentials = { email: 'test@example.com', password: 'password' }
      
      await authService.login(credentials)
      
      expect(authStore.setAuth).toHaveBeenCalledWith(mockResponse.data)
      expect(logStore.log).toHaveBeenCalledWith(`🔐 Login attempt for ${credentials.email}`)
      expect(logStore.log).toHaveBeenCalledWith(`✅ Login successful for ${credentials.email}`)
    })

    it('should throw error on failure', async () => {
      const error = new Error('Login failed')
      api.post = vi.fn().mockRejectedValue(error)
      
      await expect(authService.login({})).rejects.toThrow('Invalid credentials')
      expect(logStore.log).toHaveBeenCalledWith('❌ Login failed for undefined: Invalid credentials', 'error')
    })
  })

  describe('register', () => {
    it('should call API with user data', async () => {
      api.post = vi.fn().mockResolvedValue(mockResponse)
      const userData = { name: 'New User', email: 'new@example.com', password: 'password' }
      
      await authService.register(userData)
      
      expect(api.post).toHaveBeenCalledWith('/auth/register', userData)
    })

    it('should set token and user on success', async () => {
      api.post = vi.fn().mockResolvedValue(mockResponse)
      
      await authService.register({})
      
      expect(authStore.setAuth).toHaveBeenCalledWith(mockResponse.data)
      expect(logStore.log).toHaveBeenCalledWith('📝 Attempting registration')
      expect(logStore.log).toHaveBeenCalledWith('✅ Registration successful')
    })

    it('should throw error on failure', async () => {
      const error = new Error('Registration failed')
      api.post = vi.fn().mockRejectedValue(error)
      
      await expect(authService.register({})).rejects.toThrow('Registration failed. Please try again.')
      expect(logStore.log).toHaveBeenCalledWith('❌ Registration failed', error)
    })
  })

  describe('logout', () => {
    it('should call API logout endpoint', async () => {
      api.post = vi.fn().mockResolvedValue({})
      
      await authService.logout()
      
      expect(api.post).toHaveBeenCalledWith('/auth/logout')
      expect(logStore.log).toHaveBeenCalledWith('🚪 Attempting logout')
      expect(logStore.log).toHaveBeenCalledWith('✅ Logout successful')
    })

    it('should clear auth store', async () => {
      api.post = vi.fn().mockResolvedValue({})
      authStore.token = 'test-token'
      authStore.user = { id: 1 }
      
      await authService.logout()
      
      expect(authStore.clearAuth).toHaveBeenCalled()
      expect(logStore.log).toHaveBeenCalledWith('🚪 Attempting logout')
      expect(logStore.log).toHaveBeenCalledWith('✅ Logout successful')
    })
  })

  describe('refreshToken', () => {
    it('should call API refresh endpoint', async () => {
      api.post = vi.fn().mockResolvedValue(mockResponse)
      
      await authService.refreshToken()
      
      expect(api.post).toHaveBeenCalledWith('/refresh-token')
      expect(logStore.log).toHaveBeenCalledWith('🔄 Attempting token refresh')
      expect(logStore.log).toHaveBeenCalledWith('✅ Token refresh successful')
    })

    it('should set new token on success', async () => {
      api.post = vi.fn().mockResolvedValue(mockResponse)
      
      await authService.refreshToken()
      
      expect(authStore.setAuth).toHaveBeenCalledWith(mockResponse.data)
      expect(logStore.log).toHaveBeenCalledWith('🔄 Attempting token refresh')
      expect(logStore.log).toHaveBeenCalledWith('✅ Token refresh successful')
    })

    it('should throw error on failure', async () => {
      const error = new Error('Refresh failed')
      api.post = vi.fn().mockRejectedValue(error)
      
      await expect(authService.refreshToken()).rejects.toThrow('Session expired. Please login again.')
      expect(logStore.log).toHaveBeenCalledWith('❌ Token refresh failed', error)
    })
  })
})
