<template>
  <v-card 
    @click="$emit('product-clicked', product)"
    data-test="product-card"
  >
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

    <v-card-actions>
      <v-btn 
        @click.stop="$emit('add-to-cart', product)"
        color="primary"
        data-test="add-to-cart-button"
      >
        Add to Cart
      </v-btn>
    </v-card-actions>
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

const emit = defineEmits(['product-clicked', 'add-to-cart'])

const truncateDescription = computed(() => {
  if (!props.product.description) return ''
  const desc = props.product.description
  return desc.length >= 100
    ? `${desc.substring(0, 97)}...`
    : desc
})
</script>
