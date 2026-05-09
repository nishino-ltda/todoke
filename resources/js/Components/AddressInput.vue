<template>
  <div class="address-input-container">
    <v-combobox
      v-if="geocode"
      v-model="selectedItem"
      v-model:search="searchQuery"
      :items="items"
      :loading="loading"
      :label="t('components.address.label')"
      :placeholder="t('components.address.placeholder')"
      :error-messages="errorMessages"
      item-title="address"
      item-value="address"
      hide-no-data
      clearable
      return-object
      data-cy="address-input-combobox"
      data-cy="address-combobox"
      @update:search="onSearch"
    >
      <template v-slot:append-inner>
        <v-progress-circular
          v-if="loading"
          indeterminate
          size="20"
          width="2"
          data-cy="address-loading"
        />
      </template>
    </v-combobox>

    <v-textarea
      v-else
      v-model="address"
      :label="t('components.address.label')"
      :error-messages="errors"
      rows="3"
      auto-grow
      required
      data-cy="address-input"
      data-cy="address-textarea"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import mapService from '@/services/map'

const props = defineProps({
  modelValue: [String, Object],
  errors: {
    type: Array,
    default: () => []
  },
  geocode: {
    type: Boolean,
    default: false
  }
})

const { t } = useI18n()
const emit = defineEmits(['update:modelValue'])

const selectedItem = ref(null)
const searchQuery = ref('')
const items = ref([])
const loading = ref(false)
const geocodeError = ref(null)

const errorMessages = computed(() => {
  const allErrors = [...props.errors]
  if (geocodeError.value) {
    allErrors.push(geocodeError.value)
  }
  return allErrors
})

const address = computed({
  get: () => typeof props.modelValue === 'string' ? props.modelValue : props.modelValue?.address,
  set: (value) => emit('update:modelValue', value)
})

let timeoutId = null
const onSearch = (val) => {
  if (!val || val.length < 3) {
    items.value = []
    return
  }

  if (timeoutId) clearTimeout(timeoutId)
  timeoutId = setTimeout(async () => {
    loading.value = true
    geocodeError.value = null
    try {
      const response = await mapService.geocode(val)
      items.value = response.data || []
    } catch (err) {
      geocodeError.value = t('components.address.error')
      console.error(err)
    } finally {
      loading.value = false
    }
  }, 300)
}

watch(selectedItem, (val) => {
  const currentPropValue = typeof props.modelValue === 'object' ? props.modelValue?.address : props.modelValue
  const newValue = typeof val === 'object' ? val?.address : val
  
  if (newValue === currentPropValue && typeof val !== 'object') return

  if (val && typeof val === 'object') {
    emit('update:modelValue', {
      address: val.address,
      lat: val.lat,
      lng: val.lng
    })
  } else if (val !== undefined) {
    emit('update:modelValue', val)
  }
})

watch(() => props.modelValue, (newVal) => {
  if (newVal && typeof newVal === 'object') {
    selectedItem.value = newVal
    searchQuery.value = newVal.address
  } else {
    selectedItem.value = newVal
    searchQuery.value = newVal
  }
}, { immediate: true })

</script>
