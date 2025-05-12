<template>
  <v-form @submit.prevent="handleSubmit" ref="form">
    <h2>Checkout</h2>
    
    <address-input 
      v-model="address"
      :errors="errors.address"
      :rules="[required]"
      data-test="address"
    />

    <payment-method-input 
      v-model="paymentMethod"
      :errors="errors.paymentMethod" 
      :rules="[required]"
      data-test="payment-method-input"
    />

    <div v-if="errorMessage" class="error-message text-error mt-4 mb-4" data-test="checkout-form">
      {{ errorMessage }}
      <ul v-if="errors.address || errors.paymentMethod">
        <li v-if="errors.address">Address is required</li>
        <li v-if="errors.paymentMethod">Payment method is required</li>
      </ul>
    </div>

    <v-btn 
      type="submit" 
      color="primary"
      :loading="isSubmitting"
      :disabled="isSubmitting"
      class="mt-4"
      data-test="submit-order"
    >
      Place Order
    </v-btn>

    <div v-if="showConfirmation" class="mt-4">
      <h3>Order Confirmed</h3>
      <p>Your order has been placed successfully!</p>
    </div>
  </v-form>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import AddressInput from './AddressInput.vue'
import PaymentMethodInput from './PaymentMethodInput.vue'
import { useCartStore } from '@/stores/cart'
import { useOrderApi } from '@/services/order'

const address = ref('')
const paymentMethod = ref('')
const isSubmitting = ref(false)
const errors = ref({})
const errorMessage = ref('')
const showConfirmation = ref(false)
const router = useRouter()

const required = (value) => !!value || 'This field is required'

const cartStore = useCartStore()
const orderApi = useOrderApi()

const handleSubmit = async () => {
  isSubmitting.value = true
  errors.value = {}
  errorMessage.value = ''
  
    const orderData = {
      items: cartStore.items.map(item => ({
        id: item.id,
        name: item.name,
        price: item.price,
        quantity: item.quantity,
        selectedAddons: item.selectedAddons || []
      })),
      address: address.value,
      paymentMethod: paymentMethod.value
    }

  try {
    const result = await orderApi.createOrder(orderData)
    await router.push({ name: 'home' })
    cartStore.clearCart()
    showConfirmation.value = true
  } catch (error) {
    errorMessage.value = 'Error submitting order'
    errors.value = {
      address: error.response?.data?.errors?.address || [],
      paymentMethod: error.response?.data?.errors?.paymentMethod || []
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>
