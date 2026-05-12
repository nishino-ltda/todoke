<template>
  <div class="payment-method-container">
    <v-select
      v-model="method"
      :items="paymentOptions"
      :label="t('components.payment.label')"
      :error-messages="errorMessages"
      required
      data-cy="payment-method-select"
    />

    <v-expand-transition>
      <div v-if="method === 'cash'" class="mt-2">
        <v-text-field
          v-model="changeFor"
          :label="t('components.payment.change_for')"
          type="number"
          min="0"
          step="any"
          prefix="R$"
          hide-details="auto"
          data-cy="change-for-input"
        />
      </div>
    </v-expand-transition>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  modelValue: [String, Object],
  errors: {
    type: Array,
    default: () => []
  }
})

const { t } = useI18n()
const emit = defineEmits(['update:modelValue'])

const errorMessages = computed(() => {
  return Array.isArray(props.errors) ? props.errors : []
})

const method = ref('cash')
const changeFor = ref('')

const paymentOptions = [
  { title: t('components.payment.cash'), value: 'cash' },
  { title: t('components.payment.card_machine'), value: 'card_machine' }
]

const emitValue = () => {
  if (method.value === 'cash' && changeFor.value) {
    emit('update:modelValue', { method: 'cash', change_for: parseFloat(changeFor.value) || 0 })
  } else {
    emit('update:modelValue', method.value)
  }
}

watch([method, changeFor], emitValue)

watch(() => props.modelValue, (val) => {
  if (!val) return
  if (typeof val === 'string') {
    method.value = val
  } else if (typeof val === 'object' && val.method) {
    method.value = val.method
    if (val.change_for) {
      changeFor.value = String(val.change_for)
    }
  }
}, { immediate: true })
</script>
