import { mount } from '@vue/test-utils'
import ProductDetailsModal from '../ProductDetailsModal.vue'
import { useCartStore } from '../../stores/cart'
import { createPinia, setActivePinia } from 'pinia'
import { vi, describe, it, expect, beforeEach } from 'vitest'
import { nextTick } from 'vue'

// Mock Vuetify components
const vuetifyComponents = {
  VDialog: {
    template: '<div class="v-dialog" v-if="modelValue"><slot /></div>',
    props: ['modelValue']
  },
  VCard: {
    template: '<div class="v-card"><slot /></div>'
  },
  VCardTitle: {
    template: '<div class="v-card-title"><slot /></div>'
  },
  VCardSubtitle: {
    template: '<div class="v-card-subtitle"><slot /></div>'
  },
  VCardText: {
    template: '<div class="v-card-text"><slot /></div>'
  },
  VCardActions: {
    template: '<div class="v-card-actions"><slot /></div>'
  },
  VBtn: {
    template: '<button class="v-btn"><slot /></button>',
    props: ['icon', 'block']
  },
  VIcon: {
    template: '<span class="v-icon"><slot /></span>'
  },
  VImg: {
    template: '<img class="v-img" :src="src" :alt="alt" />',
    props: ['src', 'alt']
  }
}

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
      props: { product },
      global: {
        stubs: vuetifyComponents
      }
    })

    expect(wrapper.find('.v-card-title').text()).toBe(product.name)
    expect(wrapper.find('.v-card-subtitle').text()).toBe(`\$${product.price.toFixed(2)}`)
    expect(wrapper.find('.v-card-text.text-body-1').text()).toBe(product.description)
    expect(wrapper.find('.v-img').attributes('src')).toBe(product.image)
  })

  it('uses placeholder image when none provided', () => {
    const noImageProduct = { ...product, image: undefined }
    const wrapper = mount(ProductDetailsModal, {
      props: { product: noImageProduct },
      global: {
        stubs: vuetifyComponents
      }
    })

    expect(wrapper.find('.v-img').attributes('src')).toBe('/images/placeholder-food.jpg')
  })

  it('emits close event when close button clicked', async () => {
    const wrapper = mount(ProductDetailsModal, {
      props: { product },
      global: {
        stubs: vuetifyComponents
      }
    })

    await wrapper.find('.close-button').trigger('click')
    expect(wrapper.emitted()).toHaveProperty('close')
  })

  it('adds correct quantity to cart', async () => {
    const wrapper = mount(ProductDetailsModal, {
      props: { product },
      global: {
        stubs: vuetifyComponents
      }
    })

    // Increase quantity to 3
    const buttons = wrapper.findAll('.quantity-controls .v-btn')
    await buttons[1].trigger('click') // Increase
    await buttons[1].trigger('click') // Increase

    await wrapper.find('[data-test="add-to-cart"]').trigger('click')
    expect(cartStore.addItem).toHaveBeenCalledTimes(3)
    expect(wrapper.emitted()).toHaveProperty('close')
  })

  it('does not allow quantity below 1', async () => {
    const wrapper = mount(ProductDetailsModal, {
      props: { product },
      global: {
        stubs: vuetifyComponents
      }
    })

    const decreaseBtn = wrapper.findAll('.quantity-controls .v-btn')[0]
    await decreaseBtn.trigger('click')
    expect(wrapper.vm.quantity).toBe(1)
  })

  it('shows addons section when product has addons', () => {
    const wrapper = mount(ProductDetailsModal, {
      props: { product },
      global: {
        stubs: vuetifyComponents
      }
    })

    expect(wrapper.find('.addons-section').exists()).toBe(true)
    expect(wrapper.findAll('.addon-item')).toHaveLength(2)
  })

  it('does not show addons section when product has no addons', () => {
    const noAddonsProduct = { ...product, addons: [] }
    const wrapper = mount(ProductDetailsModal, {
      props: { product: noAddonsProduct },
      global: {
        stubs: vuetifyComponents
      }
    })

    expect(wrapper.find('.addons-section').exists()).toBe(false)
  })

  it('calculates total price with selected addons', async () => {
    const wrapper = mount(ProductDetailsModal, {
      props: { product },
      global: {
        stubs: vuetifyComponents
      }
    })

    // Select first addon
    await wrapper.findAll('.addon-item input')[0].setValue(true)
    await nextTick()
    
    // Base price (10.99) + addon (1.50) = 12.49
    expect(wrapper.vm.totalPrice).toBe(12.49)
    expect(wrapper.find('[data-test="add-to-cart"]').text()).toContain('$12.49')

    // Increase quantity to 2
    await wrapper.findAll('.quantity-controls .v-btn')[1].trigger('click')
    await nextTick()
    
    // (12.49) * 2 = 24.98
    expect(wrapper.vm.totalPrice).toBe(24.98)
    expect(wrapper.find('[data-test="add-to-cart"]').text()).toContain('$24.98')
  })
})

