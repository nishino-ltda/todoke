import { shallowMount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import AppHeader from '@/components/AppHeader.vue'
import AppFooter from '@/components/AppFooter.vue'

describe('GuestLayout', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('renders with minimal header and footer', () => {
    const wrapper = shallowMount(GuestLayout, {
      slots: {
        default: '<div data-test="guest-content">Test Content</div>'
      },
      global: {
        stubs: {
          AppHeader: true,
          AppFooter: true,
          VApp: {
            template: '<div><slot /></div>'
          },
          VMain: {
            template: '<div><slot /></div>'
          },
          VContainer: {
            template: '<div><slot /></div>'
          }
        },
        mocks: {
          route: () => ({})
        }
      }
    })

    expect(wrapper.find('[data-test="guest-layout"]').exists()).toBe(true)
    expect(wrapper.find('app-header-stub').exists()).toBe(true)
    expect(wrapper.find('app-footer-stub').exists()).toBe(true)
    
    const content = wrapper.find('[data-test="guest-content"]')
    expect(content.exists()).toBe(true)
    expect(content.text()).toBe('Test Content')
  })
})
