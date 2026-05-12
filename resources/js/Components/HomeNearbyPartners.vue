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

      <!-- Location Result Banner -->
      <v-expand-transition>
        <v-alert
          v-if="locationText"
          :type="locationError ? 'warning' : 'success'"
          variant="tonal"
          class="mb-6 rounded-lg"
          density="comfortable"
          data-cy="location-result"
        >
          <div class="d-flex align-center">
            <v-icon
              :icon="locationError ? 'mdi-alert-circle-outline' : 'mdi-map-marker-radius'"
              class="mr-3"
              size="24"
            ></v-icon>
            <span class="text-body-1 font-weight-medium">{{ locationText }}</span>
          </div>
        </v-alert>
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
          <v-card class="product-card h-100 rounded-lg" elevation="1" hover @click="viewProduct(product)">
            <v-img
              :src="resolveImageUrl(product.image)"
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
                @click.stop="viewProduct(product)"
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
    <ProductDetailsModal
      v-if="selectedProduct"
      :product="selectedProduct"
      v-model="showDetails"
      @close="showDetails = false"
    />
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import ProductDetailsModal from '@/Components/ProductDetailsModal.vue'

const { t } = useI18n()
const loading = ref(true)
const locating = ref(false)
const products = ref([])
const selectedProduct = ref(null)
const showDetails = ref(false)
const locationText = ref('')
const locationError = ref(false)

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
  locationText.value = ''
  locationError.value = false
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const uniquePartners = new Set()
        products.value.forEach(p => {
          if (p.partner) uniquePartners.add(p.partner)
        })
        const count = uniquePartners.size
        if (count > 0) {
          locationText.value = `${count} ${count === 1 ? 'parceiro' : 'parceiros'} na região`
        } else {
          locationText.value = 'Nenhum parceiro na região'
        }
        locationError.value = false
        locating.value = false
      },
      (err) => {
        console.warn('Geolocation failed:', err)
        locationText.value = 'Não foi possível determinar sua localização. Tente novamente.'
        locationError.value = true
        locating.value = false
      }
    )
  } else {
    locationText.value = 'Geolocalização não suportada pelo navegador.'
    locationError.value = true
    locating.value = false
  }
}

const resolveImageUrl = (path) => {
  if (!path) return 'https://cdn.pixabay.com/photo/2017/02/15/10/39/food-2068221_1280.jpg'
  if (path.startsWith('http')) return path
  return `/storage/${path}`
}

const viewProduct = (product) => {
    selectedProduct.value = product
    showDetails.value = true
}

onMounted(() => {
  fetchProducts()
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
</style>
