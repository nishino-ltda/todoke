import { mount } from '@vue/test-utils'
import ProductCard from '../ProductCard.vue'

describe('ProductCard', () => {
  const product = {
    id: 1,
    name: 'Margherita Pizza',
    price: 12.99,
    description: 'Classic pizza with tomato sauce and mozzarella',
    image: '/images/pizza.jpg'
  }

  it('displays product details correctly', () => {
    const wrapper = mount(ProductCard, {
      props: { product }
    })

    expect(wrapper.find('[data-test="product-name"]').text()).toBe(product.name)
    expect(wrapper.find('[data-test="product-price"]').text()).toBe(`$${product.price}`)
    expect(wrapper.find('[data-test="product-description"]').text()).toBe(product.description)
    expect(wrapper.find('[data-test="product-image"]').attributes('src')).toBe(product.image)
  })

  it('emits add-to-cart event when button clicked', async () => {
    const wrapper = mount(ProductCard, {
      props: { product }
    })

    await wrapper.find('[data-test="add-to-cart-btn"]').trigger('click')
    expect(wrapper.emitted('add-to-cart')).toBeTruthy()
    expect(wrapper.emitted('add-to-cart')[0]).toEqual([product])
  })
})
