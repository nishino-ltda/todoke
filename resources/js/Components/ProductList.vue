<template>
  <v-container data-cy="product-list-container" class="pa-0">
    <v-row v-if="showSearch" class="mb-4 px-4">
      <v-col cols="12">
        <v-text-field
          v-model="searchQuery"
          :label="$t('menu.search_placeholder')"
          prepend-inner-icon="mdi-magnify"
          clearable
          variant="solo"
          flat
          rounded="pill"
          hide-details
          data-cy="product-search"
          class="elevation-1"
        ></v-text-field>
      </v-col>
    </v-row>
    
    <v-row v-if="filteredProducts.length === 0" class="mt-4">
      <v-col cols="12" class="text-center">
        <v-icon icon="mdi-magnify-close" size="64" color="grey"></v-icon>
        <p class="mt-2 text-grey">{{ $t('menu.no_products') }}</p>
      </v-col>
    </v-row>
    
    <v-row data-cy="product-list-row" class="pa-2">
      <v-col 
        v-for="product in filteredProducts"
        :key="product.id"
        cols="12" sm="6" md="4" lg="3"
        data-cy="product-col"
      >
        <ProductCard 
          :product="product"
          @product-clicked="handleProductClicked"
          @add-to-cart="handleAddToCart"
          data-cy="product-card"
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
  },
  showSearch: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['product-clicked', 'add-to-cart'])
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

const handleAddToCart = (product) => {
  emit('add-to-cart', product)
}
</script>
