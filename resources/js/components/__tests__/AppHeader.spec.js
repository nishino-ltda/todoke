import { mount } from '@vue/test-utils'
import AppHeader from '../AppHeader.vue'
import { useAuthStore } from '@/stores/auth'
import { useLogStore } from '@/stores/log'
import { createPinia } from 'pinia'
import { vi, describe, it, expect, beforeEach, afterEach } from 'vitest'

// Mock localStorage
const localStorageMock = (function() {
  let store = {}
  return {
    getItem: function(key) {
      return store[key] || null
    },
    setItem: function(key, value) {
      store[key] = value.toString()
    },
    removeItem: function(key) {
      delete store[key]
    },
    clear: function() {
      store = {}
    }
  }
})()
global.localStorage = localStorageMock

// Mock route helper
const route = (name) => {
  const routes = {
    'login': '/login',
    'register': '/register',
    'menu': '/menu'
  }
  return routes[name] || '#'
}

// Mock Vuetify components
const VAppBar = { 
  template: '<div class="v-app-bar"><slot/></div>',
  props: ['app']
}
const VAppBarNavIcon = {
  template: '<button class="v-app-bar-nav-icon"></button>'
}
const VToolbar = { 
  template: '<div class="v-toolbar"><slot/></div>',
  props: ['dense']
}
const VToolbarTitle = { 
  template: '<div class="v-toolbar-title"><slot/></div>',
  props: ['text']
}
const VBtn = { 
  template: `
    <button 
      class="v-btn" 
      :disabled="disabled"
      @click="$emit('click')"
    >
      <slot>{{ text }}</slot>
    </button>
  `,
  props: ['text', 'disabled']
}

// Mock welcome message span
const WelcomeMessage = {
  template: '<span class="welcome-message"><slot/></span>'
}
const VSpacer = { 
  template: '<div class="v-spacer"></div>'
}

// Mock Inertia Link component
const Link = {
  template: '<a :href="href"><slot/></a>',
  props: ['href']
}

// Mock CartIcon component
const CartIcon = {
  template: '<div class="cart-icon"></div>'
}

// Component stubs
const vuetifyComponents = {
  VAppBar,
  VAppBarNavIcon,
  VToolbar,
  VToolbarTitle,
  VBtn,
  VSpacer,
  WelcomeMessage,
  Link,
  CartIcon
}

// Add route to global properties
const globalMocks = {
  $route: route,
  route
}

