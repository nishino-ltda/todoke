import { mount } from '@vue/test-utils'
import AppHeader from '../AppHeader.vue'
import { useAuthStore } from '@/stores/auth'
import { createPinia } from 'pinia'
import { vi } from 'vitest'
import { createRouter, createWebHistory } from 'vue-router'

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

// Component stubs
const vuetifyComponents = {
  VAppBar,
  VAppBarNavIcon,
  VToolbar,
  VToolbarTitle,
  VBtn,
  VSpacer,
  WelcomeMessage
}

// Create a real router instance for testing
const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: { template: '<div>Home</div>' } },
    { path: '/login', component: { template: '<div>Login</div>' } },
    { path: '/register', component: { template: '<div>Register</div>' } },
    { path: '/menu', component: { template: '<div>Menu</div>' } }
  ]
})

describe('AppHeader', () => {
  let wrapper
  let authStore

  beforeEach(async () => {
    const pinia = createPinia()
    authStore = useAuthStore(pinia)
    
    // Initialize with test values
    authStore.user = { name: 'Test User' }
    authStore.isAuthenticated = false
    authStore.loading = false

    wrapper = mount(AppHeader, {
      global: {
        plugins: [pinia, router],
        stubs: vuetifyComponents
      }
    })

    await wrapper.vm.$nextTick()
  })

  afterEach(() => {
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
        plugins: [pinia, router],
        stubs: vuetifyComponents
      }
    })

    await testWrapper.vm.$nextTick()
    
    const buttons = testWrapper.findAll('.v-btn')
    const buttonTexts = buttons.map(b => b.text())
    expect(buttonTexts).toContain('Login')
    expect(buttonTexts).toContain('Register')
  })

  it('shows welcome message and logout button when authenticated', async () => {
    // Create fresh wrapper with authenticated state
    const pinia = createPinia()
    const testAuthStore = useAuthStore(pinia)
    testAuthStore.isAuthenticated = true
    testAuthStore.user = { name: 'Test User' }

    const testWrapper = mount(AppHeader, {
      global: {
        plugins: [pinia, router],
        stubs: vuetifyComponents
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
    testAuthStore.user = { type: 'customer' }

    const testWrapper = mount(AppHeader, {
      global: {
        plugins: [pinia, router],
        stubs: vuetifyComponents
      }
    })

    await testWrapper.vm.$nextTick()
    expect(testWrapper.html()).toContain('Menu')
  })

  it('calls logout when logout button is clicked', async () => {
    // Create fresh wrapper with authenticated state
    const pinia = createPinia()
    const testAuthStore = useAuthStore(pinia)
    testAuthStore.isAuthenticated = true
    testAuthStore.user = { name: 'Test User' }
    testAuthStore.logout = vi.fn()

    const testWrapper = mount(AppHeader, {
      global: {
        plugins: [pinia, router],
        stubs: vuetifyComponents
      }
    })

    await testWrapper.vm.$nextTick()
    
    // Find the logout button by its text content
    const logoutButton = testWrapper.findAll('.v-btn').find(btn => btn.text().includes('Logout'))
    await logoutButton.trigger('click')
    
    expect(testAuthStore.logout).toHaveBeenCalled()
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
        plugins: [pinia, router],
        stubs: vuetifyComponents
      }
    })

    await testWrapper.vm.$nextTick()
    
    // Find the logout button by its text content
    const logoutButton = testWrapper.findAll('.v-btn').find(btn => btn.text().includes('Logout'))
    expect(logoutButton.attributes('disabled')).toBeDefined()
  })
})
