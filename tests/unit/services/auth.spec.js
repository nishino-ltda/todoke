import authService from '@/services/auth'
import api from '@/services/api'
import { setActivePinia, createPinia } from 'pinia'
import { describe, it, expect, beforeEach, vi } from 'vitest'

describe('Auth Service', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('should login successfully', async () => {
    const mockResponse = {
      data: {
        user: { id: 1, name: 'Test User' },
        token: 'test-token'
      }
    }
    api.post.mockResolvedValue(mockResponse)

    const response = await authService.login({
      email: 'test@example.com',
      password: 'password'
    })

    expect(api.post).toHaveBeenCalledWith('/auth/login', {
      email: 'test@example.com',
      password: 'password'
    })
    expect(response).toEqual(mockResponse)
    expect(authService.getAuthStore().user).toEqual(mockResponse.data.user)
    expect(authService.getAuthStore().token).toBe(mockResponse.data.token)
  })

  it('should handle login failure', async () => {
    const mockError = new Error('Login failed')
    api.post.mockRejectedValue(mockError)

    await expect(authService.login({
      email: 'test@example.com',
      password: 'wrong'
    })).rejects.toThrow('Login failed')

    expect(authService.getAuthStore().user).toBeNull()
    expect(authService.getAuthStore().token).toBeNull()
  })

  it('should register successfully', async () => {
    const mockResponse = {
      data: {
        user: { id: 1, name: 'Test User' },
        token: 'test-token'
      }
    }
    api.post.mockResolvedValue(mockResponse)

    const response = await authService.register({
      name: 'Test User',
      email: 'test@example.com',
      password: 'password',
      password_confirmation: 'password'
    })

    expect(api.post).toHaveBeenCalledWith('/auth/register', {
      name: 'Test User',
      email: 'test@example.com',
      password: 'password',
      password_confirmation: 'password',
      role: 'customer'
    })
    expect(response).toEqual(mockResponse)
    expect(authService.getAuthStore().user).toEqual(mockResponse.data.user)
  })

  it('should register with custom endpoint', async () => {
    const mockResponse = { data: { user: { id: 1 }, token: 'test' } }
    api.post.mockResolvedValue(mockResponse)

    await authService.register({
      _endpoint: '/custom/register',
      name: 'Test',
      email: 'test@example.com',
      password: 'password',
      password_confirmation: 'password'
    })

    expect(api.post).toHaveBeenCalledWith('/custom/register', {
      name: 'Test',
      email: 'test@example.com',
      password: 'password',
      password_confirmation: 'password',
      role: 'customer'
    })
  })

  it('should logout', () => {
    const store = authService.getAuthStore()
    store.setAuth({
      user: { id: 1 },
      token: 'test-token'
    })
    
    authService.logout()
    expect(store.user).toBeNull()
    expect(store.token).toBeNull()
  })

  it('should get current user', () => {
    const store = authService.getAuthStore()
    store.user = { id: 1, name: 'Test User' }
    
    expect(authService.getCurrentUser()).toEqual({ id: 1, name: 'Test User' })
  })

  it('should check authentication status', () => {
    const store = authService.getAuthStore()
    store.isAuthenticated = true
    
    expect(authService.isAuthenticated()).toBe(true)
  })
})
