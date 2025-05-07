<template>
  <v-select
    v-model="paymentMethod"
    :items="paymentOptions"
    label="Payment Method"
    :error-messages="errors"
    required
  />
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: String,
  errors: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update:modelValue'])

const paymentMethod = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const paymentOptions = [
  { title: 'Credit Card', value: 'credit_card' },
  { title: 'Cash on Delivery', value: 'cash' }
]
</script>
