import { mount } from '@vue/test-utils'
import { vi } from 'vitest'
import ProductList from '../ProductList.vue'
import ProductCard from '../ProductCard.vue'

// Stub Vuetify and child components
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
  'ProductCard': {
    template: '<div data-test="product-card"></div>',
    methods: {
      $emit: vi.fn()
    }
  }
}

describe('ProductList', () => {
  const products = [
    { id: 1, name: 'Pizza', price: 10, description: 'Delicious' },
    { id: 2, name: 'Burger', price: 8, description: 'Tasty' }
  ]

  it('renders product cards for each product', () => {
    const wrapper = mount(ProductList, {
      props: { products },
      global: {
        stubs: vuetifyStubs
      }
    })

    expect(wrapper.findAll('[data-test="product-card"]')).toHaveLength(products.length)
  })

  it('emits add-to-cart event when child component emits', async () => {
    const wrapper = mount(ProductList, {
      props: { products },
      global: {
        stubs: vuetifyStubs
      }
    })

    const productCards = wrapper.findAllComponents(ProductCard)
    await productCards[0].vm.$emit('add-to-cart', products[0])
    expect(wrapper.emitted('add-to-cart')).toBeTruthy()
    expect(wrapper.emitted('add-to-cart')[0][0]).toEqual(products[0])
  })
})
