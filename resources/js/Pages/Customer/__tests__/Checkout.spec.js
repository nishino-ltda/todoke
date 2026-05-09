import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { createPinia, setActivePinia } from 'pinia'
import { describe, it, expect, beforeEach } from 'vitest'
import Checkout from '@/Pages/Customer/Checkout.vue'
import { nextTick } from 'vue'

const i18n = createI18n({
  legacy: false,
  locale: 'pt-BR',
  messages: {
    'pt-BR': {
      checkout: {
        title: 'Checkout do Cliente',
        subtitle: 'Finalize seu pedido'
      }
    },
    en: {
      checkout: {
        title: 'Customer Checkout',
        subtitle: 'Complete your order'
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

describe('Checkout', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('renders properly in pt-BR', () => {
    i18n.global.locale.value = 'pt-BR'
    const wrapper = mount(Checkout, {
      global: {
        plugins: [i18n],
        stubs
      }
    })
    expect(wrapper.find('[data-cy="customer-checkout"]').exists()).toBe(true)
    expect(wrapper.find('h1').text()).toBe('Checkout do Cliente')
    expect(wrapper.find('p').text()).toBe('Finalize seu pedido')
  })

  it('renders properly in en', async () => {
    i18n.global.locale.value = 'en'
    const wrapper = mount(Checkout, {
      global: {
        plugins: [i18n],
        stubs
      }
    })
    expect(wrapper.find('h1').text()).toBe('Customer Checkout')
    expect(wrapper.find('p').text()).toBe('Complete your order')
  })
})

