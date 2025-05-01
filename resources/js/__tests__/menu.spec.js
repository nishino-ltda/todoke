import { mount } from '@vue/test-utils'
import Menu from '@/pages/Menu.vue'
import { createTestingPinia } from '@pinia/testing'

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
    
    const wrapper = mount(Menu, {
      global: {
        plugins: [createTestingPinia()],
        mocks: {
          $axios: {
            get: jest.fn().mockResolvedValue(mockResponse)
          }
        }
      },
      props: {
        slug: 'tia-mary-corumba'
      }
    })

    await wrapper.vm.$nextTick()
    expect(wrapper.vm.$axios.get).toHaveBeenCalledWith('/api/restaurants/tia-mary-corumba')
    expect(wrapper.vm.restaurantName).toBe("Tia Mary's Restaurant")
    expect(wrapper.vm.products).toEqual(mockResponse.data.products)
  })

  it('shows loading state while fetching', () => {
    const wrapper = mount(Menu, {
      global: {
        plugins: [createTestingPinia()]
      },
      props: {
        slug: 'test-slug'
      }
    })

    expect(wrapper.findComponent({ name: 'v-progress-circular' }).exists()).toBe(true)
  })
})
