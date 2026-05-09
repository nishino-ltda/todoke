import { setActivePinia, createPinia } from 'pinia'
import { useCartStore } from '../cart'
import { vi, describe, it, expect, beforeEach, afterEach } from 'vitest'

describe('Cart Store', () => {
  let localStorageMock = {
    store: {},
    getItem(key) {
      return this.store[key] || null
    },
    setItem(key, value) {
      this.store[key] = value
    },
    clear() {
      this.store = {}
    }
  }

  beforeEach(() => {
    // Replace global localStorage with mock
    vi.stubGlobal('localStorage', localStorageMock)
    localStorageMock.clear()
    
    // Mock fetch
    vi.stubGlobal('fetch', vi.fn().mockResolvedValue({
      ok: true,
      json: () => Promise.resolve({ success: true })
    }))

    // Mock document.querySelector for CSRF token
    document.head.innerHTML = '<meta name="csrf-token" content="test-token">'
    
    setActivePinia(createPinia())
  })

  afterEach(() => {
    vi.unstubAllGlobals()
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

  it('increments quantity when adding identical items', () => {
    const cart = useCartStore()
    const item = { ...product1, selectedAddons: [] }
    cart.addItem(item)
    cart.addItem(item)
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

  it('loads initial state from localStorage', () => {
    const savedCart = [{ id: 1, name: 'Saved Pizza', price: 12, quantity: 2 }]
    localStorage.setItem('cart', JSON.stringify(savedCart))
    
    const cart = useCartStore()
    expect(cart.items).toEqual(savedCart)
  })

  it('saves state to localStorage on changes', async () => {
    const cart = useCartStore()
    await cart.addItem(product1)
    
    // Wait for next tick to ensure watchEffect completes
    await new Promise(resolve => setTimeout(resolve, 0))
    
    const saved = JSON.parse(localStorage.store.cart)
    expect(saved).toEqual([{ 
      ...product1, 
      quantity: 1,
      selectedAddons: []
    }])
  })

  it('handles localStorage errors gracefully', () => {
    vi.spyOn(Storage.prototype, 'setItem').mockImplementation(() => {
      throw new Error('Storage failed')
    })
    
    const cart = useCartStore()
    expect(() => cart.addItem(product1)).not.toThrow()
  })

  it('treats items with different addons as separate', () => {
    const cart = useCartStore()
    const productWithAddons = { ...product1, selectedAddons: [1, 2] }
    const productWithOtherAddons = { ...product1, selectedAddons: [3] }
    
    cart.addItem(productWithAddons)
    cart.addItem(productWithOtherAddons)
    
    expect(cart.items).toHaveLength(2)
    expect(cart.items[0].selectedAddons).toEqual([1, 2])
    expect(cart.items[1].selectedAddons).toEqual([3])
  })

  it('increments quantity only for items with identical addons', () => {
    const cart = useCartStore()
    const productWithAddons = { ...product1, selectedAddons: [1, 2] }
    const productSameAddons = { ...product1, selectedAddons: [1, 2] }
    const productDifferentAddons = { ...product1, selectedAddons: [1] }
    
    cart.addItem(productWithAddons)
    cart.addItem(productSameAddons)
    cart.addItem(productDifferentAddons)
    
    expect(cart.items).toHaveLength(2)
    expect(cart.items[0].quantity).toBe(2)
    expect(cart.items[1].quantity).toBe(1)
  })

  it('handles items without addons', () => {
    const cart = useCartStore()
    cart.addItem(product1)
    expect(cart.items[0].selectedAddons).toEqual([])
  })

  it('submits order successfully', async () => {
    const cart = useCartStore()
    cart.addItem(product1)
    
    const orderData = {
      address: '123 Main St',
      items: cart.items
    }
    
    const result = await cart.submitOrder(orderData)
    expect(result).toEqual({ success: true, data: { success: true } })
  })
})
