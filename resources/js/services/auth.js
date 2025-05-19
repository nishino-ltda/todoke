import api from './api'
import { useAuthStore } from '@/stores/auth'
import { useLogStore } from '@/stores/log'

class AuthService {
  constructor() {
    this.logStore = useLogStore()
  }
  getAuthStore() {
    return useAuthStore()
  }

  async login(credentials) {
    try {
      this.logStore.log(`Attempting login for ${credentials.email}`, 'info')
      const response = await api.post('/auth/login', credentials)
      this.logStore.log(`Login successful for ${credentials.email}`, 'success')
      this.getAuthStore().setAuth(response.data)
      return response
    } catch (error) {
      this.logStore.log(`Login failed for ${credentials.email}: ${error.message}`, 'error')
      this.getAuthStore().logout()
      throw error
    }
  }

  async register(userData) {
    try {
      this.logStore.log(`Attempting registration for ${userData.email}`, 'info')
      const response = await api.post('/auth/register', userData)
      this.logStore.log(`Registration successful for ${userData.email}`, 'success')
      this.getAuthStore().setAuth(response.data)
      return response
    } catch (error) {
      this.logStore.log(`Registration failed for ${userData.email}: ${error.message}`, 'error')
      this.getAuthStore().logout()
      throw error
    }
  }

  logout() {
    const user = this.getCurrentUser()
    if (user) {
      this.logStore.log(`Logging out user ${user.email}`, 'info')
    }
    this.getAuthStore().logout()
  }

  getCurrentUser() {
    return this.getAuthStore().user
  }

  isAuthenticated() {
    return this.getAuthStore().isAuthenticated
  }
}

export default new AuthService()
