import type { StoreDefinition } from 'pinia'

export interface CartItem {
  id: number
  name: string
  price: number
  quantity: number
  selectedAddons?: Array<{id: number, name: string, price: number}>
}

export interface CartStore {
  items: CartItem[]
  count: number
  total: number
  addItem: (product: CartItem) => void
  removeItem: (productId: number) => void
  clearCart: () => void
  submitOrder: (orderData: unknown) => Promise<{success: boolean}>
}

export declare const useCartStore: () => CartStore