describe('AppHeader', () => {
  let wrapper
  let authStore
  let logStore

  beforeEach(async () => {
    const pinia = createPinia()
    authStore = useAuthStore(pinia)
    logStore = useLogStore(pinia)
    
    // Initialize with test values
    localStorage.setItem('token', 'test-token')
    authStore.user = { name: 'Test User' }
    authStore.isAuthenticated = false
    authStore.loading = false
    authStore.logout = vi.fn().mockResolvedValue(true)
    logStore.log = vi.fn()

    wrapper = mount(AppHeader, {
      global: {
        plugins: [pinia],
        stubs: vuetifyComponents,
        mocks: globalMocks
      }
    })

    await wrapper.vm.$nextTick()
  })

  afterEach(() => {
    localStorage.clear()
    vi.clearAllMocks()
  })

  it('renders correctly', () => {
    expect(wrapper.find('.v-app-bar').exists()).toBe(true)
    expect(wrapper.find('.v-toolbar-title').text()).toBe('TODOKE')
  })

  it('shows login/register links when not authenticated', async () => {
    // Create fresh wrapper with unauthenticated state
    const pinia = createPinia()
    const testAuthStore = useAuthStore(pinia)
    testAuthStore.isAuthenticated = false

    const testWrapper = mount(AppHeader, {
      global: {
        plugins: [pinia],
        stubs: vuetifyComponents,
        mocks: globalMocks
      }
    })

    await testWrapper.vm.$nextTick()
    
    const loginLink = testWrapper.find('[data-test="login-link"]')
    const registerLink = testWrapper.find('[data-test="register-link"]')
    expect(loginLink.exists()).toBe(true)
    expect(loginLink.text()).toBe('Login')
    expect(registerLink.exists()).toBe(true)
    expect(registerLink.text()).toBe('Register')
  })

  it('shows welcome message and logout button when authenticated', async () => {
    // Create fresh wrapper with authenticated state
    const pinia = createPinia()
    const testAuthStore = useAuthStore(pinia)
    testAuthStore.isAuthenticated = true
    testAuthStore.user = { name: 'Test User' }

    const testWrapper = mount(AppHeader, {
      global: {
        plugins: [pinia],
        stubs: vuetifyComponents,
        mocks: globalMocks
      }
    })

    await testWrapper.vm.$nextTick()
    
    expect(testWrapper.html()).toContain('Logout')
    expect(testWrapper.html()).toContain('Welcome, Test User')
  })

  it('shows menu link when authenticated', async () => {
    // Create fresh wrapper with authenticated customer state
    const pinia = createPinia()
    const testAuthStore = useAuthStore(pinia)
    testAuthStore.isAuthenticated = true
    testAuthStore.user = { role: 'customer', name: 'Test Customer' }

    const testWrapper = mount(AppHeader, {
      global: {
        plugins: [pinia],
        stubs: vuetifyComponents,
        mocks: globalMocks
      }
    })

    await testWrapper.vm.$nextTick()
    expect(testWrapper.html()).toContain('Menu')
  })

  it('calls logout and logs events when logout button is clicked', async () => {
    // Create fresh wrapper with authenticated state
    const pinia = createPinia()
    const testAuthStore = useAuthStore(pinia)
    const testLogStore = useLogStore(pinia)
    testAuthStore.isAuthenticated = true
    testAuthStore.user = { name: 'Test User' }
    testAuthStore.logout = vi.fn().mockResolvedValue(true)
    testLogStore.log = vi.fn()

    const testWrapper = mount(AppHeader, {
      global: {
        plugins: [pinia],
        stubs: vuetifyComponents,
        mocks: globalMocks
      }
    })

    await testWrapper.vm.$nextTick()
    
    // Find the logout button by its text content
    const logoutButton = testWrapper.findAll('.v-btn').find(btn => btn.text().includes('Logout'))
    await logoutButton.trigger('click')
    
    expect(testAuthStore.logout).toHaveBeenCalled()
    expect(testLogStore.log).toHaveBeenCalledWith('🔄 AppHeader: Logout initiated')
    await testAuthStore.logout.mock.results[0].value // Wait for promise
    expect(testLogStore.log).toHaveBeenCalledWith('✅ AppHeader: Logout successful')
  })

  it('logs failed logout attempts', async () => {
    const pinia = createPinia()
    const testAuthStore = useAuthStore(pinia)
    const testLogStore = useLogStore(pinia)
    testAuthStore.isAuthenticated = true
    testAuthStore.user = { name: 'Test User' }
    const error = new Error('Network error')
    testAuthStore.logout = vi.fn().mockRejectedValue(error)
    testLogStore.log = vi.fn()

    const testWrapper = mount(AppHeader, {
      global: {
        plugins: [pinia],
        stubs: vuetifyComponents,
        mocks: globalMocks
      }
    })

    await testWrapper.vm.$nextTick()
    const logoutButton = testWrapper.findAll('.v-btn').find(btn => btn.text().includes('Logout'))
    await logoutButton.trigger('click')
    
    expect(testLogStore.log).toHaveBeenCalledWith('🔄 AppHeader: Logout initiated')
    await expect(testAuthStore.logout).rejects.toThrow(error)
    expect(testLogStore.log).toHaveBeenCalledWith(`❌ AppHeader: Logout failed - ${error.message}`)
  })

  it('logs drawer toggle events', async () => {
    const pinia = createPinia()
    const testLogStore = useLogStore(pinia)
    testLogStore.log = vi.fn()

    const testWrapper = mount(AppHeader, {
      global: {
        plugins: [pinia],
        stubs: vuetifyComponents,
        mocks: globalMocks
      }
    })

    await testWrapper.vm.$nextTick()
    const drawerButton = testWrapper.find('.v-app-bar-nav-icon')
    await drawerButton.trigger('click')
    
    expect(testLogStore.log).toHaveBeenCalledWith('📱 AppHeader: Drawer toggled')
  })

  it('disables logout button when loading', async () => {
    // Create fresh wrapper with loading state
    const pinia = createPinia()
    const testAuthStore = useAuthStore(pinia)
    testAuthStore.isAuthenticated = true
    testAuthStore.user = { name: 'Test User' }
    testAuthStore.loading = true

    const testWrapper = mount(AppHeader, {
      global: {
        plugins: [pinia],
        stubs: vuetifyComponents,
        mocks: globalMocks
      }
    })

    await testWrapper.vm.$nextTick()
    
    // Find the logout button by its text content
    const logoutButton = testWrapper.findAll('.v-btn').find(btn => btn.text().includes('Logout'))
    expect(logoutButton.attributes('disabled')).toBeDefined()
  })
})
