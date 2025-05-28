import { mount } from '@vue/test-utils'
import AppFooter from './AppFooter.vue'
import { createTestingPinia } from '@pinia/testing'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import { VApp } from 'vuetify/Components/VApp'
import { VMain } from 'vuetify/Components/VMain'
import { VLayout } from 'vuetify/Components/VLayout'

const TestWrapper = {
  components: { VApp, VMain, VLayout, AppFooter },
  template: `
    <v-app>
      <v-main>
        <v-layout>
          <app-footer />
        </v-layout>
      </v-main>
    </v-app>
  `
}

describe('AppFooter', () => {
  const vuetify = createVuetify({ components, directives })

  const mockRoute = (name) => {
    const routes = {
      terms: '/terms',
      privacy: '/privacy'
    }
    return routes[name] || '/'
  }

  const setup = () => {
    return mount(TestWrapper, {
      global: {
        plugins: [createTestingPinia(), vuetify],
        mocks: {
          route: mockRoute
        },
        stubs: {
          Link: {
            template: '<a :href="href"><slot /></a>',
            props: ['href']
          }
        }
      }
    })
  }

  it('renders current year in copyright', () => {
    const wrapper = setup()
    const currentYear = new Date().getFullYear()
    expect(wrapper.text()).toContain(`© ${currentYear} TODOKE`)
  })

  it('renders terms and privacy links', () => {
    const wrapper = setup()
    expect(wrapper.find('[data-test="terms-link"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="privacy-link"]').exists()).toBe(true)
  })

  it('renders social media links', () => {
    const wrapper = setup()
    expect(wrapper.find('[data-test="facebook-link"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="twitter-link"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="instagram-link"]').exists()).toBe(true)
  })

  it('has proper Vuetify classes', () => {
    const wrapper = setup()
    expect(wrapper.find('.app-footer').exists()).toBe(true)
    expect(wrapper.find('.text-center').exists()).toBe(true)
    expect(wrapper.find('.text-right').exists()).toBe(true)
  })

  it('matches snapshot', () => {
    const wrapper = setup()
    expect(wrapper.html()).toMatchSnapshot()
  })
})
