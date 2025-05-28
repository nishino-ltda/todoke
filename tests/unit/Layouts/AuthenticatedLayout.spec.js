import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'

describe('AuthenticatedLayout', () => {
  const vuetify = createVuetify({ components, directives })

  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('renders with full header and footer', () => {
    const wrapper = mount(AuthenticatedLayout, {
      slots: {
        default: '<div data-test="authenticated-content">Test Content</div>'
      },
      global: {
        plugins: [vuetify],
        stubs: {
          AppHeader: true,
          AppFooter: true
        },
        mocks: {
          route: () => ({})
        }
      }
    })

    expect(wrapper.find('[data-test="authenticated-layout"]').exists()).toBe(true)
    expect(wrapper.findComponent(AppHeader).exists()).toBe(true)
    expect(wrapper.findComponent(AppFooter).exists()).toBe(true)
    
    const content = wrapper.find('[data-test="authenticated-content"]')
    expect(content.exists()).toBe(true)
    expect(content.text()).toBe('Test Content')
  })

  it('renders navigation items', () => {
    const wrapper = mount(AuthenticatedLayout, {
      global: {
        plugins: [vuetify],
        stubs: {
          AppHeader: true,
          AppFooter: true
        }
      }
    })

    const navItems = wrapper.findAllComponents({ name: 'VListItem' })
    expect(navItems.length).toBe(3)
    expect(navItems[0].attributes('data-test')).toBe('nav-item-dashboard')
    expect(navItems[1].attributes('data-test')).toBe('nav-item-profile')
    expect(navItems[2].attributes('data-test')).toBe('nav-item-settings')
  })
})
