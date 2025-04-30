import { mount } from '@vue/test-utils'
import AppHeader from '../AppHeader.vue'
import { useAuthStore } from '@/stores/auth'
import { useLoadingStore } from '@/stores/loading'
import { createPinia } from 'pinia'
import { vi } from 'vitest'

describe('AppHeader', () => {
  let wrapper
  let authStore

  beforeEach(() => {
    const pinia = createPinia()
    authStore = useAuthStore(pinia)

    wrapper = mount(AppHeader, {
      global: {
        plugins: [pinia]
      }
    })
  })

  it('renders correctly', () => {
    expect(wrapper.find('.logo').text()).toBe('TODOKE')
    expect(wrapper.find('.main-nav').exists()).toBe(true)
    expect(wrapper.find('.auth-section').exists()).toBe(true)
  })

  it('shows login/register links when not authenticated', () => {
    authStore.isAuthenticated = false
    expect(wrapper.find('[to="/login"]').exists()).toBe(true)
    expect(wrapper.find('[to="/register"]').exists()).toBe(true)
    expect(wrapper.find('button').exists()).toBe(false)
  })

  it('shows welcome message and logout button when authenticated', async () => {
    authStore.isAuthenticated = true
    authStore.user = { name: 'Test User' }
    await wrapper.vm.$nextTick()

    expect(wrapper.find('span').text()).toContain('Welcome, Test User')
    expect(wrapper.find('button').exists()).toBe(true)
    expect(wrapper.find('[to="/login"]').exists()).toBe(false)
    expect(wrapper.find('[to="/register"]').exists()).toBe(false)
  })

  it('shows menu link when authenticated', async () => {
    authStore.isAuthenticated = true
    await wrapper.vm.$nextTick()

    expect(wrapper.find('[to="/menu"]').exists()).toBe(true)
  })

  it('calls logout when logout button is clicked', async () => {
    authStore.isAuthenticated = true
    authStore.logout = vi.fn()
    await wrapper.vm.$nextTick()

    await wrapper.find('button').trigger('click')
    expect(authStore.logout).toHaveBeenCalled()
  })

  it('disables logout button when loading', async () => {
    authStore.isAuthenticated = true
    const loadingStore = useLoadingStore()
    loadingStore.isLoading = true
    await wrapper.vm.$nextTick()

    expect(wrapper.find('button').attributes('disabled')).toBe('')
  })
})
