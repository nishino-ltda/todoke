import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import { useLogStore } from './log'
import { router } from '@inertiajs/vue3'

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
      // Use auth service which handles token-to-session conversion
      const authService = (await import('@/services/auth')).default
      const authData = await authService.login(credentials)
      
      if (!authData?.token) {
        throw new Error('No token received in login response')
      }
      
      setAuth(authData)
      
      if (router) {
        const logStore = useLogStore()
        const userType = authData.user?.type
        logStore.log(`🔑 Login successful, user type: ${userType}`, 'debug')
        const redirectPath =
          userType === 'admin' ? '/admin/dashboard' :
            userType === 'courier' ? '/courier/dashboard' :
              userType === 'partner' ? '/partner/dashboard' :
                '/customer/dashboard'
        logStore.log(`🛣️ Redirecting to: ${redirectPath}`, 'debug')
        router.visit(redirectPath)
      }
      return authData
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

    // LOG HERE

    try {
      const endpoint = userData._endpoint || '/auth/register'
      delete userData._endpoint

      const config = {}
      if (userData instanceof FormData) {
        config.headers = {
          'Content-Type': 'multipart/form-data'
        }
      }

      const response = await api.post(endpoint, userData, config)

      const logStore = useLogStore()
      logStore.log(`🔑 Registration response received`, 'debug', response.data)

      // Convert token to session for persistence
      await api.post('/auth/token-to-session', {
        token: response.data.token
      }, {
        headers: {
          'Authorization': `Bearer ${response.data.token}`
        }
      })

      setAuth(response.data)

      logStore.log(`✅ User registered successfully as ${response.data.user?.type}`, 'info')
      return response
    } catch (err) {
      clearAuth()
      // LOG HERE
      error.value = err.response?.data?.message || 'Registration failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function logout(inertiaRouter = null) {
    const logStore = useLogStore()
    const r = inertiaRouter || router
    try {
      logStore.log('🚪 Logout initiated')
      // Try to call server-side logout
      const authService = (await import('@/services/auth')).default
      await authService.logout()
      logStore.log('✅ Server-side logout successful')
    } catch (err) {
      logStore.log(`⚠️ Server-side logout failed: ${err.message}`, 'warning')
    } finally {
      // Always clear local state
      clearAuth()
      if (r) {
        logStore.log('🏠 Redirecting to home via web logout')
        // Use POST /logout to clear the web session reliably
        r.post('/logout')
      }
    }
  }

  function setAuth(authData) {
    const logStore = useLogStore()
    logStore.log(`Setting auth data: ${JSON.stringify(authData)}`, 'debug')
    if (!authData || !authData.token || !authData.user) {
      logStore.log('Invalid auth data received', 'error')
      throw new Error('Invalid auth data received')
    }

    user.value = authData.user
    token.value = authData.token
    isAuthenticated.value = true
    error.value = null

    localStorage.setItem('token', authData.token)
    logStore.log(`Auth set successfully for user: ${authData.user.email}`, 'info')
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

  async function requestPasswordReset(email) {
    loading.value = true
    error.value = null
    try {
      const response = await api.post('/auth/forgot-password', { email })
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to send reset link'
      throw err
    } finally {
      loading.value = false
    }
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
    $reset,
    requestPasswordReset
  }
})
