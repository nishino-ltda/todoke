<template>
  <v-container data-test="product-list-container">
    <v-row class="mb-4">
      <v-col cols="12">
        <v-text-field
          v-model="searchQuery"
          :label="$t('menu.search_placeholder')"
          prepend-inner-icon="mdi-magnify"
          clearable
          outlined
          dense
          hide-details
          data-test="product-search"
        ></v-text-field>
      </v-col>
    </v-row>
    
    <v-row v-if="filteredProducts.length === 0" class="mt-4">
      <v-col cols="12" class="text-center">
        <p>{{ $t('menu.no_products') }}</p>
      </v-col>
    </v-row>
    
    <v-row data-test="product-list-row">
      <v-col 
        v-for="product in filteredProducts"
        :key="product.id"
        cols="12" sm="6" md="4" lg="3"
        data-test="product-col"
      >
        <ProductCard 
          :product="product"
          @product-clicked="handleProductClicked"
          data-test="product-card"
        />
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, computed } from 'vue'
import ProductCard from './ProductCard.vue'

const props = defineProps({
  products: {
    type: Array,
    required: true,
    default: () => []
  }
})

const emit = defineEmits(['product-clicked'])
const searchQuery = ref('')

const filteredProducts = computed(() => {
  if (!searchQuery.value) return props.products
  
  const query = searchQuery.value.toLowerCase()
  return props.products.filter(product => 
    product.name.toLowerCase().includes(query) || 
    (product.description && product.description.toLowerCase().includes(query))
  )
})

const handleProductClicked = (product) => {
  emit('product-clicked', product)
}
</script>
