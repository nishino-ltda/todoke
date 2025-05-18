import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import AppHeader from '@/components/AppHeader.vue'
import { describe, it, expect, beforeEach, vi } from 'vitest'

describe('AppHeader', () => {
  let wrapper
  let authStore

  beforeEach(() => {
    setActivePinia(createPinia())
    authStore = useAuthStore()
    
    wrapper = mount(AppHeader, {
      global: {
        plugins: [createPinia()],
        stubs: {
          'CartIcon': true,
          'Link': true
        }
      }
    })
  })

  it('renders correctly', () => {
    expect(wrapper.find('[data-test="login-link"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="register-link"]').exists()).toBe(true)
  })

  it('shows login/register links when not authenticated', () => {
    authStore.isAuthenticated = false
    expect(wrapper.find('[data-test="login-link"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="register-link"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="welcome-message"]').exists()).toBe(false)
  })

  it('shows welcome message and logout when authenticated', async () => {
    authStore.isAuthenticated = true
    authStore.user = { name: 'Test User', role: 'customer' }
    await wrapper.vm.$nextTick()

    expect(wrapper.find('[data-test="welcome-message"]').text()).toContain('Test User')
    expect(wrapper.find('[data-test="login-link"]').exists()).toBe(false)
    expect(wrapper.find('[data-test="logout-button"]').exists()).toBe(true)
  })

  it('shows menu link for customers', async () => {
    authStore.isAuthenticated = true
    authStore.user = { name: 'Test User', role: 'customer' }
    await wrapper.vm.$nextTick()

    expect(wrapper.find('[data-test="menu-link"]').exists()).toBe(true)
  })

  it('calls logout when logout button clicked', async () => {
    authStore.isAuthenticated = true
    authStore.logout = vi.fn()
    await wrapper.vm.$nextTick()

    await wrapper.find('[data-test="logout-button"]').trigger('click')
    expect(authStore.logout).toHaveBeenCalled()
  })

  it('shows loading state', async () => {
    authStore.isAuthenticated = true
    authStore.loading = true
    await wrapper.vm.$nextTick()

    expect(wrapper.find('[data-test="loading-indicator"]').exists()).toBe(true)
  })
})
