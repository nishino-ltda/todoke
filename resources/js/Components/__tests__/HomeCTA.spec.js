import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { createPinia, setActivePinia } from 'pinia'
import HomeCTA from '@/Components/HomeCTA.vue'

// Mock the route() global function if needed, but since I used /register directly in HomeCTA.vue
// I'll just check for the href attribute.

const ptBrMessages = {
  home: {
    cta: {
      title: 'Pronto para Começar?',
      description: 'Junte-se à plataforma...',
      customer: {
        title: 'Cliente',
        description: 'Peça de seus restaurantes...',
        button: 'Quero Pedir'
      },
      partner: {
        title: 'Parceiro',
        description: 'Divulgue seu cardápio...',
        button: 'Quero Vender'
      },
      courier: {
        title: 'Entregador',
        description: 'Trabalhe no seu horário...',
        button: 'Quero Entregar'
      }
    }
  }
}

const enMessages = {
  home: {
    cta: {
      title: 'Ready to Start?',
      description: 'Join the platform...',
      customer: {
        title: 'Customer',
        description: 'Order from your favorite...',
        button: 'Order Now'
      },
      partner: {
        title: 'Partner',
        description: 'Showcase your menu...',
        button: 'Start Selling'
      },
      courier: {
        title: 'Courier',
        description: 'Work on your schedule...',
        button: 'Start Delivering'
      }
    }
  }
}

function createWrapper(locale = 'pt-BR') {
  const messages = locale === 'pt-BR' ? ptBrMessages : enMessages
  const i18n = createI18n({ 
    legacy: false,
    locale, 
    messages: { [locale]: messages } 
  })
  const pinia = createPinia()
  setActivePinia(pinia)

  const stubs = {
    VContainer: { template: '<div><slot /></div>' },
    VRow: { template: '<div><slot /></div>' },
    VCol: { template: '<div><slot /></div>' },
    VCard: { template: '<div><slot /></div>' },
    VCardTitle: { template: '<div><slot /></div>' },
    VCardText: { template: '<div><slot /></div>' },
    VCardActions: { template: '<div><slot /></div>' },
    VBtn: { template: '<a :href="href"><slot /></a>', props: ['href'] },
    VIcon: { template: '<i></i>' }
  }

  return mount(HomeCTA, {
    global: { plugins: [i18n, pinia], stubs }
  })
}

describe('HomeCTA', () => {
  it('renders in pt-BR by default', () => {
    const wrapper = createWrapper('pt-BR')
    expect(wrapper.find('[data-test="cta-title"]').text()).toBe('Pronto para Começar?')
    expect(wrapper.find('[data-test="cta-customer"] h2, [data-test="cta-customer"] .text-h5').text()).toBe('Cliente')
    expect(wrapper.find('[data-test="cta-customer-btn"]').text()).toBe('Quero Pedir')
    expect(wrapper.find('[data-test="cta-customer-btn"]').attributes('href')).toBe('/register?type=customer')
  })

  it('renders in en when locale is switched', () => {
    const wrapper = createWrapper('en')
    expect(wrapper.find('[data-test="cta-title"]').text()).toBe('Ready to Start?')
    expect(wrapper.find('[data-test="cta-customer"] h2, [data-test="cta-customer"] .text-h5').text()).toBe('Customer')
    expect(wrapper.find('[data-test="cta-customer-btn"]').text()).toBe('Order Now')
    expect(wrapper.find('[data-test="cta-customer-btn"]').attributes('href')).toBe('/register?type=customer')
  })

  it('renders all three CTA cards', () => {
    const wrapper = createWrapper('pt-BR')
    expect(wrapper.find('[data-test="cta-customer"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="cta-partner"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="cta-courier"]').exists()).toBe(true)
  })
})
