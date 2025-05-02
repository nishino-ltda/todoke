<template>
  <v-badge
    :content="cart.count"
    :value="cart.count > 0"
    color="error"
    overlap
    data-test="cart-icon"
  >
    <v-btn icon @click="showCartDialog = true">
      <v-icon>mdi-cart</v-icon>
    </v-btn>
  </v-badge>
  
  <v-dialog v-model="showCartDialog" max-width="500px">
    <v-card>
      <v-card-title class="headline">Your Cart</v-card-title>
      
      <v-card-text v-if="cart.items.length === 0">
        Your cart is empty.
      </v-card-text>
      
      <v-list v-else>
        <v-list-item v-for="item in cart.items" :key="item.id">
          <v-list-item-content>
            <v-list-item-title>{{ item.name }}</v-list-item-title>
            <v-list-item-subtitle>
              ${{ item.price }} x {{ item.quantity }}
            </v-list-item-subtitle>
          </v-list-item-content>
          
          <v-list-item-action>
            <v-btn 
              icon 
              @click="removeItem(item.id)"
              data-test="remove-item"
            >
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </v-list-item-action>
        </v-list-item>
      </v-list>
      
      <v-divider v-if="cart.items.length > 0"></v-divider>
      
      <v-card-text v-if="cart.items.length > 0" class="text-right">
        <strong>Total: ${{ cart.total.toFixed(2) }}</strong>
      </v-card-text>
      
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn text @click="showCartDialog = false">Close</v-btn>
        <v-btn 
          color="primary" 
          :disabled="cart.items.length === 0"
          @click="checkout"
        >
          Checkout
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref } from 'vue'
import { useCartStore } from '@/stores/cart'
import { useRouter } from 'vue-router'

const router = useRouter()
const cart = useCartStore()
const showCartDialog = ref(false)

function removeItem(id) {
  cart.removeItem(id)
}

function checkout() {
  showCartDialog.value = false
  // Navigate to checkout page (to be implemented)
  // router.push('/checkout')
  
  // For now, just clear the cart
  if (confirm('Checkout functionality will be implemented in the next sprint. Clear cart for now?')) {
    cart.clearCart()
  }
}
</script>
