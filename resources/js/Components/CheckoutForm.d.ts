declare module '*.vue' {
  import { DefineComponent } from 'vue'

  interface CheckoutFormData {
    address: string
    paymentMethod: string
    errors: Record<string, string[]>
    isSubmitting: boolean
  }

  interface CheckoutFormMethods {
    handleSubmit: () => Promise<void>
    [key: string]: any
  }

  type CheckoutFormType = DefineComponent<
    {},
    CheckoutFormData,
    {},
    {},
    CheckoutFormMethods
  >

  const CheckoutForm: CheckoutFormType
  export default CheckoutForm
  export type { CheckoutFormType }
}
