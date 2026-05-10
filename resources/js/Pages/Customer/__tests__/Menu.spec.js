import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { createPinia, setActivePinia } from 'pinia'
import { describe, it, expect, beforeEach } from 'vitest'
import Menu from '@/Pages/Customer/Menu.vue'

const i18n = createI18n({
  legacy: false,
  locale: 'pt-BR',
  messages: {
    'pt-BR': {
      menu: {
        title: 'Menu do Cliente',
        subtitle: 'Explore os produtos disponíveis',
        loading: 'Carregando cardápio...',
        not_found: 'Parceiro não encontrado',
      },
    },
    en: {
      menu: {
        title: 'Customer Menu',
        subtitle: 'Browse available products',
        loading: 'Loading menu...',
        not_found: 'Partner not found',
      },
    },
  },
})

const stubs = {
  GuestLayout: {
    template: '<div class="guest-layout"><slot /></div>',
  },
  ProductList: {
    template: '<div class="product-list-stub" />',
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
    template: '<button class="v-btn"><slot /></button>',
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

describe('Menu Page', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('renders properly in pt-BR', () => {
    i18n.global.locale.value = 'pt-BR'
    const wrapper = mount(Menu, {
      props: {
        partner: { id: 1, name: 'Test Partner' },
        products: [{ id: 1, name: 'Product', price: 10 }],
      },
      global: {
        plugins: [i18n],
        stubs,
      },
    })
    expect(wrapper.find('[data-cy="customer-menu"]').exists()).toBe(true)
  })

  it('renders properly in en', async () => {
    i18n.global.locale.value = 'en'
    const wrapper = mount(Menu, {
      props: {
        partner: { id: 1, name: 'Test Partner' },
        products: [{ id: 1, name: 'Product', price: 10 }],
      },
      global: {
        plugins: [i18n],
        stubs,
      },
    })
    expect(wrapper.find('[data-cy="customer-menu"]').exists()).toBe(true)
  })
})
