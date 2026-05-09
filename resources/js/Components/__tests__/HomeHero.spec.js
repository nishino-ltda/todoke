import { mount } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach } from 'vitest'
import HomeHero from '../HomeHero.vue'
import { createPinia, setActivePinia } from 'pinia'

// Mock vue-i18n
vi.mock('vue-i18n', () => ({
  useI18n: () => ({
    t: (key) => {
      const translations = {
        'home.hero.title': 'Hybrid Delivery with Community Pricing',
        'home.hero.subtitle': 'Combining motorbike couriers and drones for fast, fair deliveries',
        'home.hero.register_button': 'Get Started',
        'home.hero.login_button': 'Sign In'
      }
      return translations[key] || key
    }
  })
}))

// Mock route helper
const route = (name) => {
  const routes = {
    'login': '/login',
    'register': '/register'
  }
  return routes[name] || '#'
}

// Mock Vuetify components
const vuetifyComponents = {
  VContainer: {
    template: '<div class="v-container"><slot /></div>'
  },
  VRow: {
    template: '<div class="v-row"><slot /></div>'
  },
  VCol: {
    template: '<div class="v-col" :cols="cols" :md="md"><slot /></div>',
    props: ['cols', 'md']
  },
  VBtn: {
    template: '<button class="v-btn" :to="to"><slot /></button>',
    props: ['color', 'size', 'variant', 'to']
  },
  VImg: {
    template: '<div class="v-img"></div>',
    props: ['src', 'alt']
  }
}

describe('HomeHero', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('renders correctly', () => {
    const wrapper = mount(HomeHero, {
      global: {
        stubs: vuetifyComponents,
        mocks: {
          route
        }
      }
    })

    // Check if the component renders
    expect(wrapper.find('[data-test="home-hero"]').exists()).toBe(true)
    
    // Check if the title is rendered
    expect(wrapper.find('[data-test="hero-title"]').text()).toBe('Hybrid Delivery with Community Pricing')
    
    // Check if the subtitle is rendered
    expect(wrapper.find('[data-test="hero-subtitle"]').text()).toBe('Combining motorbike couriers and drones for fast, fair deliveries')
    
    // Check if the buttons are rendered
    expect(wrapper.findAll('.v-btn').length).toBe(2)
    
    // Check if the image is rendered
    expect(wrapper.find('[data-test="hero-image"]').exists()).toBe(true)
  })

  it('has correct button links', () => {
    const wrapper = mount(HomeHero, {
      global: {
        stubs: vuetifyComponents,
        mocks: {
          route
        }
      }
    })

    const buttons = wrapper.findAll('.v-btn')
    
    // Check if the buttons have the correct text
    expect(buttons[0].text()).toBe('Get Started')
    expect(buttons[1].text()).toBe('Sign In')
    
    // Check if the buttons have the correct to props
    expect(buttons[0].attributes('to')).toBe('/register')
    expect(buttons[1].attributes('to')).toBe('/login')
  })
})

