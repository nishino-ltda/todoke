import { mount } from '@vue/test-utils'
import { createPinia } from 'pinia'
import { useCartStore } from '@/stores/cart'
import Checkout from '../Checkout.vue'
import { createRouter, createMemoryHistory } from 'vue-router'

// Stub Vuetify components
const vuetifyStubs = {
  'v-container': {
    template: '<div><slot/></div>'
  },
  'v-row': {
    template: '<div><slot/></div>'
  },
  'v-col': {
    template: '<div><slot/></div>'
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
  'v-divider': {
    template: '<div><slot/></div>'
  },
  'v-text-field': {
    template: '<input :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" data-test="address" />',
    props: ['modelValue']
  },
  'v-btn': {
    template: '<button @click="$emit(\'click\')" data-test="submit-order"><slot/></button>'
  },
  'v-form': {
    template: '<form @submit.prevent="$emit(\'submit\')"><slot/></form>'
  }
}

describe('Checkout', () => {
  let wrapper
  let cartStore
  let router

  beforeEach(() => {
    router = createRouter({
      history: createMemoryHistory(),
      routes: [
        { path: '/checkout', component: Checkout }
      ]
    })

    const pinia = createPinia()
    cartStore = useCartStore(pinia)
    
    wrapper = mount(Checkout, {
      global: {
        plugins: [pinia, router],
        stubs: vuetifyStubs
      }
    })
  })

  it('renders checkout form', () => {
    expect(wrapper.find('form').exists()).toBe(true)
  })

  it('shows cart items', async () => {
    cartStore.items = [
      { id: 1, name: 'Pizza', price: 10, quantity: 1 },
      { id: 2, name: 'Burger', price: 8, quantity: 2 }
    ]
    await wrapper.vm.$nextTick()

    expect(wrapper.html()).toContain('Pizza')
    expect(wrapper.html()).toContain('Burger')
    expect(wrapper.html()).toContain('$26.00') // Total
  })

  it('requires delivery address', async () => {
    const submitBtn = wrapper.find('[data-test="submit-order"]')
    expect(submitBtn.attributes('disabled')).toBeDefined()
  })

  it('submits order when form is valid', async () => {
    cartStore.items = [{ id: 1, name: 'Pizza', price: 10, quantity: 1 }]
    cartStore.submitOrder = vi.fn().mockResolvedValue({ success: true })
    await wrapper.vm.$nextTick()

    await wrapper.find('[data-test="address"]').setValue('123 Main St')
    
    // Call the submit method directly on the component
    await wrapper.vm.submitOrder()
    
    expect(cartStore.submitOrder).toHaveBeenCalledWith({
      address: '123 Main St',
      items: cartStore.items
    })
  })
})
