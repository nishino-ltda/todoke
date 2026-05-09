import { mount } from '@vue/test-utils'
import AppFooter from '../AppFooter.vue'
import { describe, it, expect, vi } from 'vitest'

// Mock vue-i18n
vi.mock('vue-i18n', () => ({
  useI18n: () => ({
    t: (key, params = {}) => {
      const translations = {
        'footer.copyright': '© {year} TODOKE',
        'footer.terms': 'Terms of Service',
        'footer.privacy': 'Privacy Policy'
      }
      let result = translations[key] || key
      if (params) {
        Object.entries(params).forEach(([k, v]) => {
          result = result.replace(new RegExp(`{${k}}`, 'g'), v)
        })
      }
      return result
    }
  })
}))

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
  template: '<a :href="href"><slot/></a>',
  props: ['href']
}

// Mock route helper
const route = (name) => {
  const routes = {
    'terms': '/terms',
    'privacy': '/privacy'
  }
  return routes[name] || '#'
}

// Mock translation function
const $t = (key, params = {}) => {
  const translations = {
    'footer.copyright': '© {year} TODOKE',
    'footer.terms': 'Terms of Service',
    'footer.privacy': 'Privacy Policy'
  }
  let result = translations[key] || key
  if (params) {
    Object.entries(params).forEach(([k, v]) => {
      result = result.replace(new RegExp(`{${k}}`, 'g'), v)
    })
  }
  return result
}

const vuetifyComponents = {
  VFooter,
  VContainer,
  VRow,
  VCol,
  VBtn,
  VIcon,
  Link
}

const globalMocks = {
  route,
  $t,
  t: $t // Some components might use destructuring from useI18n
}

describe('AppFooter', () => {
  it('renders current year in copyright', () => {
    const wrapper = mount(AppFooter, {
      global: {
        stubs: vuetifyComponents,
        mocks: globalMocks
      }
    })
    const currentYear = new Date().getFullYear()
    expect(wrapper.text()).toContain(`© ${currentYear} TODOKE`)
  })

  it('has data-test attribute', () => {
    const wrapper = mount(AppFooter, {
      global: {
        stubs: vuetifyComponents,
        mocks: globalMocks
      }
    })
    expect(wrapper.attributes('data-test')).toBe('app-footer')
  })

  it('contains links to terms and privacy', () => {
    const wrapper = mount(AppFooter, {
      global: {
        stubs: vuetifyComponents,
        mocks: globalMocks
      }
    })
    expect(wrapper.find('[data-test="terms-link"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="privacy-link"]').exists()).toBe(true)
  })
})

