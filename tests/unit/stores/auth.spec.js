import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

// Mock localStorage
const localStorageMock = (() => {
  let store = {}
  return {
    getItem: vi.fn((key) => store[key] || null),
    setItem: vi.fn((key, value) => { store[key] = value.toString() }),
    removeItem: vi.fn((key) => { delete store[key] }),
    clear: vi.fn(() => { store = {} })
  }
})()

// Mock the api service
vi.mock('@/services/api', () => ({
  default: {
    post: vi.fn()
  }
}))

// Mock global localStorage
global.localStorage = localStorageMock

describe('Auth Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    localStorage.clear()
    vi.clearAllMocks()
  })

  it('should initialize with default state', () => {
    const store = useAuthStore()
    expect(store.user).toBeNull()
    expect(store.token).toBeNull()
    expect(store.isAuthenticated).toBe(false)
    expect(store.loading).toBe(false)
    expect(store.error).toBeNull()
  })

  it('should login successfully', async () => {
    const mockResponse = {
      data: {
        user: { id: 1, name: 'Test User', role: 'customer' },
        token: 'test-token'
      }
    }
    api.post.mockResolvedValue(mockResponse)

    const store = useAuthStore()
    await store.login({ email: 'test@example.com', password: 'password' })

    expect(api.post).toHaveBeenCalledWith('/auth/login', {
      email: 'test@example.com',
      password: 'password'
    })
    expect(store.user).toEqual(mockResponse.data.user)
    expect(store.token).toBe(mockResponse.data.token)
    expect(store.isAuthenticated).toBe(true)
    expect(store.error).toBeNull()
    expect(localStorage.getItem('token')).toBe(mockResponse.data.token)
  })

  it('should handle login failure', async () => {
    const mockError = new Error('Login failed')
    api.post.mockRejectedValue(mockError)

    const store = useAuthStore()
    await expect(store.login({ email: 'test@example.com', password: 'wrong' }))
      .rejects.toThrow('Login failed')

    expect(store.user).toBeNull()
    expect(store.token).toBeNull()
    expect(store.isAuthenticated).toBe(false)
    expect(store.error).toBe('Login failed')
    expect(localStorage.getItem('token')).toBeNull()
  })

  it('should register successfully', async () => {
    const mockResponse = {
      data: {
        user: { id: 1, name: 'Test User', role: 'customer' },
        token: 'test-token'
      }
    }
    api.post.mockResolvedValue(mockResponse)

    const store = useAuthStore()
    await store.register({ 
      _endpoint: '/auth/register',
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
    }, {})
    expect(store.user).toEqual(mockResponse.data.user)
    expect(store.token).toBe(mockResponse.data.token)
    expect(store.isAuthenticated).toBe(true)
  })

  it('should logout', () => {
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

  it('should clear auth state', () => {
    const store = useAuthStore()
    store.setAuth({
      user: { id: 1, name: 'Test User' },
      token: 'test-token'
    })
    
    store.clearAuth()
    
    expect(store.user).toBeNull()
    expect(store.token).toBeNull()
    expect(store.isAuthenticated).toBe(false)
    expect(localStorage.getItem('token')).toBeNull()
  })
})
