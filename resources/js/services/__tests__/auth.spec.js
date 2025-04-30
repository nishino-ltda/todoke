import authService from '../auth'
import { useAuthStore } from '@/stores/auth'
import { vi } from 'vitest'
import api from '@/services/api'
import { createPinia, setActivePinia } from 'pinia'

describe('Auth Service', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    setActivePinia(createPinia())
  })

  describe('login', () => {
    it('should call API and set auth on success', async () => {
      const mockResponse = {
        data: {
          user: { id: 1, name: 'Test User' },
          token: 'test-token'
        }
      }
      vi.spyOn(api, 'post').mockResolvedValue(mockResponse)

      const response = await authService.login({
        email: 'test@example.com',
        password: 'password'
      })

      expect(api.post).toHaveBeenCalledWith('/auth/login', {
        email: 'test@example.com',
        password: 'password'
      })
      expect(response).toEqual(mockResponse)
      expect(useAuthStore().user).toEqual(mockResponse.data.user)
      expect(useAuthStore().token).toBe(mockResponse.data.token)
    })

    it('should clear auth on failure', async () => {
      vi.spyOn(api, 'post').mockRejectedValue(new Error('Invalid credentials'))

      await expect(authService.login({
        email: 'test@example.com',
        password: 'wrong'
      })).rejects.toThrow('Invalid credentials')

      expect(useAuthStore().user).toBeNull()
      expect(useAuthStore().token).toBeNull()
    })
  })

  describe('register', () => {
    it('should call API and set auth on success', async () => {
      const mockResponse = {
        data: {
          user: { id: 1, name: 'Test User' },
          token: 'test-token'
        }
      }
      vi.spyOn(api, 'post').mockResolvedValue(mockResponse)

      const response = await authService.register({
        name: 'Test User',
        email: 'test@example.com',
        password: 'password'
      })

      expect(api.post).toHaveBeenCalledWith('/auth/register', {
        name: 'Test User',
        email: 'test@example.com',
        password: 'password'
      })
      expect(response).toEqual(mockResponse)
      expect(useAuthStore().user).toEqual(mockResponse.data.user)
      expect(useAuthStore().token).toBe(mockResponse.data.token)
    })

    it('should clear auth on failure', async () => {
      vi.spyOn(api, 'post').mockRejectedValue(new Error('Validation failed'))

      await expect(authService.register({
        name: 'Test User',
        email: 'invalid',
        password: 'password'
      })).rejects.toThrow('Validation failed')

      expect(useAuthStore().user).toBeNull()
      expect(useAuthStore().token).toBeNull()
    })
  })

  describe('logout', () => {
    it('should clear auth state', () => {
      const store = useAuthStore()
      store.setAuth({
        user: { id: 1, name: 'Test User' },
        token: 'test-token'
      })

      authService.logout()
      expect(store.user).toBeNull()
      expect(store.token).toBeNull()
    })
  })

  describe('getCurrentUser', () => {
    it('should return current user', () => {
      const store = useAuthStore()
      store.setAuth({
        user: { id: 1, name: 'Test User' },
        token: 'test-token'
      })

      expect(authService.getCurrentUser()).toEqual({ id: 1, name: 'Test User' })
    })
  })

  describe('isAuthenticated', () => {
    it('should return authentication state', () => {
      const store = useAuthStore()
      store.setAuth({
        user: { id: 1, name: 'Test User' },
        token: 'test-token'
      })

      expect(authService.isAuthenticated()).toBe(true)
    })
  })
})
