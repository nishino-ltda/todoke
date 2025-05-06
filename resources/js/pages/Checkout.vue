<template>
  <v-container>
    <v-row>
      <v-col cols="12" md="8">
        <v-card>
          <v-card-title>Checkout</v-card-title>
          
          <v-card-text>
            <v-form @submit.prevent="submitOrder" data-test="checkout-form">
              <v-text-field
                v-model="address"
                label="Delivery Address"
                required
                data-test="address"
              ></v-text-field>

              <v-btn 
                type="submit"
                color="primary"
                :disabled="!address || cart.items.length === 0"
                data-test="submit-order"
              >
                Place Order
              </v-btn>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card>
          <v-card-title>Order Summary</v-card-title>
          
          <v-card-text>
            <v-list>
              <v-list-item 
                v-for="item in cart.items"
                :key="item.id"
                data-test="order-item"
              >
                <v-list-item-content>
                  <v-list-item-title>{{ item.name }}</v-list-item-title>
                  <v-list-item-subtitle>
                    ${{ item.price }} x {{ item.quantity }}
                  </v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
            </v-list>

            <v-divider class="my-4"></v-divider>

            <div class="text-right">
              <strong>Total: ${{ cart.total.toFixed(2) }}</strong>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref } from 'vue'
import { useCartStore } from '@/stores/cart'
import { useRouter } from 'vue-router'

const router = useRouter()
const cart = useCartStore()
const address = ref('')

async function submitOrder() {
  try {
    const response = await cart.submitOrder({
      address: address.value,
      items: cart.items
    })
    
    if (response.success) {
      router.push({ name: 'home' })
    }
  } catch (error) {
    console.error('Order submission failed:', error)
  }
}
</script>
