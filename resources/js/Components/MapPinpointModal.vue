<template>
  <v-dialog v-model="show" fullscreen data-cy="map-pinpoint-modal">
    <v-card>
      <v-card-title class="d-flex align-center">
        <span>{{ $t('components.map_pinpoint.title') }}</span>
        <v-spacer />
        <v-btn icon variant="text" @click="close" data-cy="map-pinpoint-close">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-card-title>

      <v-card-text class="pa-0" style="height: calc(100vh - 120px);">
        <div id="pinpoint-map" ref="mapElement" style="height: 100%; width: 100%;"></div>
      </v-card-text>

      <v-card-actions class="pa-4">
        <v-row>
          <v-col cols="12">
            <v-text-field
              :model-value="selectedAddress"
              :label="$t('components.map_pinpoint.selected_address')"
              readonly
              variant="outlined"
              data-cy="pinpoint-address"
            />
          </v-col>
          <v-col cols="6">
            <v-text-field
              :model-value="currentLat"
              :label="$t('components.map_pinpoint.latitude')"
              readonly
              variant="outlined"
              data-cy="pinpoint-lat"
            />
          </v-col>
          <v-col cols="6">
            <v-text-field
              :model-value="currentLng"
              :label="$t('components.map_pinpoint.longitude')"
              readonly
              variant="outlined"
              data-cy="pinpoint-lng"
            />
          </v-col>
        </v-row>
      </v-card-actions>

      <v-card-actions class="pa-4 pt-0">
        <v-spacer />
        <v-btn variant="text" @click="close">
          {{ $t('components.map_pinpoint.cancel') }}
        </v-btn>
        <v-btn color="primary" :loading="confirming" @click="confirmLocation" data-cy="confirm-pinpoint-btn">
          {{ $t('components.map_pinpoint.confirm') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import mapService from '@/services/map'

const props = defineProps({
  modelValue: Boolean,
  lat: { type: Number, default: -23.5505 },
  lng: { type: Number, default: -46.6333 },
})

const emit = defineEmits(['update:modelValue', 'confirm'])

const show = ref(false)
const mapElement = ref(null)
const map = ref(null)
const marker = ref(null)
const currentLat = ref(props.lat)
const currentLng = ref(props.lng)
const selectedAddress = ref('')
const confirming = ref(false)
const geocodedResult = ref(null)

watch(() => props.modelValue, async (val) => {
  show.value = val
  if (val) {
    currentLat.value = props.lat
    currentLng.value = props.lng
    await nextTick()
    initMap()
    if (map.value) {
      setTimeout(() => {
        map.value.invalidateSize()
      }, 100)
    }
    await reverseGeocode()
  }
})

watch(show, (val) => {
  if (!val) {
    if (map.value) {
      map.value.remove()
      map.value = null
    }
  }
  emit('update:modelValue', val)
})

watch([() => props.lat, () => props.lng], () => {
  currentLat.value = props.lat
  currentLng.value = props.lng
})

const initMap = () => {
  if (mapElement.value && !map.value) {
    delete L.Icon.Default.prototype._getIconUrl
    L.Icon.Default.mergeOptions({
      iconRetinaUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon-2x.png',
      iconUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png',
      shadowUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
    })

    map.value = L.map(mapElement.value).setView([currentLat.value, currentLng.value], 16)

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map.value)

    marker.value = L.marker([currentLat.value, currentLng.value], { draggable: true }).addTo(map.value)

    marker.value.on('dragend', async () => {
      const pos = marker.value.getLatLng()
      currentLat.value = parseFloat(pos.lat.toFixed(7))
      currentLng.value = parseFloat(pos.lng.toFixed(7))
      await reverseGeocode()
    })

    map.value.on('click', async (e) => {
      const { lat, lng } = e.latlng
      currentLat.value = parseFloat(lat.toFixed(7))
      currentLng.value = parseFloat(lng.toFixed(7))
      marker.value.setLatLng([lat, lng])
      await reverseGeocode()
    })
  }
}

const reverseGeocode = async () => {
  try {
    const response = await mapService.reverseGeocode(currentLat.value, currentLng.value)
    if (response.data?.data) {
      const data = response.data.data
      selectedAddress.value = data.address || `${currentLat.value}, ${currentLng.value}`
      // Store full data to emit later
      geocodedResult.value = data
    }
  } catch (err) {
    selectedAddress.value = `${currentLat.value}, ${currentLng.value}`
  }
}

const confirmLocation = async () => {
  confirming.value = true
  await reverseGeocode()
  emit('confirm', {
    lat: currentLat.value,
    lng: currentLng.value,
    address: selectedAddress.value,
    ...(geocodedResult.value || {})
  })
  confirming.value = false
  show.value = false
}

const close = () => {
  show.value = false
}
</script>

<style scoped>
#pinpoint-map {
  z-index: 1;
  cursor: crosshair;
}

:deep(.leaflet-control-container) {
  z-index: 1000;
}
</style>
