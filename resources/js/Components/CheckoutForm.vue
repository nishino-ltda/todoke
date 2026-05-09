<template>
  <v-form @submit.prevent="handleSubmit" ref="form">
    <h2>{{ $t('checkout.form_title') }}</h2>
    
    <address-input 
      v-model="address"
      :errors="errors.address"
      :rules="[required]"
      data-cy="address"
    />

    <payment-method-input 
      v-model="paymentMethod"
      :errors="errors.paymentMethod" 
      :rules="[required]"
      data-cy="payment-method-input"
    />

    <div v-if="errorMessage" class="error-message text-error mt-4 mb-4" data-cy="checkout-form">
      {{ errorMessage }}
      <ul v-if="errors.address || errors.paymentMethod">
        <li v-if="errors.address">{{ $t('checkout.address_required') }}</li>
        <li v-if="errors.paymentMethod">{{ $t('checkout.payment_method_required') }}</li>
      </ul>
    </div>

    <v-btn 
      type="submit" 
      color="primary"
      :loading="isSubmitting"
      :disabled="isSubmitting"
      class="mt-4"
      data-cy="submit-order"
    >
      {{ $t('checkout.place_order') }}
    </v-btn>

    <div v-if="showConfirmation" class="mt-4">
      <h3>{{ $t('checkout.order_confirmed') }}</h3>
      <p>{{ $t('checkout.success_message') }}</p>
    </div>
  </v-form>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AddressInput from './AddressInput.vue'
import PaymentMethodInput from './PaymentMethodInput.vue'
import { useCartStore } from '@/stores/cart'
import { useOrderApi } from '@/services/order'

const { t } = useI18n()
const address = ref('')
const paymentMethod = ref('')
const isSubmitting = ref(false)
const errors = ref({})
const errorMessage = ref('')
const showConfirmation = ref(false)

const required = (value) => !!value || t('checkout.validation.required')

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
    await router.visit('/')
    cartStore.clearCart()
    showConfirmation.value = true
  } catch (error) {
    errorMessage.value = t('checkout.error_submitting')
    errors.value = {
      address: error.response?.data?.errors?.address || [],
      paymentMethod: error.response?.data?.errors?.paymentMethod || []
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>
