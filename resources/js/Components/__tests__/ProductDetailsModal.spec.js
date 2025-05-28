import { mount } from '@vue/test-utils'
import ProductDetailsModal from '../ProductDetailsModal.vue'
import { useCartStore } from '../../stores/cart'
import { createPinia, setActivePinia } from 'pinia'
import { vi } from 'vitest'
import { nextTick } from 'vue'

describe('ProductDetailsModal', () => {
  let cartStore

  beforeEach(() => {
    setActivePinia(createPinia())
    cartStore = useCartStore()
    cartStore.addItem = vi.fn()
  })

  const product = {
    id: 1,
    name: 'Test Product',
    price: 10.99,
    description: 'Test description',
    image: '/test-image.jpg',
    addons: [
      { id: 1, name: 'Extra Cheese', price: 1.50 },
      { id: 2, name: 'Spicy Sauce', price: 0.75 }
    ]
  }

  it('renders product details correctly', () => {
    const wrapper = mount(ProductDetailsModal, {
      props: { product }
    })

    expect(wrapper.find('h2').text()).toBe(product.name)
    expect(wrapper.find('.price').text()).toBe(`\$${product.price.toFixed(2)}`)
    expect(wrapper.find('.description').text()).toBe(product.description)
    expect(wrapper.find('img').attributes('src')).toBe(product.image)
  })

  it('uses placeholder image when none provided', () => {
    const noImageProduct = { ...product, image: undefined }
    const wrapper = mount(ProductDetailsModal, {
      props: { product: noImageProduct }
    })

    expect(wrapper.find('img').attributes('src')).toBe('/images/placeholder-food.jpg')
  })

  it('emits close event when close button clicked', async () => {
    const wrapper = mount(ProductDetailsModal, {
      props: { product }
    })

    await wrapper.find('.close-button').trigger('click')
    expect(wrapper.emitted()).toHaveProperty('close')
  })

  it('adds correct quantity to cart', async () => {
    const wrapper = mount(ProductDetailsModal, {
      props: { product }
    })

    // Increase quantity to 3
    await wrapper.findAll('.quantity-controls button')[1].trigger('click')
    await wrapper.findAll('.quantity-controls button')[1].trigger('click')

    await wrapper.find('.add-to-cart').trigger('click')
    expect(cartStore.addItem).toHaveBeenCalledTimes(3)
    expect(wrapper.emitted()).toHaveProperty('close')
  })

  it('does not allow quantity below 1', async () => {
    const wrapper = mount(ProductDetailsModal, {
      props: { product }
    })

    const decreaseBtn = wrapper.findAll('.quantity-controls button')[0]
    await decreaseBtn.trigger('click')
    expect(wrapper.vm.quantity).toBe(1)
  })

  it('shows addons section when product has addons', () => {
    const wrapper = mount(ProductDetailsModal, {
      props: { product }
    })

    expect(wrapper.find('.addons-section').exists()).toBe(true)
    expect(wrapper.findAll('.addon-item')).toHaveLength(2)
  })

  it('does not show addons section when product has no addons', () => {
    const noAddonsProduct = { ...product, addons: [] }
    const wrapper = mount(ProductDetailsModal, {
      props: { product: noAddonsProduct }
    })

    expect(wrapper.find('.addons-section').exists()).toBe(false)
  })

  it('calculates total price with selected addons', async () => {
    const wrapper = mount(ProductDetailsModal, {
      props: { product }
    })

    // Select first addon
    await wrapper.findAll('.addon-item input')[0].setValue(true)
    await nextTick()
    
    // Base price (10.99) + addon (1.50) = 12.49
    expect(wrapper.vm.totalPrice).toBe(12.49)
    expect(wrapper.find('.add-to-cart').text()).toContain('$12.49')

    // Increase quantity to 2
    await wrapper.findAll('.quantity-controls button')[1].trigger('click')
    await nextTick()
    
    // (12.49) * 2 = 24.98
    expect(wrapper.vm.totalPrice).toBe(24.98)
    expect(wrapper.find('.add-to-cart').text()).toContain('$24.98')
  })

  it('includes selected addons in cart item', async () => {
    const wrapper = mount(ProductDetailsModal, {
      props: { product }
    })

    // Select both addons
    await wrapper.findAll('.addon-item input')[0].setValue(true)
    await wrapper.findAll('.addon-item input')[1].setValue(true)
    await wrapper.find('.add-to-cart').trigger('click')

    expect(cartStore.addItem).toHaveBeenCalledWith({
      ...product,
      selectedAddons: [1, 2]
    })
  })
})
