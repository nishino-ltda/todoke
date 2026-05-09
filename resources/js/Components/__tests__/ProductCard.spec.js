import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import ProductCard from '../ProductCard.vue'
import { nextTick } from 'vue'

const i18n = createI18n({
  legacy: false,
  locale: 'pt-BR',
  messages: {
    'pt-BR': {
      cart: {
        add_to_cart: 'Adicionar ao Carrinho'
      }
    },
    en: {
      cart: {
        add_to_cart: 'Add to Cart'
      }
    }
  }
})

// Stub Vuetify components
const vuetifyStubs = {
  'v-img': {
    template: '<img :src="$attrs.src" data-test="product-image"/>'
  },
  'v-card': {
    template: '<div @click="$emit(\'click\')"><slot/></div>'
  },
  'v-card-title': {
    template: '<div data-test="product-name"><slot/></div>'
  },
  'v-card-subtitle': {
    template: '<div data-test="product-price"><slot/></div>'
  },
  'v-card-text': {
    template: '<div data-test="product-description"><slot/></div>'
  },
  'v-card-actions': {
    template: '<div class="v-card-actions"><slot/></div>'
  },
  'v-btn': {
    template: '<button @click="$emit(\'click\')" data-test="add-to-cart-button"><slot/></button>'
  }
}

describe('ProductCard', () => {
  const product = {
    id: 1,
    name: 'Margherita Pizza',
    price: 12.99,
    description: 'Classic pizza with tomato sauce and mozzarella. Delicious!',
    image: '/images/pizza.jpg'
  }

  it('displays product details correctly', () => {
    const wrapper = mount(ProductCard, {
      props: { product },
      global: {
        plugins: [i18n],
        stubs: vuetifyStubs
      }
    })

    expect(wrapper.find('[data-test="product-name"]').text()).toBe(product.name)
    expect(wrapper.find('[data-test="product-price"]').text()).toBe(`$${product.price}`)
    expect(wrapper.find('[data-test="product-description"]').text().trim()).toBe(product.description.trim())
    expect(wrapper.find('[data-test="product-image"]').attributes('src')).toBe(product.image)
  })

  it('renders button text in correct language', async () => {
    i18n.global.locale.value = 'pt-BR'
    const wrapper = mount(ProductCard, {
      props: { product },
      global: {
        plugins: [i18n],
        stubs: vuetifyStubs
      }
    })

    expect(wrapper.find('[data-test="add-to-cart-button"]').text()).toBe('Adicionar ao Carrinho')

    i18n.global.locale.value = 'en'
    await nextTick()
    expect(wrapper.find('[data-test="add-to-cart-button"]').text()).toBe('Add to Cart')
  })

  it('emits product-clicked event when card is clicked', async () => {
    const wrapper = mount(ProductCard, {
      props: { product },
      global: {
        plugins: [i18n],
        stubs: vuetifyStubs
      }
    })

    await wrapper.find('[data-test="product-name"]').trigger('click')
    
    expect(wrapper.emitted('product-clicked')).toBeTruthy()
    expect(wrapper.emitted('product-clicked')[0][0]).toEqual(product)
  })

  it('truncates long descriptions', () => {
    const longDescProduct = {
      ...product,
      description: 'A'.repeat(200)
    }

    const wrapper = mount(ProductCard, {
      props: { product: longDescProduct },
      global: {
        plugins: [i18n],
        stubs: vuetifyStubs
      }
    })

    const displayedDesc = wrapper.find('[data-test="product-description"]').text()
    expect(displayedDesc.length).toBeLessThan(110)
    expect(displayedDesc).toContain('...')
  })
})
