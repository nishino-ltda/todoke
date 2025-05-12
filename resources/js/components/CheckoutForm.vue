<template>
  <v-form @submit.prevent="handleSubmit" ref="form">
    <h2>Checkout</h2>
    
    <address-input 
      v-model="address"
      :errors="errors.address"
      :rules="[required]"
    />

    <payment-method-input 
      v-model="paymentMethod"
      :errors="errors.paymentMethod" 
      :rules="[required]"
    />

    <div v-if="errorMessage" class="error-message text-error mt-4">
      {{ errorMessage }}
    </div>

    <v-btn 
      type="submit" 
      color="primary"
      :loading="isSubmitting"
      :disabled="isSubmitting"
      class="mt-4"
    >
      Place Order
    </v-btn>

    <v-dialog v-model="showConfirmation" max-width="500">
      <v-card>
        <v-card-title>Order Confirmed</v-card-title>
        <v-card-text>
          Your order has been placed successfully!
        </v-card-text>
        <v-card-actions>
          <v-btn color="primary" @click="showConfirmation = false">OK</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
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

const handleSubmit = async () => {
  isSubmitting.value = true
  errors.value = {}
  errorMessage.value = ''

  const cartStore = useCartStore()
  const orderApi = useOrderApi()
  
    const orderData = {
    items: cartStore.items.map(item => {
      const itemData = {
        id: item.id,
        name: item.name,
        price: item.price,
        quantity: item.quantity
      }
      if (item.selectedAddons) {
        itemData.selectedAddons = item.selectedAddons
      }
      return itemData
    }),
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
