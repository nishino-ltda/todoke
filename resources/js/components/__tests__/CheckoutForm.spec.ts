import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import { setActivePinia, createPinia } from 'pinia'
import CheckoutForm from '@/components/CheckoutForm.vue'
import type { CheckoutFormType } from '../CheckoutForm.vue'
import useCartStore from '@/stores/cart'

const vuetify = createVuetify({ components, directives })

function mountWithVuetify(component: CheckoutFormType, options: any = {}) {
  return mount(component, {
    ...options,
    global: {
      plugins: [vuetify, createPinia()]
    }
  })
}

// Mock child components
vi.mock('../AddressInput.vue', () => ({
  default: {
    template: '<textarea data-testid="address-input"></textarea>',
    emits: ['update:address']
  }
}))

vi.mock('../PaymentMethodInput.vue', () => ({
  default: {
    template: '<select data-testid="payment-select"><option>Credit Card</option></select>',
    emits: ['update:paymentMethod']
  }
}))

// Mock stores
vi.mock('@/stores/cart', () => ({
  __esModule: true,
  default: vi.fn(),
  useCartStore: vi.fn(() => ({
    items: [],
    clearCart: vi.fn(),
    $reset: vi.fn()
  }))
}))

// Type for order service
type OrderService = {
  createOrder: (orderData: {
    items: Array<{
      id: number
      name: string
      price: number
      quantity: number
      selectedAddons?: Array<{
        id: number
        name: string
        price: number
      }>
    }>
    address: string
    paymentMethod: string
  }) => Promise<void>
}

// Mock services
const mockCreateOrder = vi.fn()
vi.mock('@/services/order', () => ({
  default: vi.fn(() => ({
    createOrder: mockCreateOrder
  })) as () => OrderService
}))

describe('CheckoutForm', () => {
  let wrapper: any
  let cartStore: any

  beforeEach(() => {
    setActivePinia(createPinia())
    cartStore = useCartStore()
    // Ensure cartStore is initialized as an object
    if (!cartStore) cartStore = {}
    // Set mock properties directly
    cartStore.items = [{ id: 1, name: 'Test', price: 10, quantity: 1 }]
    cartStore.clearCart = vi.fn()
    cartStore.$reset = vi.fn()
    
    wrapper = mountWithVuetify(CheckoutForm as unknown as CheckoutFormType)
  })

  it('renders form with submit button', () => {
    expect(wrapper.find('form').exists()).toBe(true)
    expect(wrapper.find('button[type="submit"]').exists()).toBe(true)
  })

  it('submits order data', async () => {
    // Set form data
    await wrapper.find('[data-testid="address-input"]').setValue('123 Main St')
    await wrapper.find('[data-testid="payment-select"]').setValue('Credit Card')
    
    // Submit form
    await wrapper.find('form').trigger('submit.prevent')
    
    // Verify API call
    expect(mockCreateOrder).toHaveBeenCalledWith({
      address: '123 Main St',
      paymentMethod: 'Credit Card',
      items: [{ id: 1, name: 'Test', price: 10, quantity: 1 }]
    })
    
    // Verify cart cleared
    expect(cartStore.clearCart).toHaveBeenCalled()
  })

  it('handles submission errors', async () => {
    mockCreateOrder.mockRejectedValue(new Error('API Error'))
    
    // Submit form
    await wrapper.find('form').trigger('submit.prevent')
    
    // Verify error handling
    expect(wrapper.text()).toContain('Error submitting order')
    expect(cartStore.clearCart).not.toHaveBeenCalled()
  })
})
