<template>
  <v-container>
    <v-card>
      <v-card-title class="d-flex justify-space-between align-center">
        <span>{{ t('partner.orders.title') }}</span>
        <v-btn
          color="primary"
          prepend-icon="mdi-refresh"
          @click="refreshOrders"
        >
          {{ t('partner.orders.refresh') }}
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
              :items="translatedStatusOptions"
              item-title="title"
              item-value="value"
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
              {{ t('partner.orders.view') }}
            </v-btn>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { router } from '@inertiajs/vue3'

const { t } = useI18n()

const props = defineProps({
  orders: {
    type: Array,
    required: true,
    default: () => []
  }
})

const loading = ref(false)
const headers = computed(() => [
  { title: t('partner.orders.id'), key: 'id' },
  { title: t('partner.orders.customer'), key: 'customer_name' },
  { title: t('partner.orders.total'), key: 'total' },
  { title: t('partner.orders.status'), key: 'status' },
  { title: t('partner.orders.actions'), key: 'actions', sortable: false }
])

const statusOptions = [
  'pending',
  'preparing',
  'ready',
  'delivered',
  'cancelled'
]

const translatedStatusOptions = computed(() => 
  statusOptions.map(opt => ({
    title: t('partner.status.' + opt),
    value: opt
  }))
)

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
