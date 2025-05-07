export interface OrderData {
  items: Array<{
    id: number
    name: string
    price: number
    quantity: number
    selectedAddons?: Array<{id: number, name: string, price: number}>
  }>
  address: string
  paymentMethod: string
}

export interface OrderApi {
  createOrder: (orderData: OrderData) => Promise<unknown>
}

export declare const useOrderApi: () => OrderApi
