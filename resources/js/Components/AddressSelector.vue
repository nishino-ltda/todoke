<template>
  <div class="address-selector-container">
    <v-overlay :model-value="geolocating" class="align-center justify-center" persistent z-index="9999">
      <div class="text-center">
        <v-progress-circular indeterminate size="64" color="primary" width="6" />
        <div class="mt-4 text-h6 font-weight-bold text-white">{{ t('components.address.searching') }}</div>
      </div>
    </v-overlay>
    <v-select
      v-model="selection"
      :items="selectorOptions"
      :label="t('components.address.label')"
      :loading="loadingAddresses"
      item-title="title"
      item-value="id"
      variant="outlined"
      rounded="lg"
      return-object
      @update:model-value="handleSelectionChange"
      data-cy="address-mode-select"
    >
      <template v-slot:prepend-inner>
        <v-icon color="primary">{{ currentIcon }}</v-icon>
      </template>

      <template v-slot:item="{ props, item }">
        <v-divider v-if="item.raw.divider" class="my-2" />
        <v-list-item v-else v-bind="props" :prepend-icon="item.raw.icon">
          <template v-slot:title>
            <span :class="{ 'font-weight-bold': item.raw.type === 'action' }">{{ item.title }}</span>
          </template>
        </v-list-item>
      </template>
    </v-select>

    <v-expand-transition>
      <div v-if="selection?.id === 'typing'" class="mt-4">
        <v-text-field
          v-model="typedAddress"
          :label="t('components.address.placeholder')"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          rounded="lg"
          clearable
          :loading="geocoding"
          @update:model-value="onTypedAddressChange"
          data-cy="address-typing-input"
        />
      </div>
    </v-expand-transition>

    <v-expand-transition>
      <div v-if="geocodedResult" class="mt-4">
        <div class="mb-4 d-flex align-center bg-grey-lighten-4 pa-3 rounded-lg border">
          <v-icon color="success" size="small" class="mr-2">mdi-check-circle</v-icon>
          <span class="text-caption text-medium-emphasis flex-grow-1">{{ geocodedResult.address }}</span>
          <v-btn variant="text" size="small" color="primary" @click="openPinpoint" data-cy="adjust-pinpoint">
            {{ t('checkout.adjust_on_map') }}
          </v-btn>
        </div>

        <v-checkbox
          v-model="saveAsNew"
          :label="t('components.address.save_this_address')"
          color="primary"
          density="compact"
          class="mt-2"
          hide-details
          data-cy="save-address-checkbox"
        />

        <v-expand-transition>
          <v-text-field
            v-if="saveAsNew"
            v-model="addressLabel"
            :label="t('components.address.label_field')"
            variant="outlined"
            density="compact"
            class="mt-2"
            rounded="lg"
            placeholder="Ex: Casa do Papaco"
            data-cy="save-address-label"
          />
        </v-expand-transition>
      </div>
    </v-expand-transition>

    <MapPinpointModal
      v-model="showPinpoint"
      :lat="currentCoords.lat"
      :lng="currentCoords.lng"
      @confirm="onPinpointConfirm"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import mapService from '@/services/map'
import MapPinpointModal from './MapPinpointModal.vue'

const props = defineProps({
  modelValue: [Object, String],
  errors: Array
})

const emit = defineEmits(['update:modelValue'])

const { t } = useI18n()

const selection = ref(null)
const savedAddresses = ref([])
const loadingAddresses = ref(false)
const geocoding = ref(false)
const geolocating = ref(false)
const typedAddress = ref('')
const geocodedResult = ref(null)
const showPinpoint = ref(false)
const saveAsNew = ref(false)
const addressLabel = ref('')

const currentCoords = ref({ lat: -23.5505, lng: -46.6333 })

const selectorOptions = computed(() => {
  const options = savedAddresses.value.map(addr => ({
    title: `${addr.label} — ${addr.address}`,
    id: addr.id,
    type: 'saved',
    icon: 'mdi-map-marker',
    data: addr
  }))

  if (options.length > 0) {
    options.push({ divider: true })
  }
  
  options.push({ title: t('components.address.use_coordinates') || 'Usar coordenadas', id: 'pinpoint', type: 'action', icon: 'mdi-crosshairs-gps' })
  options.push({ title: t('components.address.type_address') || 'Digitar endereço', id: 'typing', type: 'action', icon: 'mdi-keyboard-outline' })

  return options
})

const currentIcon = computed(() => {
  if (selection.value?.type === 'saved') return 'mdi-home-marker'
  if (selection.value?.id === 'pinpoint') return 'mdi-crosshairs-gps'
  if (selection.value?.id === 'typing') return 'mdi-keyboard-outline'
  return 'mdi-map-marker'
})

