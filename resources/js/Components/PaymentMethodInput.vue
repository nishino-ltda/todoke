<template>
  <div class="payment-method-container">
    <v-select
      v-model="method"
      :items="paymentOptions"
      :label="t('components.payment.label')"
      :error-messages="errors"
      required
      data-cy="payment-method-input"
      data-cy="payment-method-select"
    />

    <v-expand-transition>
      <div v-if="method === 'credit_card'" class="credit-card-details mt-4">
        <v-text-field
          v-model="card.number"
          :label="t('components.payment.card_number')"
          maxlength="19"
          :rules="[rules.required]"
          data-cy="card-number-input"
          data-cy="card-number-input"
          @input="formatCardNumber"
        />
        
        <v-row>
          <v-col cols="6">
            <v-text-field
              v-model="card.expiry"
              :label="t('components.payment.expiry')"
              placeholder="MM/AA"
              maxlength="5"
              :rules="[rules.required]"
              data-cy="card-expiry-input"
              data-cy="card-expiry-input"
              @input="formatExpiry"
            />
          </v-col>
          <v-col cols="6">
            <v-text-field
              v-model="card.cvv"
              :label="t('components.payment.cvv')"
              type="password"
              maxlength="4"
              :rules="[rules.required]"
              data-cy="card-cvv-input"
              data-cy="card-cvv-input"
            />
          </v-col>
        </v-row>

        <v-text-field
          v-model="card.holder"
          :label="t('components.payment.holder')"
          :rules="[rules.required]"
          data-cy="card-holder-input"
          data-cy="card-holder-input"
        />
      </div>
    </v-expand-transition>
  </div>
</template>

<script setup>
import { ref, watch, reactive } from 'vue'
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

const method = ref('cash')
const card = reactive({
  number: '',
  expiry: '',
  cvv: '',
  holder: ''
})

const rules = {
  required: value => !!value || t('auth.validation.required', { field: '' })
}

const paymentOptions = [
  { title: t('components.payment.credit_card'), value: 'credit_card' },
  { title: t('components.payment.cash'), value: 'cash' }
]

const formatCardNumber = (e) => {
  let val = (typeof e === 'string' ? e : e.target.value).replace(/\D/g, '')
  let formatted = val.match(/.{1,4}/g)?.join(' ') || val
  card.number = formatted.substring(0, 19)
}

const formatExpiry = (e) => {
  let val = (typeof e === 'string' ? e : e.target.value).replace(/\D/g, '')
  if (val.length > 2) {
    val = val.substring(0, 2) + '/' + val.substring(2, 4)
  }
  card.expiry = val
}

const emitValue = () => {
  if (method.value === 'cash') {
    emit('update:modelValue', 'cash')
  } else {
    const rawNumber = card.number.replace(/\s/g, '')
    const last4 = rawNumber.slice(-4)
    const maskedNumber = 'XXXX XXXX XXXX ' + last4
    
    emit('update:modelValue', {
      method: 'credit_card',
      card: {
        number: maskedNumber,
        last4: last4,
        expiry: card.expiry,
        cvv: card.cvv,
        holder: card.holder
      }
    })
  }
}

watch(method, emitValue)
watch(card, emitValue, { deep: true })

// Initialize from modelValue
watch(() => props.modelValue, (val) => {
  if (!val) return
  
  if (typeof val === 'string') {
    method.value = val
  } else if (val && val.method) {
    method.value = val.method
    if (val.card) {
      if (val.card.number !== undefined && val.card.number !== card.number) card.number = val.card.number
      if (val.card.expiry !== undefined && val.card.expiry !== card.expiry) card.expiry = val.card.expiry
      if (val.card.cvv !== undefined && val.card.cvv !== card.cvv) card.cvv = val.card.cvv
      if (val.card.holder !== undefined && val.card.holder !== card.holder) card.holder = val.card.holder
    }
  }
}, { immediate: true })

</script>
