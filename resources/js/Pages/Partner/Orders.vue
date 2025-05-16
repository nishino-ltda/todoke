<template>
  <v-container>
    <v-card>
      <v-card-title class="d-flex justify-space-between align-center">
        <span>Orders Management</span>
        <v-btn
          color="primary"
          prepend-icon="mdi-refresh"
          @click="refreshOrders"
        >
          Refresh
        </v-btn>
      </v-card-title>

      <v-card-text>
        <v-data-table
          :headers="headers"
          :items="orders"
          :loading="loading"
          data-test="orders-table"
        >
          <template v-slot:item.status="{ item }">
            <v-select
              v-model="item.status"
              :items="statusOptions"
              density="compact"
              variant="outlined"
              data-test="status-dropdown"
              @update:model-value="updateStatus(item)"
            ></v-select>
          </template>

          <template v-slot:item.actions="{ item }">
            <v-btn
              size="small"
              variant="text"
              color="primary"
              @click="viewOrder(item)"
              data-test="view-order-btn"
            >
              View
            </v-btn>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  orders: {
    type: Array,
    required: true,
    default: () => []
  }
})

const loading = ref(false)
const headers = ref([
  { title: 'Order #', key: 'id' },
  { title: 'Customer', key: 'customer_name' },
  { title: 'Total', key: 'total' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false }
])

const statusOptions = ref([
  'pending',
  'preparing',
  'ready',
  'delivered',
  'cancelled'
])

function refreshOrders() {
  loading.value = true
  router.reload({
    only: ['orders'],
    onFinish: () => (loading.value = false)
  })
}

function updateStatus(order) {
  router.patch(`/partner/orders/${order.id}/status`, {
    status: order.status
  })
}

function viewOrder(order) {
  router.visit(`/partner/orders/${order.id}`)
}
</script>
