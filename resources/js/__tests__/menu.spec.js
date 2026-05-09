import { mount } from '@vue/test-utils'
import { describe, it, expect, beforeEach } from 'vitest'
import { createI18n } from 'vue-i18n'
import { createPinia, setActivePinia } from 'pinia'
import Menu from '@/Pages/Customer/Menu.vue'

const stubs = {
  AuthenticatedLayout: {
    template: '<div class="authenticated-layout"><slot /></div>'
  }
}

const ptBrMessages = {
  menu: {
    title: 'Menu do Cliente',
    subtitle: 'Explore os produtos disponíveis'
  }
}

const enMessages = {
  menu: {
    title: 'Customer Menu',
    subtitle: 'Browse available products.'
  }
}

function createWrapper(locale = 'pt-BR') {
  const messages = locale === 'pt-BR' ? ptBrMessages : enMessages
  const i18n = createI18n({ locale, messages: { [locale]: messages } })
  const pinia = createPinia()
  setActivePinia(pinia)

  return mount(Menu, {
    global: {
      plugins: [i18n, pinia],
      stubs
    }
  })
}

describe('Menu.vue', () => {
  it('renders in pt-BR by default', () => {
    const wrapper = createWrapper('pt-BR')
    expect(wrapper.find('[data-cy="customer-menu"]').exists()).toBe(true)
    expect(wrapper.text()).toContain('Menu do Cliente')
    expect(wrapper.text()).toContain('Explore os produtos disponíveis')
  })

  it('renders in en when locale is switched', () => {
    const wrapper = createWrapper('en')
    expect(wrapper.find('[data-cy="customer-menu"]').exists()).toBe(true)
    expect(wrapper.text()).toContain('Customer Menu')
    expect(wrapper.text()).toContain('Browse available products.')
  })
})

