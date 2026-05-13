<template>
  <div class="address-input-container">
    <v-combobox
      v-if="geocode"
      v-model="selectedItem"
      v-model:search="searchQuery"
      :items="suggestions"
      :loading="loading"
      :label="t('components.address.label')"
      :placeholder="t('components.address.placeholder')"
      :error-messages="errorMessages"
      item-title="address"
      item-value="address"
      hide-no-data
      clearable
      return-object
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

    <v-btn
      v-if="geocode"
      variant="text"
      color="primary"
      prepend-icon="mdi-crosshairs-gps"
      size="small"
      class="mt-1 mb-2 px-0 text-none"
      :loading="geolocating"
      @click="useCurrentLocation"
      data-cy="use-current-location"
    >
      {{ t('components.address.use_current_location') }}
    </v-btn>

    <v-textarea
      v-else
      v-model="address"
      :label="t('components.address.label')"
      :error-messages="errorMessages"
      rows="3"
      auto-grow
      required
      data-cy="address-textarea"
    />

    <div v-if="geocode && savedAddresses.length > 0" class="saved-addresses mb-2">
      <v-divider class="mb-2" />
      <div class="text-caption text-medium-emphasis mb-1">{{ t('components.address.saved_places') }}</div>
      <v-chip
        v-for="addr in savedAddresses"
        :key="addr.id"
        :prepend-icon="addr.is_default ? 'mdi-home-check' : 'mdi-map-marker'"
        size="small"
        variant="outlined"
        class="mr-1 mb-1"
        @click="selectSavedAddress(addr)"
        data-cy="saved-address-chip"
      >
        {{ addr.label }} — {{ addr.address }}
      </v-chip>
    </div>

    <v-btn
      v-if="geocode"
      variant="text"
      color="secondary"
      prepend-icon="mdi-format-list-bulleted"
      size="small"
      class="mt-1 mb-2 px-0 text-none"
      @click="openAddressManager"
      data-cy="manage-addresses-btn"
    >
      {{ t('components.address.manage_addresses') }}
    </v-btn>

    <AddressManagerModal
      v-model="showAddressManager"
      @address-selected="onAddressCreated"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import mapService from '@/services/map'
import api from '@/services/api'
import AddressManagerModal from './AddressManagerModal.vue'

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
const suggestions = ref([])
const savedAddresses = ref([])
const loading = ref(false)
const geolocating = ref(false)
const geocodeError = ref(null)
const showAddressManager = ref(false)

onMounted(async () => {
  if (props.geocode) {
    await fetchSavedAddresses()
  }
})

const fetchSavedAddresses = async () => {
  try {
    const response = await api.get('/customer/addresses')
    savedAddresses.value = response.data.data || []
    if (savedAddresses.value.length > 0) {
      suggestions.value = [...savedAddresses.value]
    }
  } catch (err) {
    console.warn('Could not fetch saved addresses', err)
  }
}

const useCurrentLocation = () => {
  if (!navigator.geolocation) {
    geocodeError.value = t('components.address.geolocation_not_supported')
    return
  }

  geolocating.value = true
  navigator.geolocation.getCurrentPosition(
    async (position) => {
      const { latitude, longitude } = position.coords
      try {
        const response = await mapService.reverseGeocode(latitude, longitude)
        if (response.data) {
          selectedItem.value = response.data
          emit('update:modelValue', response.data)
        }
      } catch (err) {
        console.error(err)
        geocodeError.value = t('components.address.error')
      } finally {
        geolocating.value = false
      }
    },
    (error) => {
      console.error(error)
      geolocating.value = false
      geocodeError.value = t('components.address.geolocation_error')
    }
  )
}

const selectSavedAddress = (addr) => {
  const obj = {
    address: addr.address,
    lat: addr.lat,
    lng: addr.lng,
    address_id: addr.id,
  }
  selectedItem.value = obj
  searchQuery.value = addr.address
  emit('update:modelValue', obj)
}

const openAddressManager = () => {
  showAddressManager.value = true
}

const onAddressCreated = async (newAddress) => {
  await fetchSavedAddresses()
  if (newAddress) {
    selectSavedAddress(newAddress)
  }
}

const errorMessages = computed(() => {
  const baseErrors = Array.isArray(props.errors) ? props.errors : []
  const allErrors = [...baseErrors]
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
    suggestions.value = [...savedAddresses.value]
    return
  }

  if (timeoutId) clearTimeout(timeoutId)
  timeoutId = setTimeout(async () => {
    loading.value = true
    geocodeError.value = null
    try {
      const response = await mapService.geocode(val)
      const geocoded = response.data || []
      suggestions.value = [...savedAddresses.value, ...geocoded]
    } catch (err) {
      geocodeError.value = t('components.address.error')
      console.error(err)
    } finally {
      loading.value = false
    }
  }, 500)
}

watch(selectedItem, (val) => {
  const currentPropValue = typeof props.modelValue === 'object' ? props.modelValue?.address : props.modelValue
  const newValue = typeof val === 'object' ? val?.address : val

  if (newValue === currentPropValue && typeof val !== 'object') return

  if (val && typeof val === 'object') {
    emit('update:modelValue', {
      address: val.address,
      lat: val.lat,
      lng: val.lng,
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
