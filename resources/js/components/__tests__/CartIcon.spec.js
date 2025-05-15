import { mount } from '@vue/test-utils'
import { createPinia } from 'pinia'
import { useCartStore } from '@/stores/cart'
import CartIcon from '../CartIcon.vue'
import { vi } from 'vitest'
import { router } from '@inertiajs/vue3'

// Stub Vuetify components
const vuetifyStubs = {
  'v-btn': {
    template: '<button @click="$emit(\'click\')"><slot/></button>'
  },
  'v-badge': {
    template: `
      <div>
        <div class="v-badge__badge" v-if="value">{{ content }}</div>
        <slot/>
      </div>
    `,
    props: ['content', 'value']
  },
  'v-dialog': {
    template: '<div v-if="modelValue"><slot/></div>',
    props: ['modelValue']
  },
  'v-card': {
    template: '<div><slot/></div>'
  },
  'v-card-title': {
    template: '<div><slot/></div>'
  },
  'v-card-text': {
    template: '<div><slot/></div>'
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
    template: '<div><slot/></div>'
  },
  'v-icon': {
    template: '<div><slot/></div>'
  }
}

describe('CartIcon', () => {
  let wrapper
  let cartStore

  beforeEach(() => {
    const pinia = createPinia()
    cartStore = useCartStore(pinia)
    
    // Mock Inertia's router.visit
    router.visit = vi.fn()
    
    wrapper = mount(CartIcon, {
      global: {
        plugins: [pinia],
        stubs: vuetifyStubs
      }
    })
  })

  it('renders correctly', () => {
    expect(wrapper.find('[data-test="cart-icon"]').exists()).toBe(true)
  })

  it('shows badge with item count', async () => {
    // Test with empty cart
    expect(wrapper.find('[data-test="cart-icon"]').exists()).toBe(true)
    expect(wrapper.html()).not.toContain('v-badge__badge')

    // Test with items
    cartStore.items = [{ id: 1, name: 'Product', price: 10, quantity: 1 }]
    await wrapper.vm.$nextTick()
    expect(wrapper.html()).toContain('v-badge__badge')
    expect(wrapper.html()).toContain('1')
  })

  it('opens dialog when clicked', async () => {
    await wrapper.find('[data-test="cart-icon"] button').trigger('click')
    expect(wrapper.vm.showCartDialog).toBe(true)
  })

  it('removes item when delete clicked', async () => {
    cartStore.items = [{ id: 1, name: 'Product', price: 10, quantity: 1 }]
    cartStore.removeItem = vi.fn()
    await wrapper.find('[data-test="cart-icon"] button').trigger('click') // Open dialog
    
    await wrapper.find('[data-test="remove-item"]').trigger('click')
    expect(cartStore.removeItem).toHaveBeenCalledWith(1)
  })

  it('navigates to checkout on checkout button click', async () => {
    cartStore.items = [{ id: 1, name: 'Product', price: 10, quantity: 1 }]
    await wrapper.find('[data-test="cart-icon"] button').trigger('click') // Open dialog
    
    await wrapper.find('[data-test="checkout-button"]').trigger('click')
    expect(router.visit).toHaveBeenCalledWith('/checkout')
  })
})
