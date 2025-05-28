import { mount, RouterLinkStub } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import HomeHero from '../HomeHero.vue'

// Mock Vuetify components
const vuetifyComponents = {
  'v-container': {
    template: '<div class="v-container" :fluid="fluid" :class="className"><slot /></div>',
    props: ['fluid', 'className']
  },
  'v-row': {
    template: '<div class="v-row" :no-gutters="noGutters" :align="align"><slot /></div>',
    props: ['noGutters', 'align']
  },
  'v-col': {
    template: '<div class="v-col" :cols="cols" :md="md" :class="className"><slot /></div>',
    props: ['cols', 'md', 'className']
  },
  'v-btn': {
    template: `
      <a 
        class="v-btn" 
        :color="color" 
        :size="size" 
        :variant="variant" 
        :href="href"
        :to="to"
      >
        <slot />
      </a>
    `,
    props: ['color', 'size', 'variant', 'href', 'to']
  },
  'v-img': {
    template: '<div class="v-img" :src="src" :alt="alt" :cover="cover" :height="height"></div>',
    props: ['src', 'alt', 'cover', 'height']
  }
}


describe('HomeHero', () => {
  it('renders correctly', () => {
    const wrapper = mount(HomeHero, {
      global: {
        stubs: {
          ...vuetifyComponents,
          RouterLink: RouterLinkStub
        }
      }
    })

    // Check if the component renders
    expect(wrapper.find('.hero-container').exists()).toBe(true)
    
    // Check if the title is rendered
    expect(wrapper.text()).toContain('Hybrid Delivery with Community Pricing')
    
    // Check if the subtitle is rendered
    expect(wrapper.text()).toContain('Combining motorbike couriers and drones for fast, fair deliveries')
    
    // Check if the buttons are rendered
    expect(wrapper.findAll('.v-btn').length).toBe(2)
    
    // Check if the image is rendered
    expect(wrapper.find('.v-img').exists()).toBe(true)
  })

  it('has correct button links', () => {
    const wrapper = mount(HomeHero, {
      global: {
        stubs: {
          ...vuetifyComponents,
          RouterLink: RouterLinkStub
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

  it('has responsive layout classes', () => {
    const wrapper = mount(HomeHero, {
      global: {
        stubs: {
          ...vuetifyComponents,
          RouterLink: RouterLinkStub
        }
      }
    })

    // Check if the container has fluid class
    const container = wrapper.find('.v-container')
    expect(container.attributes('fluid')).toBeDefined()
    
    // Check if the row has no-gutters attribute
    const row = wrapper.find('.v-row')
    expect(row.attributes('no-gutters')).toBeDefined()
    
    // Check if the columns have responsive classes
    const cols = wrapper.findAll('.v-col')
    expect(cols.length).toBe(2)
    expect(cols[0].attributes('cols')).toBe('12')
    expect(cols[0].attributes('md')).toBe('6')
    expect(cols[1].attributes('cols')).toBe('12')
    expect(cols[1].attributes('md')).toBe('6')
  })
})
