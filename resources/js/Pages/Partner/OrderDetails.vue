<template>
  <v-container>
    <v-card>
      <v-card-title>Order #{{ order.id }}</v-card-title>
      <v-card-subtitle>Status: {{ order.status }}</v-card-subtitle>

      <v-card-text>
        <v-row>
          <v-col cols="12" md="6">
            <h3 class="text-h6 mb-4">Customer Information</h3>
            <v-list>
              <v-list-item>
                <v-list-item-title>Name: {{ order.customer_name }}</v-list-item-title>
              </v-list-item>
              <v-list-item>
                <v-list-item-title>Phone: {{ order.customer_phone }}</v-list-item-title>
              </v-list-item>
              <v-list-item>
                <v-list-item-title>Address: {{ order.delivery_address }}</v-list-item-title>
              </v-list-item>
            </v-list>
          </v-col>

          <v-col cols="12" md="6">
            <h3 class="text-h6 mb-4">Order Summary</h3>
            <v-list>
              <v-list-item>
                <v-list-item-title>Subtotal: ${{ order.subtotal }}</v-list-item-title>
              </v-list-item>
              <v-list-item>
                <v-list-item-title>Delivery Fee: ${{ order.delivery_fee }}</v-list-item-title>
              </v-list-item>
              <v-list-item>
                <v-list-item-title>Total: ${{ order.total }}</v-list-item-title>
              </v-list-item>
              <v-list-item>
                <v-list-item-title>Payment Method: {{ order.payment_method }}</v-list-item-title>
              </v-list-item>
            </v-list>
          </v-col>
        </v-row>

        <v-divider class="my-4"></v-divider>

        <h3 class="text-h6 mb-4">Order Items</h3>
        <v-table data-test="order-items-table">
          <thead>
            <tr>
              <th>Item</th>
              <th>Quantity</th>
              <th>Price</th>
              <th>Addons</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in order.items" :key="item.id" data-test="order-item">
              <td>{{ item.product_name }}</td>
              <td>{{ item.quantity }}</td>
              <td>${{ item.price }}</td>
              <td>
                <v-chip
                  v-for="addon in item.addons"
                  :key="addon.id"
                  size="small"
                  class="mr-1 mb-1"
                >
                  {{ addon.name }} (+${{ addon.price }})
                </v-chip>
              </td>
            </tr>
          </tbody>
        </v-table>
      </v-card-text>

      <v-card-actions>
        <v-btn color="primary" @click="goBack">Back to Orders</v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
</template>

<script setup>
import { router } from '@inertiajs/vue3'

const props = defineProps({
  order: {
    type: Object,
    required: true
  }
})

function goBack() {
  router.visit('/partner/orders')
}
</script>
