import api from './api'
import { useAuthStore } from '@/stores/auth'
import { useLogStore } from '@/stores/log'

/**
 * AuthServiceInterface defines the contract for authentication services
 * @interface
 */
const AuthServiceInterface = {
  /**
   * Authenticate user with credentials
   * @param {Object} credentials - {email, password}
   * @returns {Promise<Object>} Auth response
   */
  login: async (credentials) => {},

  /**
   * Register new user
   * @param {Object} userData - User registration data
   * @returns {Promise<Object>} Auth response
   */
  register: async (userData) => {},

  /**
   * Logout current user
   * @returns {Promise<void>}
   */
  logout: async () => {},

  /**
   * Refresh authentication token
   * @returns {Promise<Object>} New auth data
   */
  refreshToken: async () => {}
}

const authService = {
  async login(credentials) {
    const logStore = useLogStore()
    try {
      logStore.log('🔐 Attempting login')
      const response = await api.post('/auth/login', credentials)
      const authStore = useAuthStore()
      authStore.setAuth(response.data)
      logStore.log('✅ Login successful')
      return response.data
    } catch (error) {
      logStore.log('❌ Login failed', error)
      throw new Error('Login failed. Please check your credentials.')
    }
  },

  async register(userData) {
    const logStore = useLogStore()
    try {
      logStore.log('📝 Attempting registration')
      const response = await api.post('/auth/register', userData)
      const authStore = useAuthStore()
      authStore.setAuth(response.data)
      logStore.log('✅ Registration successful')
      return response.data
    } catch (error) {
      logStore.log('❌ Registration failed', error)
      throw new Error('Registration failed. Please try again.')
    }
  },

  async logout() {
    const logStore = useLogStore()
    try {
      logStore.log('🚪 Attempting logout')
      await api.post('/auth/logout')
      const authStore = useAuthStore()
      authStore.clearAuth()
      logStore.log('✅ Logout successful')
    } catch (error) {
      logStore.log('❌ Logout failed', error)
      throw new Error('Logout failed. Please try again.')
    }
  },

  async refreshToken() {
    const logStore = useLogStore()
    try {
      logStore.log('🔄 Attempting token refresh')
      const response = await api.post('/refresh-token')
      const authStore = useAuthStore()
      authStore.setAuth(response.data)
      logStore.log('✅ Token refresh successful')
      return response.data
    } catch (error) {
      logStore.log('❌ Token refresh failed', error)
      throw new Error('Session expired. Please login again.')
    }
  }
}

// Verify implementation matches interface
Object.keys(AuthServiceInterface).forEach(method => {
  if (typeof authService[method] !== 'function') {
    throw new Error(`AuthService missing required method: ${method}`)
  }
})

export default authService
