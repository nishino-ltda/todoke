<template>
  <v-badge
    :content="cart.count"
    :model-value="cart.count > 0"
    color="error"
    overlap
    data-cy="cart-icon"
  >
    <v-btn icon @click="showCartDialog = true">
      <v-icon>mdi-cart</v-icon>
    </v-btn>
  </v-badge>
  
  <v-dialog v-model="showCartDialog" max-width="500px">
    <v-card>
      <v-card-title class="headline">{{ $t('cart.title') }}</v-card-title>
      
      <v-card-text v-if="cart.items.length === 0">
        {{ $t('cart.empty') }}
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
              data-cy="remove-item"
            >
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </v-list-item-action>
        </v-list-item>
      </v-list>
      
      <v-divider v-if="cart.items.length > 0"></v-divider>
      
      <v-card-text v-if="cart.items.length > 0" class="text-right">
        <strong>{{ $t('cart.total') }}: ${{ cart.total.toFixed(2) }}</strong>
      </v-card-text>
      
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn text @click="showCartDialog = false">{{ $t('cart.close') }}</v-btn>
        <v-btn 
          color="primary" 
          :disabled="cart.items.length === 0"
          @click="checkout"
          data-cy="checkout-button"
        >
          {{ $t('cart.checkout') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref } from 'vue'
import { useCartStore } from '@/stores/cart'
import { router } from '@inertiajs/vue3'

const cart = useCartStore()
const showCartDialog = ref(false)

function removeItem(id) {
  cart.removeItem(id)
}

function checkout() {
    showCartDialog.value = false
    router.visit('/customer/checkout')
}
</script>
