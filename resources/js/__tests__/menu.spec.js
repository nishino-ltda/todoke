import { mount, flushPromises } from '@vue/test-utils'
import { vi } from 'vitest'
import axios from 'axios'
import Menu from '@/pages/Menu.vue'
import { createTestingPinia } from '@pinia/testing'

// Stub components
const stubs = {
  'AuthenticatedLayout': {
    template: '<div><slot/></div>'
  },
  'v-progress-circular': {
    template: '<div data-test="loading-indicator"></div>'
  },
  'v-container': {
    template: '<div><slot/></div>'
  },
  'ProductList': {
    template: '<div/>'
  }
}

describe('Menu.vue', () => {
  it('fetches products based on slug prop', async () => {
    const mockResponse = {
      data: {
        name: "Tia Mary's Restaurant",
        products: [
          { id: 1, name: 'Feijoada', price: 25.90 }
        ]
      }
    }
    
    // Mock axios directly since component uses import
    const axiosGet = vi.spyOn(axios, 'get').mockResolvedValue(mockResponse)
    
    const wrapper = mount(Menu, {
      global: {
        plugins: [createTestingPinia()],
        stubs: stubs
      },
      props: {
        slug: 'tia-mary-corumba'
      }
    })

    // Wait for async operations to complete
    await flushPromises()
    
    expect(axiosGet).toHaveBeenCalledWith('/api/v1/restaurants/tia-mary-corumba')
    expect(wrapper.vm.restaurantName).toBe("Tia Mary's Restaurant")
    expect(wrapper.vm.products).toEqual(mockResponse.data.products)
  })

  it('shows loading state while fetching', () => {
    vi.spyOn(axios, 'get').mockResolvedValue({ data: {} })
    
    const wrapper = mount(Menu, {
      global: {
        plugins: [createTestingPinia()],
        stubs: stubs
      },
      props: {
        slug: 'test-slug'
      }
    })

    expect(wrapper.find('[data-test="loading-indicator"]').exists()).toBe(true)
  })
})
