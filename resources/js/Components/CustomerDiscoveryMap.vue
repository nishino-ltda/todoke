<template>
  <div class="customer-discovery-map mb-8" data-cy="customer-discovery-map">
    <div class="d-flex align-center justify-space-between mb-4">
      <h2 class="text-h5 font-weight-bold d-flex align-center">
        <v-icon icon="mdi-map-marker-radius" color="primary" class="mr-2"></v-icon>
        {{ $t('dashboard.nearby_partners', 'Perto de Você') }}
      </h2>
      <v-btn
        variant="text"
        color="primary"
        prepend-icon="mdi-crosshairs-gps"
        @click="startGeolocation"
        :loading="locating"
        data-cy="locate-me-btn"
      >
        {{ $t('dashboard.locate_me', 'Atualizar Localização') }}
      </v-btn>
    </div>

    <v-card elevation="2" class="rounded-xl overflow-hidden mb-6">
      <div id="discovery-map" ref="mapElement" style="height: 350px; width: 100%;"></div>
      <div v-if="!userLocation && !locating" class="map-overlay d-flex align-center justify-center">
         <v-btn color="primary" size="large" rounded="pill" @click="startGeolocation">
           <v-icon start icon="mdi-map-search"></v-icon>
           {{ $t('dashboard.show_nearby', 'Ver Parceiros no Mapa') }}
         </v-btn>
      </div>
    </v-card>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import api from '@/services/api'

const mapElement = ref(null)
const map = ref(null)
const locating = ref(false)
const userLocation = ref(null)
const partners = ref([])

const startGeolocation = () => {
  locating.value = true
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        userLocation.value = {
          lat: position.coords.latitude,
          lng: position.coords.longitude
        }
        locating.value = false
        setTimeout(initMap, 100)
      },
      (err) => {
        console.warn('Geolocation failed:', err)
        locating.value = false
        // Default to a central point (mock)
        userLocation.value = { lat: -23.5505, lng: -46.6333 } // São Paulo
        initMap()
      }
    )
  }
}

const fetchPartners = async () => {
  try {
    // In a real app, this would be filtered by proximity
    const response = await api.get('/products') // Using products as a proxy for partners for now
    // Group products by partner to show partner markers
    const seen = new Set()
    partners.value = (response.data.products || []).filter(p => {
        if (!seen.has(p.partner)) {
            seen.add(p.partner)
            return true
        }
        return false
    }).map(p => ({
        name: p.partner,
        // Mocking coordinates near the user if available
        lat: userLocation.value ? userLocation.value.lat + (Math.random() - 0.5) * 0.02 : -23.5505 + (Math.random() - 0.5) * 0.02,
        lng: userLocation.value ? userLocation.value.lng + (Math.random() - 0.5) * 0.02 : -46.6333 + (Math.random() - 0.5) * 0.02,
    }))
  } catch (err) {
    console.error('Failed to fetch partners:', err)
  }
}

const initMap = () => {
  if (map.value) {
    map.value.remove()
  }
  
  if (mapElement.value && userLocation.value) {
    map.value = L.map(mapElement.value).setView([userLocation.value.lat, userLocation.value.lng], 14)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map.value)

    // Fix for default marker icon issues
    delete L.Icon.Default.prototype._getIconUrl
    L.Icon.Default.mergeOptions({
      iconRetinaUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon-2x.png',
      iconUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png',
      shadowUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
    })

    // User marker
    L.circleMarker([userLocation.value.lat, userLocation.value.lng], {
      color: '#1976D2',
      fillColor: '#2196F3',
      fillOpacity: 0.8,
      radius: 8
    }).addTo(map.value).bindPopup('Você está aqui')

    fetchPartners().then(() => {
        partners.value.forEach(partner => {
            L.marker([partner.lat, partner.lng])
                .addTo(map.value)
                .bindPopup(partner.name)
        })
    })
  }
}

onUnmounted(() => {
  if (map.value) {
    map.value.remove()
  }
})
</script>

<style scoped>
.customer-discovery-map {
  position: relative;
}

.map-overlay {
    position: absolute;
    top: 48px;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(2px);
    z-index: 2;
    border-radius: 24px;
}

#discovery-map {
  z-index: 1;
}
</style>
