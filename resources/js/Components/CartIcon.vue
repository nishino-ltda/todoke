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
    <v-card theme="light" class="glass-card" elevation="2" rounded="xl">
      <v-card-title class="text-h5 font-weight-black pt-6 px-6">
        {{ $t('cart.title') }}
      </v-card-title>
      
      <v-card-text v-if="cart.items.length === 0" class="px-6 py-4 text-medium-emphasis">
        {{ $t('cart.empty') }}
      </v-card-text>
      
      <v-list v-else class="bg-transparent px-4 py-2">
        <v-list-item v-for="item in cart.items" :key="item.id" class="mb-2 rounded-lg">
          <template v-slot:prepend>
            <v-avatar rounded="lg" size="48" class="mr-3" color="grey-lighten-3">
              <v-img :src="resolveImageUrl(item.image)" cover></v-img>
            </v-avatar>
          </template>

          <v-list-item-title class="font-weight-medium">{{ item.name }}</v-list-item-title>
          <v-list-item-subtitle class="text-primary font-weight-bold">
            ${{ item.price }} <span class="text-medium-emphasis text-caption ml-1">x {{ item.quantity }}</span>
          </v-list-item-subtitle>
          
          <template v-slot:append>
            <v-btn 
              icon="mdi-delete"
              variant="text"
              color="error"
              size="small"
              @click="removeItem(item.id)"
              data-cy="remove-item"
            ></v-btn>
          </template>
        </v-list-item>
      </v-list>
      
      <v-divider v-if="cart.items.length > 0" class="mx-6"></v-divider>
      
      <v-card-text v-if="cart.items.length > 0" class="text-right px-6 py-4 text-h6 font-weight-black">
        {{ $t('cart.total') }}: <span class="text-primary">${{ cart.total.toFixed(2) }}</span>
      </v-card-text>
      
      <v-card-actions class="px-6 pb-6 pt-0">
        <v-spacer></v-spacer>
        <v-btn 
          variant="text" 
          class="text-none font-weight-medium"
          rounded="pill"
          @click="showCartDialog = false"
        >
          {{ $t('cart.close') }}
        </v-btn>
        <v-btn 
          color="primary" 
          variant="flat"
          class="text-none font-weight-bold px-6"
          rounded="pill"
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
import { useAuthStore } from '@/stores/auth'
import { router } from '@inertiajs/vue3'

const cart = useCartStore()
const authStore = useAuthStore()
const showCartDialog = ref(false)

const resolveImageUrl = (path) => {
  if (!path) return 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'
  if (path.startsWith('http')) return path
  return `/storage/${path}`
}

function removeItem(id) {
  cart.removeItem(id)
}

function checkout() {
    showCartDialog.value = false
    if (authStore.isAuthenticated) {
      router.visit('/customer/checkout')
    } else {
      router.visit('/login?redirect=' + encodeURIComponent('/customer/checkout'))
    }
}
</script>

<style scoped>
.glass-card {
  background: rgba(255, 255, 255, 0.8) !important;
  backdrop-filter: blur(12px);
  border: 1px solid rgba(var(--v-border-color), 0.1);
}

:deep(.v-badge__badge) {
  bottom: calc(100% - 24px) !important;
  left: calc(100% - 24px) !important;
}
</style>
