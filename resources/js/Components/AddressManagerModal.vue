<template>
  <v-dialog v-model="show" max-width="600" persistent data-cy="address-manager-modal">
    <v-card>
      <v-card-title class="d-flex align-center">
        <span>{{ title }}</span>
        <v-spacer />
        <v-btn icon variant="text" @click="close" data-cy="address-manager-close">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-card-title>

      <v-card-text v-if="editingAddress === null">
        <v-list v-if="addresses.length > 0" data-cy="address-list">
          <v-list-item
            v-for="addr in addresses"
            :key="addr.id"
            class="mb-2 rounded-lg border"
          >
            <template v-slot:prepend>
              <v-icon :color="addr.is_default ? 'primary' : undefined">
                {{ addr.is_default ? 'mdi-home-check' : 'mdi-map-marker' }}
              </v-icon>
            </template>

            <v-list-item-title>{{ addr.label }}</v-list-item-title>
            <v-list-item-subtitle>
              {{ addr.address }}, {{ addr.city }} - {{ addr.state }}
            </v-list-item-subtitle>

            <template v-slot:append>
              <v-btn icon variant="text" size="small" @click="editAddress(addr)" data-cy="edit-address-btn">
                <v-icon size="small">mdi-pencil</v-icon>
              </v-btn>
              <v-btn icon variant="text" size="small" color="error" @click="deleteAddress(addr)" data-cy="delete-address-btn">
                <v-icon size="small">mdi-delete</v-icon>
              </v-btn>
            </template>

            <template v-slot:default v-if="!addr.is_default">
              <v-btn variant="text" size="small" color="primary" @click="setDefault(addr)" data-cy="set-default-btn">
                {{ $t('components.address.set_default') }}
              </v-btn>
            </template>
          </v-list-item>
        </v-list>

        <v-alert v-else type="info" class="mb-4">
          {{ $t('components.address.no_saved_addresses') }}
        </v-alert>
      </v-card-text>

      <v-card-text v-else>
        <v-form ref="formRef">
          <v-select
            v-if="!isCustomLabel"
            v-model="form.label"
            :label="$t('components.address.label_field')"
            :items="['Casa', 'Trabalho', 'Outro']"
            :rules="[v => !!v || 'Required']"
            required
            data-cy="address-label-select"
          />
          <v-text-field
            v-else
            v-model="form.label"
            :label="$t('components.address.label_field')"
            :rules="[v => !!v || 'Required']"
            append-inner-icon="mdi-close"
            required
            @click:append-inner="resetLabel"
            data-cy="address-label-input"
          />

          <v-combobox
            v-model="selectedAddress"
            v-model:search="searchQuery"
            :items="suggestions"
            :loading="loading"
            :label="$t('components.address.address_field')"
            :rules="[v => !!v || 'Required']"
            item-title="address"
            item-value="address"
            hide-no-data
            required
            data-cy="address-input-field"
            @update:search="onSearch"
          >
            <template v-slot:append-inner>
              <v-progress-circular
                v-if="loading"
                indeterminate
                size="20"
                width="2"
              />
            </template>
          </v-combobox>
          <v-row>
            <v-col cols="6">
              <v-text-field
                v-model="form.complement"
                :label="$t('components.address.complement')"
                data-cy="address-complement-input"
              />
            </v-col>
            <v-col cols="6">
              <v-text-field
                v-model="form.neighborhood"
                :label="$t('components.address.neighborhood')"
                data-cy="address-neighborhood-input"
              />
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="5">
              <v-text-field
                v-model="form.city"
                :label="$t('components.address.city')"
                :rules="[v => !!v || 'Required']"
                required
                data-cy="address-city-input"
              />
            </v-col>
            <v-col cols="3">
              <v-text-field
                v-model="form.state"
                :label="$t('components.address.state')"
                :rules="[v => !!v || 'Required', v => v.length === 2 || 'Use 2-letter code']"
                maxlength="2"
                required
                data-cy="address-state-input"
              />
            </v-col>
            <v-col cols="4">
              <v-text-field
                v-model="form.zip_code"
                :label="$t('components.address.zip_code')"
                data-cy="address-zip-input"
              />
            </v-col>
          </v-row>
          <v-switch
            v-model="form.is_default"
            :label="$t('components.address.set_as_default')"
            color="primary"
            data-cy="address-default-switch"
          />
        </v-form>
      </v-card-text>

      <v-card-actions v-if="editingAddress === null">
        <v-spacer />
        <v-btn color="primary" variant="text" @click="addNew" data-cy="add-address-btn">
          {{ $t('components.address.add_new') }}
        </v-btn>
      </v-card-actions>

      <v-card-actions v-else>
        <v-spacer />
        <v-btn variant="text" @click="editingAddress = null">
          {{ $t('components.address.cancel') }}
        </v-btn>
        <v-btn color="primary" :loading="saving" @click="saveAddress" data-cy="save-address-btn">
          {{ editingAddress.id ? $t('components.address.update') : $t('components.address.create') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import api from '@/services/api'
import mapService from '@/services/map'

const props = defineProps({
  modelValue: Boolean,
})

const emit = defineEmits(['update:modelValue', 'address-selected'])

const show = ref(false)
const addresses = ref([])
const editingAddress = ref(null)
const saving = ref(false)
const formRef = ref(null)

const isCustomLabel = ref(false)
const searchQuery = ref('')
const suggestions = ref([])
const loading = ref(false)
const selectedAddress = ref(null)

const form = ref({
  label: 'Casa',
  address: '',
  complement: '',
  neighborhood: '',
  city: '',
  state: '',
  zip_code: '',
  lat: null,
  lng: null,
  is_default: false,
})

watch(() => form.value.label, (newVal) => {
  if (newVal === 'Outro') {
    isCustomLabel.value = true
    form.value.label = ''
  }
})

const resetLabel = () => {
  isCustomLabel.value = false
  form.value.label = 'Casa'
}

let timeoutId = null
const onSearch = (val) => {
  if (!val || val.length < 3) {
    suggestions.value = []
    return
  }

  if (timeoutId) clearTimeout(timeoutId)
  timeoutId = setTimeout(async () => {
    loading.value = true
    try {
      const response = await mapService.geocode(val)
      suggestions.value = response.data || []
    } catch (err) {
      console.error('Geocoding error', err)
    } finally {
      loading.value = false
    }
  }, 500) // 500ms debounce
}

watch(selectedAddress, (val) => {
  if (!val) return
  
  if (typeof val === 'object') {
    form.value.address = val.address
    form.value.lat = val.lat
    form.value.lng = val.lng
    // Try to extract city/state/neighborhood if present in Nominatim response
    // Usually Nominatim returns them in address object if format=json&addressdetails=1 is used
    // But our backend MapController might be stripping them or not.
    // Let's check if they are available.
    if (val.city) form.value.city = val.city
    if (val.state) form.value.state = val.state
    if (val.neighborhood) form.value.neighborhood = val.neighborhood
  } else {
    form.value.address = val
  }
})

watch(() => props.modelValue, async (val) => {
  show.value = val
  if (val) {
    await fetchAddresses()
  }
})

watch(show, (val) => {
  if (!val) {
    editingAddress.value = null
  }
  emit('update:modelValue', val)
})

const fetchAddresses = async () => {
  try {
    const response = await api.get('/customer/addresses')
    addresses.value = response.data.data || []
  } catch (err) {
    console.error('Failed to fetch addresses', err)
  }
}

const addNew = () => {
  editingAddress.value = {}
  form.value = { label: 'Casa', address: '', complement: '', neighborhood: '', city: '', state: '', zip_code: '', lat: null, lng: null, is_default: false }
  isCustomLabel.value = false
  selectedAddress.value = null
}

const editAddress = (addr) => {
  editingAddress.value = addr
  form.value = { ...addr }
  isCustomLabel.value = !['Casa', 'Trabalho'].includes(addr.label)
  selectedAddress.value = addr.address
}

const deleteAddress = async (addr) => {
  try {
    await api.delete(`/customer/addresses/${addr.id}`)
    await fetchAddresses()
  } catch (err) {
    console.error('Failed to delete address', err)
  }
}

const setDefault = async (addr) => {
  try {
    await api.patch(`/customer/addresses/${addr.id}/default`)
    await fetchAddresses()
  } catch (err) {
    console.error('Failed to set default address', err)
  }
}

const saveAddress = async () => {
  const valid = await formRef.value?.validate()
  if (!valid?.valid) return

  saving.value = true
  try {
    if (editingAddress.value?.id) {
      await api.put(`/customer/addresses/${editingAddress.value.id}`, form.value)
    } else {
      const response = await api.post('/customer/addresses', form.value)
      emit('address-selected', response.data.data)
    }
    await fetchAddresses()
    editingAddress.value = null
  } catch (err) {
    console.error('Failed to save address', err)
  } finally {
    saving.value = false
  }
}

const close = () => {
  show.value = false
}
</script>
