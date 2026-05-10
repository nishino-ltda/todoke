<template>
  <div class="delivery-map-container">
    <v-card v-if="loading" class="d-flex align-center justify-center" height="300" data-cy="map-loading">
      <v-progress-circular indeterminate color="primary" />
      <span class="ml-3">{{ t('courier.map.loading') }}</span>
    </v-card>

    <v-alert v-if="error" type="error" class="mb-4" data-cy="map-error">
      {{ t('courier.map.error') }}
    </v-alert>

    <div v-show="!loading" class="map-wrapper">
      <div id="map" ref="mapElement" style="height: 300px; width: 100%;" data-cy="delivery-map"></div>
      
      <v-row v-if="distanceInfo" class="mt-2 px-2">
        <v-col cols="6">
          <v-chip color="secondary" label data-cy="map-distance">
            {{ t('courier.map.distance', { distance: distanceInfo.distance }) }}
          </v-chip>
        </v-col>
        <v-col cols="6" class="text-right">
          <v-chip color="primary" label data-cy="map-time">
            {{ t('courier.map.estimatedTime', { time: distanceInfo.time }) }}
          </v-chip>
        </v-col>
      </v-row>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import mapService from '@/services/map'

const props = defineProps({
  origin: {
    type: Object,
    required: true // {lat, lng}
  },
  destination: {
    type: Object,
    required: true // {lat, lng}
  },
  currentPosition: {
    type: Object,
    default: null // {lat, lng}
  }
})

const { t } = useI18n()
const mapElement = ref(null)
const map = ref(null)
const loading = ref(true)
const error = ref(false)
const distanceInfo = ref(null)
const markers = ref({})
const routeLine = ref(null)

const initMap = async () => {
  try {
    loading.value = true
    error.value = false

    // Initialize Leaflet map
    if (mapElement.value) {
      map.value = L.map(mapElement.value).setView([props.origin.lat, props.origin.lng], 13)
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(map.value)

      // Add markers
      addMarkers()

      // Add route line
      addRoute()

      // Get distance/time
      await fetchDistance()

      loading.value = false
      
      // Fit bounds
      fitMapBounds()
    }
  } catch (err) {
    console.error('Map init failed:', err)
    error.value = true
    loading.value = false
  }
}

const addMarkers = () => {
  // Fix for default marker icon issues in bundlers
  delete L.Icon.Default.prototype._getIconUrl
  L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon-2x.png',
    iconUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
  })

  markers.value.origin = L.marker([props.origin.lat, props.origin.lng])
    .addTo(map.value)
    .bindPopup(t('courier.map.origin'))

  markers.value.destination = L.marker([props.destination.lat, props.destination.lng])
    .addTo(map.value)
    .bindPopup(t('courier.map.destination'))

  updateCurrentPositionMarker()
}

const updateCurrentPositionMarker = () => {
  const pos = props.currentPosition
  if (pos && map.value) {
    if (markers.value.current) {
      markers.value.current.setLatLng([pos.lat, pos.lng])
    } else {
      markers.value.current = L.circleMarker([pos.lat, pos.lng], {
        color: '#1976D2',
        fillColor: '#2196F3',
        fillOpacity: 0.8,
        radius: 8
      }).addTo(map.value).bindPopup(t('courier.map.current'))
    }
  }
}

const addRoute = () => {
  if (map.value) {
    const latlngs = [
      [props.origin.lat, props.origin.lng],
      [props.destination.lat, props.destination.lng]
    ]
    routeLine.value = L.polyline(latlngs, { color: 'blue', weight: 5, opacity: 0.6 }).addTo(map.value)
  }
}

const fetchDistance = async () => {
  try {
    const response = await mapService.getDistance(props.origin, props.destination)
    const data = response.data || response
    distanceInfo.value = {
      distance: data.distance || '0',
      time: data.duration || '0'
    }
  } catch (err) {
    console.warn('Failed to fetch distance', err)
  }
}

const fitMapBounds = () => {
  if (map.value) {
    const markerArray = Object.values(markers.value).filter(m => !!m)
    if (markerArray.length > 0) {
      const group = new L.featureGroup(markerArray)
      map.value.fitBounds(group.getBounds().pad(0.1))
    }
  }
}

const handleGeolocation = () => {
  if (!props.currentPosition && navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const { latitude, longitude } = position.coords
        if (map.value) {
           const pos = { lat: latitude, lng: longitude }
           if (markers.value.current) {
             markers.value.current.setLatLng([pos.lat, pos.lng])
           } else {
             markers.value.current = L.circleMarker([pos.lat, pos.lng], {
                color: '#1976D2',
                fillColor: '#2196F3',
                fillOpacity: 0.8,
                radius: 8
              }).addTo(map.value).bindPopup(t('courier.map.current'))
           }
        }
      },
      (err) => console.warn('Geolocation failed', err)
    )
  }
}

onMounted(() => {
  initMap()
  handleGeolocation()
})

onUnmounted(() => {
  if (map.value) {
    map.value.remove()
  }
})

watch(() => props.currentPosition, updateCurrentPositionMarker, { deep: true })
watch([() => props.origin, () => props.destination], () => {
  if (map.value) {
    map.value.remove()
    initMap()
  }
}, { deep: true })
</script>

<style scoped>
.map-wrapper {
  position: relative;
  width: 100%;
}
#map {
  border-radius: 8px;
  z-index: 1;
}
</style>
