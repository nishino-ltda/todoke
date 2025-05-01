import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import HomeFeatures from '../HomeFeatures.vue'

// Mock Vuetify components
const vuetifyComponents = {
  'v-container': {
    template: '<div class="v-container" :class="className"><slot /></div>',
    props: ['className']
  },
  'v-row': {
    template: '<div class="v-row"><slot /></div>'
  },
  'v-col': {
    template: '<div class="v-col" :cols="cols" :md="md"><slot /></div>',
    props: ['cols', 'md']
  },
  'v-card': {
    template: '<div class="v-card" :height="height"><slot /></div>',
    props: ['height']
  },
  'v-card-item': {
    template: '<div class="v-card-item"><slot /></div>'
  },
  'v-icon': {
    template: '<div class="v-icon" :size="size" :color="color"><slot /></div>',
    props: ['size', 'color']
  },
  'v-card-title': {
    template: '<div class="v-card-title"><slot /></div>'
  },
  'v-card-subtitle': {
    template: '<div class="v-card-subtitle"><slot /></div>'
  },
  'v-card-text': {
    template: '<div class="v-card-text"><slot /></div>'
  }
}

describe('HomeFeatures', () => {
  const wrapper = mount(HomeFeatures, {
    global: {
      stubs: vuetifyComponents
    }
  })

  it('renders correctly', () => {
    expect(wrapper.exists()).toBe(true)
  })

  it('displays feature cards', () => {
    const cards = wrapper.findAll('.v-card')
    expect(cards.length).toBe(3)
    
    cards.forEach(card => {
      expect(card.find('.v-card-title').exists()).toBe(true)
      expect(card.find('.v-card-subtitle').exists()).toBe(true)
    })
  })

  it('displays the correct section title', () => {
    expect(wrapper.text()).toContain('Why Choose TODOKE?')
  })

  it('displays the correct feature titles', () => {
    const titles = wrapper.findAll('.v-card-title')
    expect(titles.length).toBe(3)
    expect(titles[0].text()).toBe('Hybrid Delivery')
    expect(titles[1].text()).toBe('Community Pricing')
    expect(titles[2].text()).toBe('Reliable Service')
  })

  it('displays the correct feature subtitles', () => {
    const subtitles = wrapper.findAll('.v-card-subtitle')
    expect(subtitles.length).toBe(3)
    expect(subtitles[0].text()).toBe('Fast and flexible')
    expect(subtitles[1].text()).toBe('Fair and transparent')
    expect(subtitles[2].text()).toBe('Consistent and secure')
  })
})
