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
        subtitle: 'Finalize seu pedido',
        login_required_title: 'Login Necessário',
        login_required_message: 'Faça login ou crie uma conta para finalizar seu pedido.',
        empty_cart_title: 'Seu carrinho está vazio',
        empty_cart_message: 'Adicione itens ao carrinho antes de ir para o checkout.',
        browse_menu: 'Ver Cardápio',
        order_summary: 'Resumo do Pedido',
      },
      auth: {
        login: 'Entrar',
        register: 'Registrar',
      },
      cart: {
        subtotal: 'Subtotal',
        delivery_fee: 'Taxa de Entrega',
        total: 'Total',
      },
    },
    en: {
      checkout: {
        title: 'Customer Checkout',
        subtitle: 'Complete your order',
        login_required_title: 'Login Required',
        login_required_message: 'Please log in or create an account to complete your order.',
        empty_cart_title: 'Your cart is empty',
        empty_cart_message: 'Add items to your cart before proceeding to checkout.',
        browse_menu: 'Browse Menu',
        order_summary: 'Order Summary',
      },
      auth: {
        login: 'Login',
        register: 'Register',
      },
      cart: {
        subtotal: 'Subtotal',
        delivery_fee: 'Delivery Fee',
        total: 'Total',
      },
    },
  },
})

const stubs = {
  CustomerLayout: {
    template: '<div class="customer-layout"><slot /></div>',
  },
  CheckoutForm: {
    template: '<div class="checkout-form-stub" />',
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
  'v-card': {
    template: '<div class="v-card"><slot /></div>',
  },
  'v-card-title': {
    template: '<div class="v-card-title"><slot /></div>',
  },
  'v-card-text': {
    template: '<div class="v-card-text"><slot /></div>',
  },
  'v-list': {
    template: '<div class="v-list"><slot /></div>',
  },
  'v-list-item': {
    template: '<div class="v-list-item"><slot /></div>',
  },
  'v-list-item-content': {
    template: '<div class="v-list-item-content"><slot /></div>',
  },
  'v-list-item-title': {
    template: '<div class="v-list-item-title"><slot /></div>',
  },
  'v-list-item-subtitle': {
    template: '<div class="v-list-item-subtitle"><slot /></div>',
  },
  'v-list-item-action': {
    template: '<div class="v-list-item-action"><slot /></div>',
  },
  'v-divider': {
    template: '<hr class="v-divider" />',
  },
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
        stubs,
      },
    })
    expect(wrapper.find('[data-cy="customer-checkout"]').exists()).toBe(true)
  })

  it('renders properly in en', async () => {
    i18n.global.locale.value = 'en'
    const wrapper = mount(Checkout, {
      global: {
        plugins: [i18n],
        stubs,
      },
    })
    expect(wrapper.find('[data-cy="customer-checkout"]').exists()).toBe(true)
  })
})
