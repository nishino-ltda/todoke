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
          @add-to-cart="addToCart"
        />
      </template>
    </v-container>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import ProductList from '@/components/ProductList.vue'
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

const addToCart = (product) => {
  cart.addItem(product)
}

onMounted(async () => {
  try {
    const response = await axios.get(`/api/v1/restaurants/${props.slug}`)
    restaurantName.value = response.data.name
    products.value = response.data.products
  } catch (error) {
    console.error('Error fetching restaurant data:', error)
    // Handle error (e.g., redirect to 404)
  } finally {
    loading.value = false
  }
})
</script>
