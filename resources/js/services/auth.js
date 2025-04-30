import api from './api'
import { useAuthStore } from '@/stores/auth'

class AuthService {
  getAuthStore() {
    return useAuthStore()
  }

  async login(credentials) {
    try {
      const response = await api.post('/auth/login', credentials)
      this.getAuthStore().setAuth(response.data)
      return response
    } catch (error) {
      this.getAuthStore().logout()
      throw error
    }
  }

  async register(userData) {
    try {
      const response = await api.post('/auth/register', userData)
      this.getAuthStore().setAuth(response.data)
      return response
    } catch (error) {
      this.getAuthStore().logout()
      throw error
    }
  }

  logout() {
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
