import { mount } from '@vue/test-utils'
import { describe, it, expect, beforeEach } from 'vitest'
import Menu from '@/Pages/Customer/Menu.vue'
import { createPinia, setActivePinia } from 'pinia'

// Stub components
const stubs = {
  AuthenticatedLayout: {
    template: '<div class="authenticated-layout"><slot /></div>'
  }
}

describe('Menu.vue', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('renders properly', () => {
    const wrapper = mount(Menu, {
      global: {
        stubs
      }
    })
    expect(wrapper.find('[data-test="customer-menu"]').exists()).toBe(true)
    expect(wrapper.text()).toContain('Customer Menu')
    expect(wrapper.text()).toContain('Browse available products.')
  })
})

