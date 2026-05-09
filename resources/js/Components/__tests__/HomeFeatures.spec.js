import { mount } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach } from 'vitest'
import HomeFeatures from '../HomeFeatures.vue'
import { createPinia, setActivePinia } from 'pinia'

// Mock vue-i18n
vi.mock('vue-i18n', () => ({
  useI18n: () => ({
    t: (key) => {
      const translations = {
        'home.features.title': 'Why Choose TODOKE?',
        'home.features.hybrid.title': 'Hybrid Delivery',
        'home.features.hybrid.subtitle': 'Fast and flexible',
        'home.features.pricing.title': 'Community Pricing',
        'home.features.pricing.subtitle': 'Fair and transparent',
        'home.features.reliable.title': 'Reliable Service',
        'home.features.reliable.subtitle': 'Consistent and secure'
      }
      return translations[key] || key
    }
  })
}))

// Mock log store
vi.mock('@/stores/log', () => ({
  useLogStore: () => ({
    log: vi.fn()
  })
}))

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
  VCard: {
    template: '<div class="v-card"><slot /></div>'
  },
  VCardItem: {
    template: '<div class="v-card-item"><slot /></div>'
  },
  VIcon: {
    template: '<div class="v-icon"><slot /></div>'
  },
  VCardTitle: {
    template: '<div class="v-card-title"><slot /></div>'
  },
  VCardSubtitle: {
    template: '<div class="v-card-subtitle"><slot /></div>'
  },
  VCardText: {
    template: '<div class="v-card-text"><slot /></div>'
  }
}

describe('HomeFeatures', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('renders correctly', () => {
    const wrapper = mount(HomeFeatures, {
      global: {
        stubs: vuetifyComponents
      }
    })
    expect(wrapper.exists()).toBe(true)
  })

  it('displays feature cards', () => {
    const wrapper = mount(HomeFeatures, {
      global: {
        stubs: vuetifyComponents
      }
    })
    const cards = wrapper.findAll('.v-card')
    expect(cards.length).toBe(3)
    
    cards.forEach(card => {
      expect(card.find('.v-card-title').exists()).toBe(true)
      expect(card.find('.v-card-subtitle').exists()).toBe(true)
    })
  })

  it('displays the correct section title', () => {
    const wrapper = mount(HomeFeatures, {
      global: {
        stubs: vuetifyComponents
      }
    })
    expect(wrapper.text()).toContain('Why Choose TODOKE?')
  })

  it('displays the correct feature titles', () => {
    const wrapper = mount(HomeFeatures, {
      global: {
        stubs: vuetifyComponents
      }
    })
    const titles = wrapper.findAll('.v-card-title')
    expect(titles.length).toBe(3)
    expect(titles[0].text()).toBe('Hybrid Delivery')
    expect(titles[1].text()).toBe('Community Pricing')
    expect(titles[2].text()).toBe('Reliable Service')
  })

  it('displays the correct feature subtitles', () => {
    const wrapper = mount(HomeFeatures, {
      global: {
        stubs: vuetifyComponents
      }
    })
    const subtitles = wrapper.findAll('.v-card-subtitle')
    expect(subtitles.length).toBe(3)
    expect(subtitles[0].text()).toBe('Fast and flexible')
    expect(subtitles[1].text()).toBe('Fair and transparent')
    expect(subtitles[2].text()).toBe('Consistent and secure')
  })
})

