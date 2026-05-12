<template>
  <GuestLayout>
    <div data-cy="partner-store" class="partner-store-page">
      <div v-if="partner">
        <!-- Partner Header -->
        <section class="partner-header py-8">
          <v-container>
            <v-btn
              variant="text"
              color="primary"
              prepend-icon="mdi-arrow-left"
              class="mb-4"
              @click="goBack"
              data-cy="back-btn"
            >
              Voltar
            </v-btn>
            <div class="d-flex align-center ga-4 flex-wrap">
              <v-avatar size="80" color="primary" variant="tonal">
                <v-icon icon="mdi-store" size="40" color="primary"></v-icon>
              </v-avatar>
              <div>
                <h1 class="text-h3 font-weight-bold">{{ partner.name }}</h1>
                <v-chip
                  v-if="partner.type"
                  size="small"
                  color="primary"
                  variant="tonal"
                  class="font-weight-medium text-uppercase mt-1"
                >
                  {{ partner.type }}
                </v-chip>
                <p v-if="partner.description" class="text-body-1 text-medium-emphasis mt-2">
                  {{ partner.description }}
                </p>
              </div>
            </div>
          </v-container>
        </section>

        <!-- Map Section -->
        <section class="partner-map-section py-6 bg-grey-lighten-4">
          <v-container>
            <h2 class="text-h5 font-weight-bold mb-4 d-flex align-center">
              <v-icon icon="mdi-map-marker-radius" color="primary" class="mr-2"></v-icon>
              Localização
            </h2>
            <v-card elevation="2" class="rounded-xl overflow-hidden">
              <div id="store-map" ref="mapElement" style="height: 350px; width: 100%;"></div>
            </v-card>
          </v-container>
        </section>

        <!-- Products Section -->
        <section class="partner-products py-8">
          <v-container>
            <h2 class="text-h4 font-weight-bold mb-6">
              Cardápio
            </h2>
            <v-row v-if="products.length > 0">
              <v-col
                v-for="product in products"
                :key="product.id"
                cols="12" sm="6" md="4" lg="3"
              >
                <ProductCard
                  :product="product"
                  @product-clicked="handleProductClicked"
                  @add-to-cart="handleAddToCart"
                />
              </v-col>
            </v-row>
            <v-empty-state
              v-else
              icon="mdi-food-off"
              title="Nenhum produto disponível"
              text="Este parceiro ainda não possui produtos no cardápio."
            ></v-empty-state>
          </v-container>
        </section>
      </div>

      <div v-else class="text-center py-16">
        <v-container>
          <v-icon icon="mdi-store-off" size="80" color="grey"></v-icon>
          <h2 class="text-h4 mt-4">Loja não encontrada</h2>
          <p class="text-body-1 text-medium-emphasis mt-2">
            O parceiro que você está procurando não existe ou está temporariamente indisponível.
          </p>
          <v-btn
            color="primary"
            class="mt-4"
            @click="goBack"
          >
            Voltar para a Home
          </v-btn>
        </v-container>
      </div>

      <ProductDetailsModal
        v-if="selectedProduct"
        :product="selectedProduct"
        v-model="showDetails"
        @close="showDetails = false"
      />
    </div>
  </GuestLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import ProductCard from '@/Components/ProductCard.vue'
import ProductDetailsModal from '@/Components/ProductDetailsModal.vue'
import { useCartStore } from '@/stores/cart'

const props = defineProps({
  partner: {
    type: Object,
    default: null,
  },
  products: {
    type: Array,
    default: () => [],
  },
})

const cartStore = useCartStore()
const mapElement = ref(null)
const map = ref(null)
const selectedProduct = ref(null)
const showDetails = ref(false)

const formatPrice = (value) => {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0)
}

const resolveImageUrl = (path) => {
  if (!path) return 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'
  if (path.startsWith('http')) return path
  return `/storage/${path}`
}

const goBack = () => {
  router.visit('/')
}

const handleProductClicked = (product) => {
  selectedProduct.value = product
  showDetails.value = true
}

const handleAddToCart = (product) => {
  cartStore.addItem(product)
}

const initMap = () => {
  if (map.value) {
    map.value.remove()
  }

  if (mapElement.value && props.partner) {
    const lat = props.partner.latitude || -23.5505
    const lng = props.partner.longitude || -46.6333

    map.value = L.map(mapElement.value).setView([lat, lng], 15)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map.value)

    delete L.Icon.Default.prototype._getIconUrl
    L.Icon.Default.mergeOptions({
      iconRetinaUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon-2x.png',
      iconUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png',
      shadowUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
    })

    L.marker([lat, lng])
      .addTo(map.value)
      .bindPopup(props.partner.name)
  }
}

onMounted(() => {
  if (props.partner) {
    setTimeout(initMap, 200)
  }
})

onUnmounted(() => {
  if (map.value) {
    map.value.remove()
  }
})
</script>

<style scoped>
.partner-header {
  background: #fafafa;
  border-bottom: 1px solid rgba(0,0,0,0.05);
}

.partner-map-section {
  background: #f5f5f5;
}

#store-map {
  z-index: 1;
}
</style>
