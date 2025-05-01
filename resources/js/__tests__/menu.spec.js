import { mount, flushPromises } from '@vue/test-utils'
import ProductCard from '@/components/ProductCard.vue'
import { vi } from 'vitest'
import axios from 'axios'
import Menu from '@/pages/Menu.vue'
import { createTestingPinia } from '@pinia/testing'
import { useCartStore } from '@/stores/cart'

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
    template: '<div data-test="product-list"></div>',
    props: ['products'],
    emits: ['product-clicked']
  },
  'ProductDetailsModal': {
    template: '<div data-test="product-modal"><button @click="$emit(\'add-to-cart\')">Add</button></div>',
    props: ['product'],
    emits: ['close', 'add-to-cart']
  }
}

describe('Menu.vue', () => {
  let pinia
  let cartStore
  
  beforeEach(() => {
    pinia = createTestingPinia()
    cartStore = useCartStore()
  })

  it('fetches products based on slug prop', async () => {
    const mockResponse = {
      data: {
        name: "Tia Mary's Restaurant",
        products: [
          { id: 1, name: 'Feijoada', price: 25.90 }
        ]
      }
    }
    
    const axiosGet = vi.spyOn(axios, 'get').mockResolvedValue(mockResponse)
    
    const wrapper = mount(Menu, {
      global: {
        plugins: [pinia],
        stubs: stubs
      },
      props: {
        slug: 'tia-mary-corumba'
      }
    })

    await flushPromises()
    
    expect(axiosGet).toHaveBeenCalledWith('/api/v1/restaurants/tia-mary-corumba')
    expect(wrapper.vm.restaurantName).toBe("Tia Mary's Restaurant")
    expect(wrapper.vm.products).toEqual(mockResponse.data.products)
  })

  it('shows loading state while fetching', () => {
    vi.spyOn(axios, 'get').mockResolvedValue({ data: {} })
    
    const wrapper = mount(Menu, {
      global: {
        plugins: [pinia],
        stubs: stubs
      },
      props: {
        slug: 'test-slug'
      }
    })

    expect(wrapper.find('[data-test="loading-indicator"]').exists()).toBe(true)
  })

  it('opens modal when product is clicked', async () => {
    vi.spyOn(axios, 'get').mockResolvedValue({
      data: {
        products: [{ id: 1, name: 'Test', price: 10 }]
      }
    })
    
    const wrapper = mount(Menu, {
      global: {
        plugins: [pinia],
        stubs: stubs
      },
      props: { slug: 'test' }
    })

    await flushPromises()
    
    // Set selectedProduct directly to simulate a product being selected
    wrapper.vm.selectedProduct = { id: 1, name: 'Test', price: 10 }
    await wrapper.vm.$nextTick()
    
    expect(wrapper.find('[data-test="product-modal"]').exists()).toBe(true)
  })

  it('updates cart when items are added via modal', async () => {
    vi.spyOn(axios, 'get').mockResolvedValue({
      data: {
        products: [{ id: 1, name: 'Test', price: 10 }]
      }
    })
    
    const wrapper = mount(Menu, {
      global: {
        plugins: [pinia],
        stubs: stubs
      },
      props: { slug: 'test' }
    })

    await flushPromises()
    
    // Set selectedProduct directly to simulate a product being selected
    wrapper.vm.selectedProduct = { id: 1, name: 'Test', price: 10 }
    await wrapper.vm.$nextTick()
    
    // Simulate modal adding to cart
    wrapper.vm.addToCart({ id: 1, name: 'Test', price: 10 })
    
    expect(cartStore.addItem).toHaveBeenCalled()
  })
})
