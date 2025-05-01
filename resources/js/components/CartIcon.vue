<template>
  <v-badge
    :content="cart.count"
    color="primary"
    overlap
  >
    <v-btn icon>
      <v-icon>mdi-cart</v-icon>
    </v-btn>
  </v-badge>
</template>

<script setup>
import { useCartStore } from '@/stores/cart'
const cart = useCartStore()
</script>
