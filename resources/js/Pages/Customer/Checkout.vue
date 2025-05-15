<template>
  <v-container>
    <h1>Checkout</h1>
    <v-row>
      <v-col cols="12" md="8">
        <v-card>
          <v-card-title>Order Details</v-card-title>
          
          <v-card-text>
            <v-form @submit.prevent="submitOrder" data-test="checkout-form">
              <v-text-field
                v-model="address"
                label="Delivery Address"
                required
                data-test="address"
              ></v-text-field>

              <div class="mb-4">
                <label>Payment Method</label>
                <v-select
                  v-model="paymentMethod"
                  :items="paymentMethods"
                  item-title="label"
                  item-value="value"
                  data-test="payment-method-input"
                ></v-select>
              </div>

              <div v-if="errorMessage" class="error-message text-error mt-4 mb-4">
                {{ errorMessage }}
                <ul v-if="errors.address || errors.paymentMethod">
                  <li v-for="(error, index) in errors.address" :key="'addr-'+index">{{ error }}</li>
                  <li v-for="(error, index) in errors.paymentMethod" :key="'pm-'+index">{{ error }}</li>
                </ul>
              </div>
              
              <v-btn 
                type="submit"
                color="primary"
                :disabled="!address || !paymentMethod || cart.items.length === 0"
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
                  
                  <div v-if="item.selectedAddons && item.selectedAddons.length > 0">
                    <div 
                      v-for="addon in item.selectedAddons" 
                      :key="addon.id"
                      class="ml-4 text-caption"
                      data-test="addon-item"
                    >
                      + {{ addon.name }} (${{ addon.price }})
                    </div>
                  </div>
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
  
  <!-- Success Dialog -->
  <v-dialog v-model="showSuccessDialog" max-width="400">
    <v-card>
      <v-card-title>Order Confirmed</v-card-title>
      <v-card-text>Your order has been submitted successfully!</v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="primary" @click="showSuccessDialog = false">OK</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref } from 'vue'
import { useCartStore } from '@/stores/cart'
import { router } from '@inertiajs/vue3'

const cart = useCartStore()
const address = ref('')
const paymentMethod = ref('')
const errorMessage = ref('')
const errors = ref({ address: [], paymentMethod: [] })
const showSuccessDialog = ref(false)
const paymentMethods = [
  { label: 'Credit Card', value: 'credit_card' },
  { label: 'Cash on Delivery', value: 'cash' },
  { label: 'PayPal', value: 'paypal' }
]

async function submitOrder() {
  // Reset errors
  errorMessage.value = ''
  errors.value = { address: [], paymentMethod: [] }
  
  try {
    const response = await cart.submitOrder({
      address: address.value,
      paymentMethod: paymentMethod.value,
      items: cart.items
    })
    
    if (response.success) {
      // Show success dialog
      showSuccessDialog.value = true
      
      // Clear cart
      cart.clearCart()
      
      // Redirect after dialog is closed
      setTimeout(() => {
        router.visit('/')
      }, 2000)
    }
  } catch (error) {
    errorMessage.value = 'Error submitting order'
    
    // Handle validation errors
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    }
    
    console.error('Order submission failed:', error)
  }
}
</script>
