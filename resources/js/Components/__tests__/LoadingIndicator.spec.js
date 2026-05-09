import { describe, it, expect, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import LoadingIndicator from '../LoadingIndicator.vue'

// Mock Vuetify components
const vuetifyComponents = {
  VOverlay: {
    template: '<div class="v-overlay" v-if="value"><slot /></div>',
    props: ['value']
  },
  VProgressCircular: {
    template: '<div class="v-progress-circular"></div>'
  }
}

describe('LoadingIndicator', () => {
  it('does not render when isLoading is false', () => {
    const wrapper = mount(LoadingIndicator, {
      props: {
        isLoading: false
      },
      global: {
        stubs: vuetifyComponents
      }
    })
    expect(wrapper.find('[data-test="inline-loader"]').exists()).toBe(false)
    expect(wrapper.find('[data-test="full-loader"]').exists()).toBe(false)
  })

  it('renders inline loader when isLoading is true and fullPage is false', () => {
    const wrapper = mount(LoadingIndicator, {
      props: {
        isLoading: true,
        fullPage: false
      },
      global: {
        stubs: vuetifyComponents
      }
    })
    expect(wrapper.find('[data-test="inline-loader"]').exists()).toBe(true)
    expect(wrapper.find('.v-progress-circular').exists()).toBe(true)
  })

  it('renders full page loader when fullPage is true', () => {
    const wrapper = mount(LoadingIndicator, {
      props: {
        isLoading: true,
        fullPage: true
      },
      global: {
        stubs: vuetifyComponents
      }
    })
    expect(wrapper.find('[data-test="full-loader"]').exists()).toBe(true)
  })

  it('shows message when provided', () => {
    const wrapper = mount(LoadingIndicator, {
      props: {
        isLoading: true,
        message: 'Loading data...'
      },
      global: {
        stubs: vuetifyComponents
      }
    })
    expect(wrapper.text()).toContain('Loading data...')
  })
})

