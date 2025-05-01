import { mount } from '@vue/test-utils'
import ProductList from '../ProductList.vue'

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
  'v-text-field': {
    template: '<input :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" data-test="product-search" />',
    props: ['modelValue'],
    emits: ['update:modelValue']
  },
  'ProductCard': {
    template: '<div @click="$emit(\'product-clicked\', product)" data-test="product-card">{{ product.name }}</div>',
    props: ['product'],
    emits: ['product-clicked']
  }
}

describe('ProductList', () => {
  const products = [
    { id: 1, name: 'Pizza', price: 10, description: 'Delicious' },
    { id: 2, name: 'Burger', price: 8, description: 'Tasty' },
    { id: 3, name: 'Pasta Carbonara', price: 12, description: 'Italian classic' }
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

  it('passes correct product props to each card', () => {
    const wrapper = mount(ProductList, {
      props: { products },
      global: {
        stubs: vuetifyStubs
      }
    })

    const html = wrapper.html()
    expect(html).toContain(products[0].name)
    expect(html).toContain(products[1].name)
    expect(html).toContain(products[2].name)
  })
  
  it('emits product-clicked event when a product card is clicked', async () => {
    const wrapper = mount(ProductList, {
      props: { products },
      global: {
        stubs: vuetifyStubs
      }
    })
    
    await wrapper.findAll('[data-test="product-card"]')[0].trigger('click')
    
    expect(wrapper.emitted('product-clicked')).toBeTruthy()
    expect(wrapper.emitted('product-clicked')[0][0]).toEqual(products[0])
  })
  
  it('filters products based on search query', async () => {
    const wrapper = mount(ProductList, {
      props: { products },
      global: {
        stubs: vuetifyStubs
      }
    })
    
    // Initial state - all products shown
    expect(wrapper.findAll('[data-test="product-card"]')).toHaveLength(3)
    
    // Simulate input event on the search field
    await wrapper.find('[data-test="product-search"]').setValue('pasta')
    await wrapper.find('[data-test="product-search"]').trigger('input')
    
    // Manually set the searchQuery ref
    wrapper.vm.searchQuery = 'pasta'
    await wrapper.vm.$nextTick()
    
    // Check filtered products
    const filteredProducts = wrapper.vm.filteredProducts
    expect(filteredProducts.length).toBe(1)
    expect(filteredProducts[0].name).toBe('Pasta Carbonara')
  })
  
  it('filters products by description', async () => {
    const wrapper = mount(ProductList, {
      props: { products },
      global: {
        stubs: vuetifyStubs
      }
    })
    
    // Manually set the searchQuery ref
    wrapper.vm.searchQuery = 'italian'
    await wrapper.vm.$nextTick()
    
    // Check filtered products
    const filteredProducts = wrapper.vm.filteredProducts
    expect(filteredProducts.length).toBe(1)
    expect(filteredProducts[0].name).toBe('Pasta Carbonara')
  })
  
  it('shows all products when search is cleared', async () => {
    const wrapper = mount(ProductList, {
      props: { products },
      global: {
        stubs: vuetifyStubs
      }
    })
    
    // Set search query
    wrapper.vm.searchQuery = 'pasta'
    await wrapper.vm.$nextTick()
    
    // Check filtered products
    let filteredProducts = wrapper.vm.filteredProducts
    expect(filteredProducts.length).toBe(1)
    
    // Clear search query
    wrapper.vm.searchQuery = ''
    await wrapper.vm.$nextTick()
    
    // Check all products are shown
    filteredProducts = wrapper.vm.filteredProducts
    expect(filteredProducts.length).toBe(3)
  })
})
