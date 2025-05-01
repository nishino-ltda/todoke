import { setActivePinia, createPinia } from 'pinia'
import { useCartStore } from '../cart'

describe('Cart Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  const product1 = { id: 1, name: 'Pizza', price: 10 }
  const product2 = { id: 2, name: 'Burger', price: 8 }

  it('adds items to cart', () => {
    const cart = useCartStore()
    cart.addItem(product1)
    expect(cart.items).toHaveLength(1)
    expect(cart.items[0].id).toBe(1)
    expect(cart.items[0].quantity).toBe(1)
  })

  it('increments quantity when adding existing item', () => {
    const cart = useCartStore()
    cart.addItem(product1)
    cart.addItem(product1)
    expect(cart.items).toHaveLength(1)
    expect(cart.items[0].quantity).toBe(2)
  })

  it('removes items from cart', () => {
    const cart = useCartStore()
    cart.addItem(product1)
    cart.addItem(product2)
    cart.removeItem(1)
    expect(cart.items).toHaveLength(1)
    expect(cart.items[0].id).toBe(2)
  })

  it('calculates total correctly', () => {
    const cart = useCartStore()
    cart.addItem(product1)
    cart.addItem(product1) // quantity 2
    cart.addItem(product2)
    expect(cart.total).toBe(10*2 + 8)
  })

  it('calculates count correctly', () => {
    const cart = useCartStore()
    cart.addItem(product1)
    cart.addItem(product2)
    expect(cart.count).toBe(2)
  })

  it('clears cart', () => {
    const cart = useCartStore()
    cart.addItem(product1)
    cart.clearCart()
    expect(cart.items).toHaveLength(0)
  })
})
