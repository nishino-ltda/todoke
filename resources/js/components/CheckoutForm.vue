<template>
  <v-form @submit.prevent="handleSubmit">
    <h2>Checkout</h2>
    
    <address-input 
      v-model="address"
      :errors="errors.address"
    />

    <payment-method-input 
      v-model="paymentMethod"
      :errors="errors.paymentMethod"
    />

    <v-btn 
      type="submit" 
      color="primary"
      :loading="isSubmitting"
    >
      Place Order
    </v-btn>
  </v-form>
</template>

<script setup>
import { ref } from 'vue'
import AddressInput from './AddressInput.vue'
import PaymentMethodInput from './PaymentMethodInput.vue'
import { useCartStore } from '@/stores/cart'
import { useOrderApi } from '@/services/order'

const address = ref('')
const paymentMethod = ref('')
const isSubmitting = ref(false)
const errors = ref({})

const handleSubmit = async () => {
  console.log('Form submitted')
  isSubmitting.value = true
  errors.value = {}

    const cartStore = useCartStore()
    const orderApi = useOrderApi()
    console.log('Using orderApi:', orderApi)
    
    const orderData = {
      items: cartStore.items,
      address: address.value,
      paymentMethod: paymentMethod.value
    }
    console.log('Submitting order data:', orderData)

    try {
      const result = await orderApi.createOrder(orderData)
      console.log('Order API response:', result)
      
      cartStore.clearCart()
      console.log('Cart cleared')
    } catch (error) {
      console.error('Submission error:', error)
      // Ensure errors object has expected structure
      errors.value = {
        address: error.response?.data?.errors?.address || ['Invalid'],
        paymentMethod: error.response?.data?.errors?.paymentMethod || []
      }
      console.log('Set errors:', errors.value)
  } finally {
    isSubmitting.value = false
    console.log('Submission complete')
  }
}
</script>
