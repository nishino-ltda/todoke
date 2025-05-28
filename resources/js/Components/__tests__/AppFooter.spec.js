import { mount } from '@vue/test-utils'
import AppFooter from '../AppFooter.vue'
import { describe, it, expect } from 'vitest'

// Mock Vuetify components
const VFooter = {
  template: '<footer class="v-footer"><slot/></footer>',
  props: ['app', 'color', 'dark']
}
const VContainer = {
  template: '<div class="v-container"><slot/></div>'
}
const VRow = {
  template: '<div class="v-row"><slot/></div>',
  props: ['justify', 'align']
}
const VCol = {
  template: '<div class="v-col"><slot/></div>',
  props: ['cols', 'md', 'class']
}
const VBtn = {
  template: '<button class="v-btn"><slot/></button>',
  props: ['variant', 'color', 'href', 'class']
}
const VIcon = {
  template: '<span class="v-icon"><slot/></span>',
  props: ['left']
}

// Mock Inertia Link component
const Link = {
  template: '<a :href="href" :class="classValue" :data-test="dataTest"><slot/></a>',
  props: {
    href: String,
    classValue: String,
    dataTest: String
  }
}

// Mock route helper
const route = (name) => {
  const routes = {
    'terms': '/terms',
    'privacy': '/privacy'
  }
  return routes[name] || '#'
}

describe('AppFooter', () => {
  it('renders current year in copyright', () => {
    const wrapper = mount(AppFooter, {
      global: {
        stubs: {
          VFooter,
          VContainer,
          VRow,
          VCol,
          VBtn,
          VIcon,
          Link
        },
        mocks: {
          route
        }
      }
    })
    const currentYear = new Date().getFullYear()
    expect(wrapper.text()).toContain(`© ${currentYear} TODOKE`)
  })

  it('has data-test attribute', () => {
    const wrapper = mount(AppFooter, {
      global: {
        stubs: {
          VFooter,
          VContainer,
          VRow,
          VCol,
          VBtn,
          VIcon,
          Link
        },
        mocks: {
          route
        }
      }
    })
    expect(wrapper.attributes('data-test')).toBe('app-footer')
  })

  it('contains links to terms and privacy', () => {
    const wrapper = mount(AppFooter, {
      global: {
        stubs: {
          VFooter,
          VContainer,
          VRow,
          VCol,
          VBtn,
          VIcon,
          Link
        },
        mocks: {
          route
        }
      }
    })
    expect(wrapper.find('[data-test="terms-link"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="privacy-link"]').exists()).toBe(true)
  })
})