const syncSelectionFromModel = () => {
  if (!props.modelValue) {
    selection.value = null
    geocodedResult.value = null
    return
  }

  if (typeof props.modelValue === 'object') {
    // Try to match with a saved address (it could be 'id' or 'address_id')
    const addressId = props.modelValue.id || props.modelValue.address_id
    const matched = selectorOptions.value.find(o => o.type === 'saved' && o.id === addressId)
    
    if (matched) {
      selection.value = matched
    } else {
      // If we have coordinates but no match, it's a pinpoint or typed address
      if (props.modelValue.lat && props.modelValue.lng) {
        geocodedResult.value = props.modelValue
        typedAddress.value = props.modelValue.address || ''
        selection.value = selectorOptions.value.find(o => o.id === 'typing')
      }
    }
  }
}

watch(() => props.modelValue, (newVal) => {
  // Only sync if the change came from outside
  const currentId = selection.value?.data?.id || selection.value?.id
  const newId = newVal?.id || newVal?.address_id
  if (currentId !== newId) {
    syncSelectionFromModel()
  }
}, { deep: true })

onMounted(async () => {
  await fetchSavedAddresses()
  syncSelectionFromModel()
})

const fetchSavedAddresses = async () => {
  loadingAddresses.value = true
  try {
    const response = await api.get('/customer/addresses')
    savedAddresses.value = response.data.data || []
  } catch (err) {
    console.error('Failed to fetch addresses', err)
  } finally {
    loadingAddresses.value = false
  }
}

const handleSelectionChange = (val) => {
  if (!val) return

  if (val.type === 'saved') {
    geocodedResult.value = null
    typedAddress.value = ''
    emit('update:modelValue', {
      address: val.data.address,
      lat: val.data.lat,
      lng: val.data.lng,
      address_id: val.data.id
    })
  } else if (val.id === 'pinpoint') {
    openPinpoint()
  } else if (val.id === 'typing') {
    geocodedResult.value = null
    typedAddress.value = ''
    emit('update:modelValue', null)
  }
}

const openPinpoint = async () => {
  if (geocodedResult.value) {
    currentCoords.value = { 
      lat: geocodedResult.value.lat || -23.5505, 
      lng: geocodedResult.value.lng || -46.6333 
    }
  } else if (navigator.geolocation) {
    geolocating.value = true
    try {
      const position = await new Promise((resolve, reject) => {
        navigator.geolocation.getCurrentPosition(resolve, reject, { 
          enableHighAccuracy: true,
          timeout: 10000,
          maximumAge: 0
        })
      })
      currentCoords.value = {
        lat: position.coords.latitude,
        lng: position.coords.longitude
      }
    } catch (err) {
      console.warn('Geolocation failed', err)
    } finally {
      geolocating.value = false
    }
  }
  showPinpoint.value = true
}

let debounceTimeout = null
const onTypedAddressChange = (val) => {
  if (!val || val.length < 5) {
    geocodedResult.value = null
    return
  }

  if (debounceTimeout) clearTimeout(debounceTimeout)
  debounceTimeout = setTimeout(async () => {
    geocoding.value = true
    try {
      const response = await mapService.geocode(val)
      if (response.data?.data) {
        geocodedResult.value = response.data.data
        emitValue()
      }
    } catch (err) {
      console.error(err)
    } finally {
      geocoding.value = false
    }
  }, 800) // Increased debounce for better experience
}

const onPinpointConfirm = (data) => {
  geocodedResult.value = data
  typedAddress.value = data.address
  if (selection.value?.id !== 'typing') {
    selection.value = { id: 'pinpoint', type: 'action', title: 'Ajustado no mapa' }
  }
  emitValue()
}

const emitValue = async () => {
  if (!geocodedResult.value) return

  // Extract 2-letter state from state_short (e.g., BR-MS -> MS)
  let state = geocodedResult.value.state_short || geocodedResult.value.state || ''
  if (state.includes('-')) {
    state = state.split('-').pop()
  }
  if (state.length > 2) state = state.substring(0, 2).toUpperCase()

  const value = {
    address: geocodedResult.value.address,
    lat: geocodedResult.value.lat,
    lng: geocodedResult.value.lng,
    city: geocodedResult.value.city || '',
    state: state,
    neighborhood: geocodedResult.value.neighborhood || ''
  }

  if (saveAsNew.value && addressLabel.value) {
    value.save_as = addressLabel.value
    value.is_new = true
  }

  emit('update:modelValue', value)
}

watch([saveAsNew, addressLabel], emitValue)

</script>

<style scoped>
.address-selector-container {
  width: 100%;
  margin-bottom: 16px;
}
</style>
