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

    <div v-if="errorMessage" class="error-message text-error mt-4">
      {{ errorMessage }}
    </div>

    <v-btn 
      type="submit" 
      color="primary"
      :loading="isSubmitting"
      class="mt-4"
    >
      Place Order
    </v-btn>
  </v-form>
</template>

<script setup>
import { ref, computed } from 'vue'
import AddressInput from './AddressInput.vue'
import PaymentMethodInput from './PaymentMethodInput.vue'
import { useCartStore } from '@/stores/cart'
import { useOrderApi } from '@/services/order'

const address = ref('')
const paymentMethod = ref('')
const isSubmitting = ref(false)
const errors = ref({})
const errorMessage = ref('')

const handleSubmit = async () => {
  console.log('Form submitted')
  isSubmitting.value = true
  errors.value = {}
  errorMessage.value = ''

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
      
      // Always call clearCart method
      cartStore.clearCart()
      console.log('Cart cleared')
  } catch (error) {
    console.error('Submission error:', error)
    // Set general error message
    errorMessage.value = 'Error submitting order'
    
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
