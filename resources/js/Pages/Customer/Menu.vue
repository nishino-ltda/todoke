<template>
  <GuestLayout>
    <div data-cy="customer-menu" class="pa-6">
      <v-row v-if="loading" class="justify-center pa-6">
        <v-col cols="12" class="text-center">
          <v-progress-circular indeterminate color="primary" data-cy="menu-loading" />
          <p class="mt-4">{{ $t('menu.loading') }}</p>
        </v-col>
      </v-row>

      <v-row v-else-if="error" class="justify-center pa-6">
        <v-col cols="12" class="text-center">
          <v-icon color="error" size="64">mdi-alert-circle-outline</v-icon>
          <h2 class="text-h5 mt-2">{{ $t('menu.error_title') }}</h2>
          <p class="text-body-1 text-grey mt-1">{{ error }}</p>
          <v-btn color="primary" class="mt-4" @click="fetchPartner" data-cy="menu-retry">
            {{ $t('menu.retry') }}
          </v-btn>
        </v-col>
      </v-row>

      <template v-else-if="partner">
        <div class="d-flex align-center mb-4">
          <div>
            <h1 class="text-h4 mb-1" data-cy="partner-name">{{ partner.name }}</h1>
            <p class="text-subtitle-1 text-grey mb-0">{{ partner.type ? $t(`menu.partner_type`) : '' }}</p>
          </div>
        </div>

        <v-divider class="mb-4" />

        <ProductList
          :products="products"
          @product-clicked="handleProductClicked"
        />

        <ProductDetailsModal
          v-if="selectedProduct"
          :product="selectedProduct"
          v-model="showDetails"
          @add-to-cart="addToCart"
          @close="showDetails = false"
        />
      </template>

      <v-row v-else class="justify-center pa-6">
        <v-col cols="12" class="text-center">
          <v-icon color="grey" size="64">mdi-store-off-outline</v-icon>
          <h2 class="text-h5 mt-2">{{ $t('menu.not_found') }}</h2>
        </v-col>
      </v-row>
    </div>
  </GuestLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import ProductList from '@/Components/ProductList.vue'
import ProductDetailsModal from '@/Components/ProductDetailsModal.vue'
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
const selectedProduct = ref(null)
const showDetails = ref(false)
const loading = ref(false)
const error = ref(null)
const partnerData = ref(props.partner)
const productsData = ref(props.products)

const handleProductClicked = (product) => {
  selectedProduct.value = product
  showDetails.value = true
}

const addToCart = (product) => {
  cartStore.addItem(product)
  showDetails.value = false
}

const fetchPartner = async () => {
  loading.value = true
  error.value = null
  const slug = window.location.pathname.split('/').filter(Boolean).pop()
  try {
    const response = await api.get(`/partners/${slug}`)
    partnerData.value = response.data.partner
    productsData.value = response.data.products
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load partner data'
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  if (!props.partner && !props.products.length) {
    await fetchPartner()
  } else {
    partnerData.value = props.partner
    productsData.value = props.products
  }
})
</script>
