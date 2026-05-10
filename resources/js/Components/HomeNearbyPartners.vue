<template>
  <section class="nearby-partners py-12" data-cy="nearby-partners">
    <v-container>
      <div class="d-flex align-center justify-space-between mb-8">
        <div>
          <h2 class="text-h4 font-weight-bold mb-2">
            {{ t('home.nearby.title', 'Parceiros em Operação') }}
          </h2>
          <p class="text-subtitle-1 text-medium-emphasis mb-0">
            {{ t('home.nearby.subtitle', 'Descubra os melhores produtos perto de você') }}
          </p>
        </div>
        <v-btn
          color="primary"
          prepend-icon="mdi-crosshairs-gps"
          size="large"
          @click="startGeolocation"
          :loading="locating"
          data-cy="geolocation-btn"
        >
          {{ t('home.nearby.locate_me', 'Me Localizar') }}
        </v-btn>
      </div>

      <!-- Map Section -->
      <v-expand-transition>
        <div v-if="showMap" class="mb-10">
          <v-card elevation="2" class="rounded-xl overflow-hidden">
            <div id="home-map" ref="mapElement" style="height: 400px; width: 100%;"></div>
          </v-card>
        </div>
      </v-expand-transition>

      <!-- Products Grid -->
      <v-row v-if="loading" class="justify-center py-10">
        <v-progress-circular indeterminate color="primary" size="64" />
      </v-row>
      
      <v-row v-else-if="products.length > 0">
        <v-col 
          v-for="product in products.slice(0, 8)" 
          :key="product.id" 
          cols="12" sm="6" md="4" lg="3"
        >
          <v-card class="product-card h-100 rounded-lg" elevation="1" hover>
            <v-img
              :src="product.image || 'https://cdn.pixabay.com/photo/2017/02/15/10/39/food-2068221_1280.jpg'"
              height="200"
              cover
            >
              <template v-slot:placeholder>
                <v-row class="fill-height ma-0" align="center" justify="center">
                  <v-progress-circular indeterminate color="grey-lighten-5" />
                </v-row>
              </template>
            </v-img>
            
            <v-card-item>
              <v-card-title class="text-h6 font-weight-bold">{{ product.name }}</v-card-title>
              <v-card-subtitle class="d-flex align-center mt-1">
                <v-icon icon="mdi-store" size="small" color="primary" class="mr-1"></v-icon>
                {{ product.partner }}
              </v-card-subtitle>
            </v-card-item>

            <v-card-text>
              <p class="text-truncate-2 text-body-2 text-medium-emphasis">
                {{ product.description }}
              </p>
              <div class="text-h6 font-weight-bold mt-2 text-primary">
                {{ formatPrice(product.price) }}
              </div>
            </v-card-text>

            <v-card-actions>
              <v-btn
                variant="tonal"
                color="primary"
                block
                @click="viewProduct(product)"
                data-cy="view-product-btn"
              >
                {{ t('home.nearby.view_details', 'Ver Detalhes') }}
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
      
      <v-row v-else class="justify-center py-10">
        <p class="text-h6 text-medium-emphasis">Nenhum parceiro operando no momento.</p>
      </v-row>
    </v-container>
  </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const { t } = useI18n()
const loading = ref(true)
const locating = ref(false)
const showMap = ref(false)
const products = ref([])
const mapElement = ref(null)
const map = ref(null)
const userLocation = ref(null)

const fetchProducts = async () => {
  loading.value = true
  try {
    const response = await api.get('/products')
    products.value = response.data.products || []
  } catch (err) {
    console.error('Failed to fetch products:', err)
  } finally {
    loading.value = false
  }
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(price)
}

const startGeolocation = () => {
  locating.value = true
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        userLocation.value = {
          lat: position.coords.latitude,
          lng: position.coords.longitude
        }
        showMap.value = true
        locating.value = false
        setTimeout(initMap, 100)
      },
      (err) => {
        console.warn('Geolocation failed:', err)
        locating.value = false
        // Fallback or alert
      }
    )
  } else {
    locating.value = false
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

    // User marker
    L.circleMarker([userLocation.value.lat, userLocation.value.lng], {
      color: '#1976D2',
      fillColor: '#2196F3',
      fillOpacity: 0.8,
      radius: 8
    }).addTo(map.value).bindPopup(t('home.nearby.your_location', 'Sua Localização'))

    // Fix for default marker icon issues in bundlers
    delete L.Icon.Default.prototype._getIconUrl
    L.Icon.Default.mergeOptions({
      iconRetinaUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon-2x.png',
      iconUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png',
      shadowUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
    })

    // Mock partner markers around the user for demonstration
    const mockPartners = [
      { name: 'Pizzaria Bella Ciao', lat: userLocation.value.lat + 0.005, lng: userLocation.value.lng + 0.003 },
      { name: 'Burger Master', lat: userLocation.value.lat - 0.004, lng: userLocation.value.lng + 0.006 },
      { name: 'Sushi Express', lat: userLocation.value.lat + 0.007, lng: userLocation.value.lng - 0.002 }
    ]

    mockPartners.forEach(partner => {
      L.marker([partner.lat, partner.lng])
        .addTo(map.value)
        .bindPopup(partner.name)
    })
  }
}

const viewProduct = (product) => {
    // In a real app, this would navigate to the partner's page
    console.log('Viewing product:', product)
}

onMounted(() => {
  fetchProducts()
})

onUnmounted(() => {
  if (map.value) {
    map.value.remove()
  }
})
</script>

<style scoped>
.nearby-partners {
  background: #fafafa;
}

.product-card {
  transition: transform 0.2s ease-in-out;
}

.product-card:hover {
  transform: translateY(-4px);
}

.text-truncate-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

#home-map {
  z-index: 1;
}
</style>
