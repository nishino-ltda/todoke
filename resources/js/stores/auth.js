import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token') || null)
  const isAuthenticated = ref(false)

  async function login(credentials) {
    try {
      const response = await api.post('/auth/login', credentials)
      setAuth(response.data)
      return response
    } catch (error) {
      logout()
      throw error
    }
  }

  async function register(userData) {
    try {
      const response = await api.post('/auth/register', userData)
      setAuth(response.data)
      return response
    } catch (error) {
      logout()
      throw error
    }
  }

  function logout() {
    user.value = null
    token.value = null
    isAuthenticated.value = false
    localStorage.removeItem('token')
  }

  function setAuth(authData) {
    user.value = authData.user
    token.value = authData.token
    isAuthenticated.value = true
    localStorage.setItem('token', authData.token)
  }

  return {
    user,
    token,
    isAuthenticated,
    login,
    register,
    logout,
    setAuth
  }
})
