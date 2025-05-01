<template>
  <v-container data-test="product-list-container">
    <v-row data-test="product-list-row">
      <v-col 
        v-for="product in products"
        :key="product.id"
        cols="12" sm="6" md="4" lg="3"
        data-test="product-col"
      >
        <ProductCard 
          :product="product" 
          @add-to-cart="addToCart"
          data-test="product-card"
        />
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref } from 'vue'
import ProductCard from './ProductCard.vue'

const props = defineProps({
  products: {
    type: Array,
    required: true,
    default: () => []
  }
})

const emit = defineEmits(['add-to-cart'])

const addToCart = (product) => {
  emit('add-to-cart', product)
}
</script>
