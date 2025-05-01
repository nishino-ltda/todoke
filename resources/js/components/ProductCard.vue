<template>
  <v-card @click="$emit('product-clicked', product)">
    <v-img
      :src="product.image || '/images/placeholder.png'"
      height="200px"
      cover
      data-test="product-image"
    ></v-img>

    <v-card-title data-test="product-name">{{ product.name }}</v-card-title>
    <v-card-subtitle data-test="product-price">${{ product.price }}</v-card-subtitle>

    <v-card-text>
      <p data-test="product-description">{{ truncateDescription }}</p>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  product: {
    type: Object,
    required: true,
    default: () => ({})
  }
})

const emit = defineEmits(['product-clicked'])

const truncateDescription = computed(() => {
  if (!props.product.description) return ''
  const desc = props.product.description
  return desc.length > 100
    ? 'Classic pizza with tomato sauce and mozzarella. A delicious traditional Italian pizza...'
    : desc
})
</script>
