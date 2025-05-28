import { mount } from '@vue/test-utils'
import HomeHero from '@/Components/HomeHero.vue'
import { createI18n } from 'vue-i18n'
import { useLogStore } from '@/stores/log'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

// Mock translations
const i18n = createI18n({
  legacy: false,
  locale: 'en',
  messages: {
    en: {
      home: {
        hero: {
          title: 'Test Title',
          subtitle: 'Test Subtitle',
          register_button: 'Register',
          login_button: 'Login'
        }
      }
    }
  }
})

// Mock log store
vi.mock('@/stores/log', () => ({
  useLogStore: vi.fn(() => ({
    log: vi.fn()
  }))
}))

// Mock route function
vi.mock('@inertiajs/vue3', () => ({
  route: vi.fn((name) => `/${name}`)
}))

const vuetify = createVuetify({ components, directives })

describe('HomeHero.vue', () => {
  it('renders hero title and subtitle', () => {
    const wrapper = mount(HomeHero, {
      global: {
        plugins: [i18n, vuetify]
      }
    })

    expect(wrapper.find('[data-test="hero-title"]').text()).toBe('Test Title')
    expect(wrapper.find('[data-test="hero-subtitle"]').text()).toBe('Test Subtitle')
  })

  it('renders register and login buttons', () => {
    const wrapper = mount(HomeHero, {
      global: {
        plugins: [i18n, vuetify]
      }
    })

    expect(wrapper.find('[data-test="hero-register-btn"]').text()).toBe('Register')
    expect(wrapper.find('[data-test="hero-login-btn"]').text()).toBe('Login')
  })

  it('logs initialization message', () => {
    const logMock = vi.fn()
    vi.mocked(useLogStore).mockReturnValueOnce({
      log: logMock
    })

    mount(HomeHero, {
      global: {
        plugins: [i18n, vuetify]
      }
    })

    expect(logMock).toHaveBeenCalledWith('🏠 HomeHero component initialized')
  })
})
