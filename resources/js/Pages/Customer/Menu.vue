<template>
  <CustomerLayout>
    <div data-cy="customer-menu" class="menu-container">
      <!-- Header & Search -->
      <header class="mb-8">
        <div class="d-flex align-center justify-space-between mb-4">
          <h1 class="text-h3 font-weight-black" data-cy="partner-name">
            {{ partnerData ? partnerData.name : $t('menu.title', 'Explorar Cardápio') }}
          </h1>
          <v-btn-toggle
            v-model="viewMode"
            mandatory
            variant="tonal"
            color="primary"
            rounded="pill"
            class="hidden-sm-and-down"
          >
            <v-btn value="grid" prepend-icon="mdi-view-grid">Lista</v-btn>
            <v-btn value="map" prepend-icon="mdi-map">Mapa</v-btn>
          </v-btn-toggle>
        </div>

        <v-row align="center">
          <v-col cols="12" md="8">
            <v-text-field
              v-model="searchQuery"
              :label="$t('menu.search_placeholder', 'Buscar pratos ou restaurantes...')"
              prepend-inner-icon="mdi-magnify"
              variant="solo"
              flat
              rounded="pill"
              class="search-bar"
              hide-details
              clearable
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="4" class="d-flex justify-end">
            <v-btn variant="text" prepend-icon="mdi-filter-variant">Filtros</v-btn>
          </v-col>
        </v-row>
      </header>

      <!-- View Content -->
      <div v-if="loading" class="text-center py-12">
        <v-progress-circular indeterminate color="primary" size="64" />
      </div>

      <div v-else-if="viewMode === 'map'" class="map-view">
        <CustomerDiscoveryMap />
      </div>

      <div v-else class="grid-view">
        <v-row v-if="filteredProducts.length > 0">
          <v-col 
            v-for="product in filteredProducts" 
            :key="product.id" 
            cols="12" sm="6" md="4" lg="3"
          >
            <ProductCard 
              :product="product"
              @product-clicked="handleProductClicked"
              @add-to-cart="addToCart"
              class="premium-card"
            />
          </v-col>
        </v-row>
        <v-empty-state
          v-else
          icon="mdi-food-off"
          title="Nada encontrado"
          text="Tente outro termo de busca."
        ></v-empty-state>
      </div>

      <ProductDetailsModal
        v-if="selectedProduct"
        :product="selectedProduct"
        v-model="showDetails"
        @add-to-cart="addToCart"
        @close="showDetails = false"
      />
    </div>
  </CustomerLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import CustomerLayout from '@/Layouts/CustomerLayout.vue'
import ProductCard from '@/Components/ProductCard.vue'
import ProductDetailsModal from '@/Components/ProductDetailsModal.vue'
import CustomerDiscoveryMap from '@/Components/CustomerDiscoveryMap.vue'
import { useCartStore } from '@/stores/cart'
import api from '@/services/api'

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
const viewMode = ref('grid')
const searchQuery = ref('')
const selectedProduct = ref(null)
const showDetails = ref(false)
const loading = ref(false)
const partnerData = ref(props.partner)
const productsData = ref(props.products)

const filteredProducts = computed(() => {
  if (!searchQuery.value) return productsData.value
  const q = searchQuery.value.toLowerCase()
  return productsData.value.filter(p => 
    p.name.toLowerCase().includes(q) || 
    (p.description && p.description.toLowerCase().includes(q)) ||
    (p.partner && p.partner.toLowerCase().includes(q))
  )
})

const handleProductClicked = (product) => {
  selectedProduct.value = product
  showDetails.value = true
}

const addToCart = (product) => {
  cartStore.addItem(product)
  showDetails.value = false
}

const fetchData = async () => {
  loading.value = true
  try {
    const response = await api.get('/products')
    productsData.value = response.data.products || []
  } catch (err) {
    console.error('Failed to load menu data:', err)
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  if (!props.products.length) {
    await fetchData()
  }
})
</script>

<style scoped>
.menu-container {
  max-width: 1200px;
  margin: 0 auto;
}

.search-bar {
  box-shadow: 0 4px 12px rgba(0,0,0,0.05) !important;
}

.premium-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 24px !important;
}

.premium-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.1) !important;
}
</style>
