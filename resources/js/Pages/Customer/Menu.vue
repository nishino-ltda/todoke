<template>
  <AuthenticatedLayout>
    <v-container>
      <v-progress-circular
        v-if="loading"
        indeterminate
        color="primary"
      ></v-progress-circular>
      
      <template v-else>
        <h1 class="text-h4 mb-4">{{ restaurantName }}</h1>
        <ProductList 
          :products="products" 
          @product-clicked="showProductDetails"
        />
        
        <ProductDetailsModal
          v-if="selectedProduct"
          :product="selectedProduct"
          @close="selectedProduct = null"
          @add-to-cart="addToCart"
        />
      </template>
    </v-container>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ProductList from '@/components/ProductList.vue'
import ProductDetailsModal from '@/components/ProductDetailsModal.vue'
import { useCartStore } from '@/stores/cart'

const props = defineProps({
  slug: {
    type: String,
    required: true
  }
})

const cart = useCartStore()
const loading = ref(true)
const products = ref([])
const restaurantName = ref('')
const selectedProduct = ref(null)

const showProductDetails = (product) => {
  selectedProduct.value = product
}

const addToCart = (productWithAddons) => {
  const product = productWithAddons || selectedProduct.value
  cart.addItem({
    ...product,
    selectedAddons: product.selectedAddons || []
  })
  selectedProduct.value = null
}

onMounted(async () => {
  try {
    const response = await axios.get(`/api/v1/restaurants/${props.slug}`)
    restaurantName.value = response.data.name
    products.value = response.data.products
  } catch (error) {
    if (error.response?.status === 404) {
      window.location.href = '/?error=restaurant_not_found'
    } else {
      console.error('Error fetching restaurant data:', error)
      window.location.href = '/?error=server_error'
    }
  } finally {
    loading.value = false
  }
})
</script>
