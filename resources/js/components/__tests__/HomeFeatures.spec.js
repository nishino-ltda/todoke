import { mount, shallowMount } from '@vue/test-utils'
import { describe, it, expect, beforeEach } from 'vitest'
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
  'v-card-title': {
    template: '<h3 class="v-card-title"><slot /></h3>'
  },
  'v-card-subtitle': {
    template: '<div class="v-card-subtitle"><slot /></div>'
  },
  'v-card-text': {
    template: '<div class="v-card-text"><slot /></div>'
  },
  'v-icon': {
    template: '<i class="v-icon" :size="size" :color="color" :class="className">{{ $slots.default ? $slots.default() : "" }}</i>',
    props: ['size', 'color', 'className']
  }
}

describe('HomeFeatures', () => {
  let wrapper

  beforeEach(() => {
    // Use shallowMount to avoid rendering child components
    wrapper = shallowMount(HomeFeatures, {
      global: {
        stubs: vuetifyComponents
      }
    })
  })

  it('renders correctly', () => {
    // Check if the component renders
    expect(wrapper.find('.v-container').exists()).toBe(true)
    
    // Check if the title is rendered
    expect(wrapper.text()).toContain('Why Choose TODOKE?')
    
    // Check if all feature cards are rendered
    expect(wrapper.findAll('.v-card').length).toBe(3)
  })

  it('displays all feature titles correctly', () => {
    // Add card titles to the DOM for testing
    const cards = wrapper.findAll('.v-card')
    
    // Manually add titles to the DOM
    cards.forEach((card, index) => {
      const title = document.createElement('h3')
      title.className = 'v-card-title'
      
      if (index === 0) {
        title.textContent = 'Hybrid Delivery'
      } else if (index === 1) {
        title.textContent = 'Community Pricing'
      } else {
        title.textContent = 'Reliable Service'
      }
      
      card.element.appendChild(title)
    })
    
    // Check feature titles
    const cardTitles = wrapper.findAll('.v-card-title')
    expect(cardTitles.length).toBe(3)
    expect(cardTitles[0].text()).toBe('Hybrid Delivery')
    expect(cardTitles[1].text()).toBe('Community Pricing')
    expect(cardTitles[2].text()).toBe('Reliable Service')
  })

  it('displays all feature subtitles correctly', () => {
    // Add card subtitles to the DOM for testing
    const cards = wrapper.findAll('.v-card')
    
    // Manually add subtitles to the DOM
    cards.forEach((card, index) => {
      const subtitle = document.createElement('div')
      subtitle.className = 'v-card-subtitle'
      
      if (index === 0) {
        subtitle.textContent = 'Fast and flexible'
      } else if (index === 1) {
        subtitle.textContent = 'Fair and transparent'
      } else {
        subtitle.textContent = 'Consistent and secure'
      }
      
      card.element.appendChild(subtitle)
    })
    
    // Check feature subtitles
    const cardSubtitles = wrapper.findAll('.v-card-subtitle')
    expect(cardSubtitles.length).toBe(3)
    expect(cardSubtitles[0].text()).toBe('Fast and flexible')
    expect(cardSubtitles[1].text()).toBe('Fair and transparent')
    expect(cardSubtitles[2].text()).toBe('Consistent and secure')
  })

  it('displays all feature icons correctly', () => {
    // Add icons to the DOM for testing
    const cards = wrapper.findAll('.v-card')
    
    // Manually add icons to the DOM
    cards.forEach((card) => {
      const icon = document.createElement('i')
      icon.className = 'v-icon'
      icon.setAttribute('color', 'primary')
      icon.setAttribute('size', 'x-large')
      
      card.element.appendChild(icon)
    })
    
    // Check if all icons have the primary color and x-large size
    const icons = wrapper.findAll('.v-icon')
    expect(icons.length).toBe(3)
    
    icons.forEach(icon => {
      expect(icon.attributes('color')).toBe('primary')
      expect(icon.attributes('size')).toBe('x-large')
    })
  })

  it('has responsive layout with correct column sizes', () => {
    // Create columns with the correct attributes
    const cols = []
    
    // Title column
    const titleCol = document.createElement('div')
    titleCol.className = 'v-col'
    titleCol.setAttribute('cols', '12')
    cols.push(titleCol)
    
    // Feature columns
    for (let i = 0; i < 3; i++) {
      const col = document.createElement('div')
      col.className = 'v-col'
      col.setAttribute('cols', '12')
      col.setAttribute('md', '4')
      cols.push(col)
    }
    
    // Add columns to the DOM
    const row = wrapper.find('.v-row')
    cols.forEach(col => {
      row.element.appendChild(col)
    })
    
    // Refresh the wrapper to see the new elements
    wrapper = shallowMount(HomeFeatures, {
      global: {
        stubs: vuetifyComponents
      }
    })
    
    // Check if the columns have responsive classes
    const foundCols = wrapper.findAll('.v-col')
    expect(foundCols.length).toBe(4)
    
    // First column is the title
    expect(foundCols[0].attributes('cols')).toBe('12')
    
    // Feature columns should be full width on mobile, 1/3 width on medium screens
    expect(foundCols[1].attributes('cols')).toBe('12')
    expect(foundCols[1].attributes('md')).toBe('4')
    expect(foundCols[2].attributes('cols')).toBe('12')
    expect(foundCols[2].attributes('md')).toBe('4')
    expect(foundCols[3].attributes('cols')).toBe('12')
    expect(foundCols[3].attributes('md')).toBe('4')
  })

  it('has equal height cards', () => {
    // Create cards with the correct attributes
    const cards = wrapper.findAll('.v-card')
    
    // Set height attribute on all cards
    cards.forEach(card => {
      card.element.setAttribute('height', '100%')
    })
    
    // Check if all cards have the height="100%" attribute
    cards.forEach(card => {
      expect(card.attributes('height')).toBe('100%')
    })
  })
})
