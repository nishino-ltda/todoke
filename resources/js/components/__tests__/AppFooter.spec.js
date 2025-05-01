import { mount } from '@vue/test-utils'
import AppFooter from '../AppFooter.vue'

// Mock Vuetify components and router-link
const vuetifyComponents = {
  'v-footer': {
    template: '<div class="v-footer"><slot /></div>'
  },
  'v-container': {
    template: '<div class="v-container"><slot /></div>'
  },
  'v-row': {
    template: '<div class="v-row"><slot /></div>'
  },
  'v-col': {
    template: '<div class="v-col"><slot /></div>'
  },
  'router-link': {
    template: '<a class="router-link"><slot /></a>',
    props: ['to']
  }
}

describe('AppFooter', () => {
  it('renders correctly', () => {
    const wrapper = mount(AppFooter, {
      global: {
        components: vuetifyComponents
      }
    })
    const currentYear = new Date().getFullYear()

    expect(wrapper.find('.v-footer').exists()).toBe(true)
    expect(wrapper.text()).toContain(`© ${currentYear} TODOKE`)
    expect(wrapper.findAll('.router-link').length).toBe(2)
    expect(wrapper.findAll('.router-link')[0].text()).toBe('Terms')
    expect(wrapper.findAll('.router-link')[1].text()).toBe('Privacy')
    expect(wrapper.findAll('.social-link').length).toBe(3)
  })

  it('contains correct social links', () => {
    const wrapper = mount(AppFooter, {
      global: {
        components: vuetifyComponents
      }
    })
    const links = wrapper.findAll('.social-link')

    expect(links[0].attributes('href')).toContain('facebook.com')
    expect(links[1].attributes('href')).toContain('twitter.com')
    expect(links[2].attributes('href')).toContain('instagram.com')
  })
})
