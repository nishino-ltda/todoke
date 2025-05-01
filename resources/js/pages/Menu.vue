<template>
  <AuthenticatedLayout>
    <v-container>
      <ProductList 
        :products="products" 
        @add-to-cart="addToCart"
      />
    </v-container>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import ProductList from '@/components/ProductList.vue'
import { useCartStore } from '@/stores/cart'

const cart = useCartStore()
const products = ref([
  {
    id: 1,
    name: 'Margherita Pizza',
    description: 'Classic pizza with tomato sauce and mozzarella',
    price: 12.99,
    image: '/images/pizza-margherita.jpg'
  },
  {
    id: 2,
    name: 'Pepperoni Pizza',
    description: 'Pizza with tomato sauce, mozzarella and pepperoni',
    price: 14.99,
    image: '/images/pizza-pepperoni.jpg'
  }
])

const addToCart = (product) => {
  cart.addItem(product)
}
</script>
