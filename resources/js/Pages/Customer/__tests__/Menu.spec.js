import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { createPinia, setActivePinia } from 'pinia'
import { describe, it, expect, beforeEach } from 'vitest'
import Menu from '@/Pages/Customer/Menu.vue'
import { nextTick } from 'vue'

const i18n = createI18n({
  legacy: false,
  locale: 'pt-BR',
  messages: {
    'pt-BR': {
      menu: {
        title: 'Menu do Cliente',
        subtitle: 'Explore os produtos disponíveis'
      }
    },
    en: {
      menu: {
        title: 'Customer Menu',
        subtitle: 'Browse available products'
      }
    }
  }
})

// Stub components
const stubs = {
  AuthenticatedLayout: {
    template: '<div class="authenticated-layout"><slot /></div>'
  }
}

describe('Menu Page', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('renders properly in pt-BR', () => {
    i18n.global.locale.value = 'pt-BR'
    const wrapper = mount(Menu, {
      global: {
        plugins: [i18n],
        stubs
      }
    })
    expect(wrapper.find('[data-cy="customer-menu"]').exists()).toBe(true)
    expect(wrapper.find('h1').text()).toBe('Menu do Cliente')
    expect(wrapper.find('p').text()).toBe('Explore os produtos disponíveis')
  })

  it('renders properly in en', async () => {
    i18n.global.locale.value = 'en'
    const wrapper = mount(Menu, {
      global: {
        plugins: [i18n],
        stubs
      }
    })
    expect(wrapper.find('h1').text()).toBe('Customer Menu')
    expect(wrapper.find('p').text()).toBe('Browse available products')
  })
})
