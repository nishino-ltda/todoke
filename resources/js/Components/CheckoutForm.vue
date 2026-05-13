<template>
  <v-form @submit.prevent="handleSubmit" ref="formRef">
    <h2 class="text-h5 mb-4">{{ $t('checkout.form_title') }}</h2>

    <address-selector
      v-model="address"
      :errors="errors"
      class="mb-6"
      data-cy="address-selector"
    />

    <payment-method-input
      v-model="paymentMethod"
      :errors="errors"
      class="mb-6"
      :rules="[requiredField]"
      :total="props.total"
      data-cy="payment-method-input"
    />

    <div v-if="props.errorMessage" class="text-error mt-4 mb-4" data-cy="checkout-form-error">
      {{ props.errorMessage }}
    </div>

    <v-btn
      type="submit"
      color="primary"
      size="x-large"
      :loading="props.isSubmitting"
      :disabled="props.isSubmitting"
      class="mt-4 font-weight-bold text-none"
      rounded="xl"
      block
      data-cy="submit-order"
    >
      {{ $t('checkout.place_order') }}
    </v-btn>
  </v-form>
</template>

<script setup>
import { ref } from 'vue'
import api from '@/services/api'
import AddressSelector from './AddressSelector.vue'
import PaymentMethodInput from './PaymentMethodInput.vue'

const props = defineProps({
  isSubmitting: {
    type: Boolean,
    default: false,
  },
  errorMessage: {
    type: String,
    default: '',
  },
  total: {
    type: Number,
    default: 0,
  },
})

const emit = defineEmits(['submit'])

const address = ref('')
const paymentMethod = ref('')
const errors = ref([])
const formRef = ref(null)

const requiredField = (value) => !!value || 'This field is required'

const handleSubmit = async () => {
  const valid = await formRef.value?.validate()
  if (!valid?.valid) {
    return
  }

  // Save address if marked as new
  if (address.value?.is_new && address.value?.save_as) {
    try {
      await api.post('/customer/addresses', {
        label: address.value.save_as,
        address: address.value.address,
        lat: address.value.lat,
        lng: address.value.lng,
        city: address.value.city,
        state: address.value.state,
        neighborhood: address.value.neighborhood
      })
    } catch (err) {
      console.warn('Failed to save address, proceeding with order anyway', err)
    }
  }

  emit('submit', {
    address: address.value,
    paymentMethod: paymentMethod.value,
  })
}
</script>
