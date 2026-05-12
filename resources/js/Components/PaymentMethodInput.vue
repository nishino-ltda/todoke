<template>
  <div class="payment-method-container">
    <v-select
      v-model="method"
      :items="paymentOptions"
      :label="t('components.payment.label')"
      :error-messages="errorMessages"
      variant="outlined"
      rounded="lg"
      required
      data-cy="payment-method-select"
    />

    <v-expand-transition>
      <div v-if="method === 'cash'" class="mt-4 payment-options-fade-in">
        <p class="text-subtitle-2 mb-2 text-medium-emphasis">{{ t('components.payment.change_for') }}:</p>
        
        <v-btn-toggle
          v-model="changeSelection"
          mandatory
          color="primary"
          variant="tonal"
          class="change-options-group mb-4"
          @update:model-value="handleSelectionChange"
          data-cy="change-selection-toggle"
        >
          <v-btn value="exact" size="small" class="text-none" data-cy="change-exact">
            {{ t('components.payment.exact_amount') }}
          </v-btn>
          
          <v-btn 
            v-for="val in suggestions" 
            :key="val" 
            :value="val" 
            size="small"
            data-cy="change-suggestion"
          >
            R$ {{ val }}
          </v-btn>
          
          <v-btn value="other" size="small" class="text-none" data-cy="change-other">
            {{ t('components.payment.other') }}
          </v-btn>
        </v-btn-toggle>

        <v-expand-transition>
          <div v-if="changeSelection === 'other'" class="mt-2">
            <v-text-field
              v-model="changeFor"
              :label="t('components.payment.change_for')"
              type="number"
              min="0"
              step="any"
              prefix="R$"
              variant="outlined"
              rounded="lg"
              hide-details="auto"
              data-cy="change-for-input"
            />
          </div>
        </v-expand-transition>
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
  },
  total: {
    type: Number,
    default: 0
  }
})

const { t } = useI18n()
const emit = defineEmits(['update:modelValue'])

const errorMessages = computed(() => {
  return Array.isArray(props.errors) ? props.errors : []
})

const method = ref('cash')
const changeFor = ref('')
const changeSelection = ref('exact')

const denominations = [1, 2, 5, 10, 20, 50, 100, 200]

const suggestions = computed(() => {
  const total = parseFloat(props.total) || 0
  if (total <= 0) return [50, 100, 200]
  
  const potentialSuggestions = new Set()
  
  // Next multiple of 10
  const nextTen = Math.ceil(total / 10) * 10
  if (nextTen > total) potentialSuggestions.add(nextTen)
  
  // Next multiple of 50
  const nextFifty = Math.ceil(total / 50) * 50
  if (nextFifty > total) potentialSuggestions.add(nextFifty)
  
  // Standard denominations
  denominations.forEach(d => {
    if (d > total) potentialSuggestions.add(d)
  })
  
  // Sort and take the first 4 relevant ones
  return Array.from(potentialSuggestions)
    .sort((a, b) => a - b)
    .slice(0, 4)
})

const paymentOptions = [
  { title: t('components.payment.cash'), value: 'cash' },
  { title: t('components.payment.card_machine'), value: 'card_machine' }
]

const handleSelectionChange = (val) => {
  if (val === 'exact') {
    changeFor.value = String(props.total)
  } else if (typeof val === 'number') {
    changeFor.value = String(val)
  }
  // If 'other', we keep the current changeFor or let the user type
}

const emitValue = () => {
  if (method.value === 'cash') {
    const val = parseFloat(changeFor.value) || 0
    emit('update:modelValue', { method: 'cash', change_for: val })
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
      
      // Update selection based on value
      if (Math.abs(val.change_for - props.total) < 0.01) {
        changeSelection.value = 'exact'
      } else if (denominations.includes(val.change_for)) {
        changeSelection.value = val.change_for
      } else {
        changeSelection.value = 'other'
      }
    }
  }
}, { immediate: true })

// Initial state for changeFor if exact is selected
watch(() => props.total, (newTotal) => {
  if (changeSelection.value === 'exact') {
    changeFor.value = String(newTotal)
  }
}, { immediate: true })
</script>

<style scoped>
.change-options-group {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  height: auto !important;
}

:deep(.v-btn-toggle .v-btn) {
  border: 1px solid rgba(var(--v-border-color), 0.1);
  border-radius: 8px !important;
}

.payment-options-fade-in {
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
