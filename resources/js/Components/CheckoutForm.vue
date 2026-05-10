<template>
  <v-form @submit.prevent="handleSubmit" ref="formRef">
    <h2 class="text-h5 mb-4">{{ $t('checkout.form_title') }}</h2>

    <address-input
      v-model="address"
      :errors="errors"
      :rules="[requiredField]"
      :geocode="true"
      data-cy="address-input"
    />

    <payment-method-input
      v-model="paymentMethod"
      :errors="errors"
      :rules="[requiredField]"
      data-cy="payment-method-input"
    />

    <div v-if="props.errorMessage" class="text-error mt-4 mb-4" data-cy="checkout-form-error">
      {{ props.errorMessage }}
    </div>

    <v-btn
      type="submit"
      color="primary"
      size="large"
      :loading="props.isSubmitting"
      :disabled="props.isSubmitting"
      class="mt-4"
      data-cy="submit-order"
    >
      {{ $t('checkout.place_order') }}
    </v-btn>
  </v-form>
</template>

<script setup>
import { ref } from 'vue'
import AddressInput from './AddressInput.vue'
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
})

const emit = defineEmits(['submit'])

const address = ref('')
const paymentMethod = ref('')
const errors = ref({})
const formRef = ref(null)

const requiredField = (value) => !!value || 'This field is required'

const handleSubmit = async () => {
  const valid = await formRef.value?.validate()
  if (!valid?.valid) {
    return
  }

  emit('submit', {
    address: address.value,
    paymentMethod: paymentMethod.value,
  })
}
</script>
