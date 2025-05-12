<template>
  <v-textarea
    v-model="address"
    label="Delivery Address"
    :error-messages="errors"
    rows="3"
    auto-grow
    required
    data-testid="address-input"
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

const address = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})
</script>
