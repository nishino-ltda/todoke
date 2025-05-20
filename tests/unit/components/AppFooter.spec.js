import { shallowMount } from '@vue/test-utils'
import AppFooter from '@/components/AppFooter.vue'
import { describe, it, expect, vi } from 'vitest'

describe('AppFooter', () => {
  let wrapper

  beforeEach(() => {
    wrapper = shallowMount(AppFooter, {
      global: {
        stubs: {
          'Link': true,
          'v-footer': {
            template: '<div><slot /></div>'
          },
          'v-container': {
            template: '<div><slot /></div>'
          },
          'v-row': {
            template: '<div><slot /></div>'
          },
          'v-col': {
            template: '<div><slot /></div>'
          }
        },
        mocks: {
          route: vi.fn().mockImplementation((name) => `/${name}`)
        }
      }
    })
  })

  it('renders correctly', () => {
    expect(wrapper.find('[data-test="app-footer"]').exists()).toBe(true)
  })

  it('displays current year in copyright', () => {
    const currentYear = new Date().getFullYear()
    expect(wrapper.text()).toContain(`© ${currentYear} TODOKE`)
  })

  it('has terms and privacy links', () => {
    expect(wrapper.find('[data-test="terms-link"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="privacy-link"]').exists()).toBe(true)
  })

  it('has social media links', () => {
    expect(wrapper.find('[data-test="facebook-link"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="twitter-link"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="instagram-link"]').exists()).toBe(true)
  })
})
