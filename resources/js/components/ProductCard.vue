<template>
  <v-card>
    <v-img
      :src="product.image || '/images/placeholder.png'"
      height="200px"
      cover
      data-test="product-image"
    ></v-img>

    <v-card-title data-test="product-name">{{ product.name }}</v-card-title>
    <v-card-subtitle data-test="product-price">${{ product.price }}</v-card-subtitle>

    <v-card-text>
      <p data-test="product-description">{{ product.description }}</p>
    </v-card-text>

    <v-card-actions>
      <v-btn 
        color="primary"
        @click="$emit('add-to-cart', product)"
        data-test="add-to-cart-btn"
      >
        Add to Cart
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script setup>
const props = defineProps({
  product: {
    type: Object,
    required: true,
    default: () => ({})
  }
})
</script>
