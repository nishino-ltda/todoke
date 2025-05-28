import { mount } from '@vue/test-utils'
import HomeFeatures from '@/Components/HomeFeatures.vue'
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
        features: {
          title: 'Why Choose TODOKE?',
          hybrid: {
            title: 'Hybrid Delivery',
            subtitle: 'Fast and flexible',
            description: 'Hybrid delivery description'
          },
          pricing: {
            title: 'Community Pricing',
            subtitle: 'Fair and transparent',
            description: 'Pricing description'
          },
          reliable: {
            title: 'Reliable Service',
            subtitle: 'Consistent and secure',
            description: 'Reliability description'
          }
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

const vuetify = createVuetify({ components, directives })

describe('HomeFeatures.vue', () => {
  it('renders features title', () => {
    const wrapper = mount(HomeFeatures, {
      global: {
        plugins: [i18n, vuetify]
      }
    })

    expect(wrapper.find('[data-test="features-title"]').text()).toBe('Why Choose TODOKE?')
  })

  it('renders all feature cards with correct data', () => {
    const wrapper = mount(HomeFeatures, {
      global: {
        plugins: [i18n, vuetify]
      }
    })

    // Test hybrid feature card
    expect(wrapper.find('[data-test="hybrid-title"]').text()).toBe('Hybrid Delivery')
    expect(wrapper.find('[data-test="hybrid-subtitle"]').text()).toBe('Fast and flexible')
    expect(wrapper.find('[data-test="hybrid-description"]').text()).toBe('Hybrid delivery description')

    // Test pricing feature card
    expect(wrapper.find('[data-test="pricing-title"]').text()).toBe('Community Pricing')
    expect(wrapper.find('[data-test="pricing-subtitle"]').text()).toBe('Fair and transparent')
    expect(wrapper.find('[data-test="pricing-description"]').text()).toBe('Pricing description')

    // Test reliable feature card
    expect(wrapper.find('[data-test="reliable-title"]').text()).toBe('Reliable Service')
    expect(wrapper.find('[data-test="reliable-subtitle"]').text()).toBe('Consistent and secure')
    expect(wrapper.find('[data-test="reliable-description"]').text()).toBe('Reliability description')
  })

  it('logs initialization message', () => {
    const logMock = vi.fn()
    vi.mocked(useLogStore).mockReturnValueOnce({
      log: logMock
    })

    mount(HomeFeatures, {
      global: {
        plugins: [i18n, vuetify]
      }
    })

    expect(logMock).toHaveBeenCalledWith('✨ HomeFeatures component initialized')
  })
})
