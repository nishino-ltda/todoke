import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token') || null)
  const isAuthenticated = ref(false)
  const loading = ref(false)
  const error = ref(null)

  async function login(credentials, router = null) {
    loading.value = true
    error.value = null
    try {
      const response = await api.post('/auth/login', credentials)
      setAuth(response.data)
      if (router) {
        const redirectPath = response.data.user?.type === 'partner' ? '/partner' : '/'
        router.push(redirectPath)
      }
      return response
    } catch (err) {
      clearAuth()
      error.value = err.response?.data?.message || 'Login failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function register(userData, router = null) {
    loading.value = true
    error.value = null
    try {
      // Use the endpoint provided in userData or default to '/auth/register'
      const endpoint = userData._endpoint || '/auth/register'
      delete userData._endpoint
      
      const response = await api.post(endpoint, {
        ...userData,
        role: userData.role || 'customer'
      })
      setAuth(response.data)
      if (router) router.push('/')
      return response
    } catch (err) {
      clearAuth()
      error.value = err.response?.data?.message || 'Registration failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  function logout(router = null) {
    user.value = null
    token.value = null
    isAuthenticated.value = false
    error.value = null
    localStorage.removeItem('token')
    if (router) router.push('/login')
  }

  function setAuth(authData) {
    user.value = authData.user
    token.value = authData.token
    isAuthenticated.value = true
    error.value = null
    localStorage.setItem('token', authData.token)
  }

  function clearAuth() {
    user.value = null
    token.value = null
    isAuthenticated.value = false
    localStorage.removeItem('token')
  }

  // Initialize store with router for components that need it
  function init(router) {
    return {
      user,
      token,
      isAuthenticated,
      loading,
      error,
      login: (credentials) => login(credentials, router),
      register: (userData) => register(userData, router),
      logout: () => logout(router),
      setAuth
    }
  }

  // Default export without router for testing
  function $reset() {
    clearAuth()
  }

  return {
    user,
    token,
    isAuthenticated,
    loading,
    error,
    login,
    register,
    logout,
    setAuth,
    init,
    $reset
  }
})
