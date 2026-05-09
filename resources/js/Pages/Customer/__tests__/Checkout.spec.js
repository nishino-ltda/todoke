import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { describe, it, expect, beforeEach } from 'vitest'
import Checkout from '@/Pages/Customer/Checkout.vue'

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

  it('renders properly', () => {
    const wrapper = mount(Checkout, {
      global: {
        stubs
      }
    })
    expect(wrapper.find('[data-test="customer-checkout"]').exists()).toBe(true)
    expect(wrapper.text()).toContain('Customer Checkout')
    expect(wrapper.text()).toContain('Complete your order.')
  })
})

