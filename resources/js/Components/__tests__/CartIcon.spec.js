import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { createPinia } from 'pinia'
import { useCartStore } from '@/stores/cart'
import CartIcon from '../CartIcon.vue'
import { vi } from 'vitest'
import { router } from '@inertiajs/vue3'
import { nextTick } from 'vue'

const i18n = createI18n({
  legacy: false,
  locale: 'pt-BR',
  messages: {
    'pt-BR': {
      cart: {
        title: 'Seu Carrinho',
        empty: 'Seu carrinho está vazio.',
        total: 'Total',
        checkout: 'Finalizar Compra',
        close: 'Fechar'
      }
    },
    en: {
      cart: {
        title: 'Your Cart',
        empty: 'Your cart is empty.',
        total: 'Total',
        checkout: 'Checkout',
        close: 'Close'
      }
    }
  }
})

// Stub Vuetify components
const vuetifyStubs = {
  'v-btn': {
    template: '<button class="v-btn"><slot/></button>'
  },
  'v-badge': {
    template: `
      <div>
        <div class="v-badge__badge" v-if="modelValue">{{ content }}</div>
        <slot/>
      </div>
    `,
    props: ['content', 'modelValue']
  },
  'v-dialog': {
    template: '<div v-if="modelValue"><slot/></div>',
    props: ['modelValue']
  },
  'v-card': {
    template: '<div><slot/></div>'
  },
  'v-card-title': {
    template: '<div class="v-card-title"><slot/></div>'
  },
  'v-card-text': {
    template: '<div class="v-card-text"><slot/></div>'
  },
  'v-list-item': {
    template: '<div><slot/></div>'
  },
  'v-list-item-content': {
    template: '<div><slot/></div>'
  },
  'v-list-item-title': {
    template: '<div><slot/></div>'
  },
  'v-list-item-subtitle': {
    template: '<div><slot/></div>'
  },
  'v-list-item-action': {
    template: '<div><slot/></div>'
  },
  'v-list': {
    template: '<div><slot/></div>'
  },
  'v-divider': {
    template: '<div><slot/></div>'
  },
  'v-spacer': {
    template: '<div><slot/></div>'
  },
  'v-card-actions': {
    template: '<div class="v-card-actions"><slot/></div>'
  },
  'v-icon': {
    template: '<div><slot/></div>'
  }
}

// Mock localStorage
const localStorageMock = (() => {
  let store = {}
  return {
    getItem: vi.fn(key => store[key] || null),
    setItem: vi.fn((key, value) => { store[key] = value.toString() }),
    clear: vi.fn(() => { store = {} }),
    removeItem: vi.fn(key => { delete store[key] })
  }
})()
vi.stubGlobal('localStorage', localStorageMock)

describe('CartIcon', () => {
  let wrapper
  let cartStore

  beforeEach(() => {
    localStorage.clear()
    const pinia = createPinia()
    cartStore = useCartStore(pinia)
    
    // Mock Inertia's router.visit
    router.visit = vi.fn()
    
    wrapper = mount(CartIcon, {
      global: {
        plugins: [pinia, i18n],
        stubs: vuetifyStubs
      }
    })
  })

  it('renders correctly', () => {
    expect(wrapper.find('[data-cy="cart-icon"]').exists()).toBe(true)
  })

  it('shows badge with item count', async () => {
    // Test with empty cart
    expect(wrapper.find('[data-cy="cart-icon"]').exists()).toBe(true)
    expect(wrapper.html()).not.toContain('v-badge__badge')

    // Test with items
    cartStore.items = [{ id: 1, name: 'Product', price: 10, quantity: 1 }]
    await wrapper.vm.$nextTick()
    expect(wrapper.html()).toContain('v-badge__badge')
    expect(wrapper.html()).toContain('1')
  })

  it('opens dialog when clicked', async () => {
    await wrapper.find('[data-cy="cart-icon"] button').trigger('click')
    expect(wrapper.vm.showCartDialog).toBe(true)
  })

  it('removes item when delete clicked', async () => {
    cartStore.items = [{ id: 1, name: 'Product', price: 10, quantity: 1 }]
    cartStore.removeItem = vi.fn()
    await wrapper.find('[data-cy="cart-icon"] button').trigger('click') // Open dialog
    
    await wrapper.find('[data-cy="remove-item"]').trigger('click')
    expect(cartStore.removeItem).toHaveBeenCalledWith(1)
  })

  it('navigates to checkout on checkout button click', async () => {
    cartStore.items = [{ id: 1, name: 'Product', price: 10, quantity: 1 }]
    await wrapper.find('[data-cy="cart-icon"] button').trigger('click') // Open dialog
    
    await wrapper.find('[data-cy="checkout-button"]').trigger('click')
    expect(router.visit).toHaveBeenCalledWith('/customer/checkout')
  })

  it('renders text in correct language', async () => {
    i18n.global.locale.value = 'pt-BR'
    await wrapper.find('[data-cy="cart-icon"] button').trigger('click') // Open dialog
    
    expect(wrapper.find('.v-card-title').text()).toBe('Seu Carrinho')
    expect(wrapper.find('.v-card-text').text()).toBe('Seu carrinho está vazio.')
    expect(wrapper.find('[data-cy="checkout-button"]').text()).toBe('Finalizar Compra')

    i18n.global.locale.value = 'en'
    await nextTick()
    expect(wrapper.find('.v-card-title').text()).toBe('Your Cart')
    expect(wrapper.find('.v-card-text').text()).toBe('Your cart is empty.')
    expect(wrapper.find('[data-cy="checkout-button"]').text()).toBe('Checkout')
  })
})
