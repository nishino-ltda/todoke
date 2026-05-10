import { mount } from '@vue/test-utils'
import { describe, it, expect, beforeEach } from 'vitest'
import { createI18n } from 'vue-i18n'
import { createPinia, setActivePinia } from 'pinia'
import Menu from '@/Pages/Customer/Menu.vue'

const stubs = {
  GuestLayout: {
    template: '<div class="guest-layout"><slot /></div>',
  },
  ProductList: {
    template: '<div class="product-list-stub"><slot /></div>',
    props: ['products'],
  },
  ProductDetailsModal: {
    template: '<div class="product-details-stub" />',
    props: ['product'],
  },
  'v-progress-circular': {
    template: '<div class="v-progress-circular" />',
  },
  'v-icon': {
    template: '<div class="v-icon"><slot /></div>',
  },
  'v-btn': {
    template: '<button class="v-btn" @click="$emit(\'click\')"><slot /></button>',
  },
  'v-row': {
    template: '<div class="v-row"><slot /></div>',
  },
  'v-col': {
    template: '<div class="v-col"><slot /></div>',
  },
  'v-divider': {
    template: '<hr class="v-divider" />',
  },
}

const ptBrMessages = {
  menu: {
    title: 'Menu do Cliente',
    subtitle: 'Explore os produtos disponíveis',
    loading: 'Carregando cardápio...',
    not_found: 'Parceiro não encontrado',
  },
}

const enMessages = {
  menu: {
    title: 'Customer Menu',
    subtitle: 'Browse available products.',
    loading: 'Loading menu...',
    not_found: 'Partner not found',
  },
}

function createWrapper(locale = 'pt-BR', props = {}) {
  const messages = locale === 'pt-BR' ? ptBrMessages : enMessages
  const i18n = createI18n({ locale, messages: { [locale]: messages } })
  const pinia = createPinia()
  setActivePinia(pinia)

  return mount(Menu, {
    props: {
      partner: props.partner || null,
      products: props.products || [],
    },
    global: {
      plugins: [i18n, pinia],
      stubs,
    },
  })
}

describe('Menu.vue', () => {
  it('renders in pt-BR by default', () => {
    const wrapper = createWrapper('pt-BR', {
      partner: { id: 1, name: 'Test Partner' },
      products: [{ id: 1, name: 'Product', price: 10 }],
    })
    expect(wrapper.find('[data-cy="customer-menu"]').exists()).toBe(true)
  })

  it('renders in en when locale is switched', () => {
    const wrapper = createWrapper('en', {
      partner: { id: 1, name: 'Test Partner' },
      products: [{ id: 1, name: 'Product', price: 10 }],
    })
    expect(wrapper.find('[data-cy="customer-menu"]').exists()).toBe(true)
  })
})
