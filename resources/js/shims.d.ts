declare module '@/stores/cart' {
  const useCartStore: () => {
    items: Array<{
      id: number
      name: string
      price: number
      quantity: number
      selectedAddons?: Array<{id: number, name: string, price: number}>
    }>
    clearCart: () => void
    submitOrder: (orderData: unknown) => Promise<{success: boolean}>
  }
  export default useCartStore
}

declare module '@/services/order' {
  const useOrderApi: () => {
    createOrder: (orderData: {
      items: Array<{
        id: number
        name: string
        price: number
        quantity: number
        selectedAddons?: Array<{id: number, name: string, price: number}>
      }>
      address: string
      paymentMethod: string
    }) => Promise<unknown>
  }
  export default useOrderApi
}
