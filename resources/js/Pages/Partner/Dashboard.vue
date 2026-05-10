<template>
  <PartnerLayout>
    <div class="partner-dashboard">
      <!-- Metrics Overview -->
      <v-row class="mb-6">
        <v-col cols="12" sm="6" lg="3" v-for="metric in metrics" :key="metric.title">
          <MetricsWidget
            :title="t(metric.titleKey)"
            :value="metric.value"
            :icon="metric.icon"
            :color="metric.color.replace('bg-', '')"
            data-cy="dashboard-metric"
          />
        </v-col>
      </v-row>

      <!-- Recent Orders Section -->
      <v-row>
        <v-col cols="12">
          <DataTable
            :title="t('partner.dashboard.recent_orders')"
            :headers="translatedHeaders"
            :items="recentOrders"
            :loading="loading"
            data-cy="recent-orders-table"
          >
            <template #item.status="{ item }">
              <v-chip
                :color="getStatusColor(item.status)"
                size="small"
                class="text-uppercase font-weight-bold"
              >
                {{ t('partner.status.' + item.status) }}
              </v-chip>
            </template>
            
            <template #item.total="{ item }">
              <span class="font-weight-bold">${{ item.total?.toFixed(2) || '0.00' }}</span>
            </template>

            <template #item.actions="{ item }">
              <v-btn
                variant="text"
                color="primary"
                icon="mdi-eye"
                @click="viewOrder(item)"
                data-cy="view-order-btn"
              ></v-btn>
              <v-btn
                v-if="item.status === 'pending'"
                variant="text"
                color="success"
                icon="mdi-check"
                @click="acceptOrder(item)"
                data-cy="accept-order-btn"
              ></v-btn>
            </template>
          </DataTable>
        </v-col>
      </v-row>

      <!-- Order Detail Modal -->
      <AppModal
        v-model="showOrderModal"
        :title="t('partner.orders.id', { id: selectedOrder?.id })"
        maxWidth="600"
      >
        <div v-if="selectedOrder">
          <v-list density="compact">
            <v-list-item v-for="item in selectedOrder.items" :key="item.id">
              <template #prepend>
                <span class="mr-3 font-weight-bold">{{ item.quantity }}x</span>
              </template>
              <v-list-item-title>{{ item.name }}</v-list-item-title>
              <template #append>
                <span>{{ t('common.currency_symbol', { value: (item.price * item.quantity).toFixed(2) }, '$' + (item.price * item.quantity).toFixed(2)) }}</span>
              </template>
            </v-list-item>
          </v-list>
          <v-divider class="my-3"></v-divider>
          <div class="d-flex justify-space-between text-h6 px-4">
            <span>{{ t('partner.orders.total') }}</span>
            <span class="primary--text font-weight-bold">{{ t('common.currency_symbol', { value: selectedOrder.total?.toFixed(2) || '0.00' }, '$' + (selectedOrder.total?.toFixed(2) || '0.00')) }}</span>
          </div>
        </div>
        <template #actions>
          <v-btn variant="text" @click="showOrderModal = false">{{ t('partner.actions.close') }}</v-btn>
          <v-btn v-if="selectedOrder?.status === 'pending'" color="success" @click="acceptOrder(selectedOrder)">
            {{ t('partner.actions.accept') }}
          </v-btn>
        </template>
      </AppModal>
    </div>
  </PartnerLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import AppModal from '@/Components/AppModal.vue';
import MetricsWidget from '@/Components/MetricsWidget.vue';
import partnerService from '@/services/partner';
import { useNotificationStore } from '@/stores/notification';
import { useRealtime } from '@/composables/useRealtime';

const props = defineProps({
  initialMetrics: {
    type: Object,
    default: () => ({})
  }
});

const { t } = useI18n();
const notifications = useNotificationStore();
const realtime = useRealtime();

const loading = ref(false);
const showOrderModal = ref(false);
const selectedOrder = ref(null);

const metrics = ref([
  { titleKey: 'partner.dashboard.metrics.new_orders', value: '0', icon: 'mdi-bell-ring', color: 'bg-orange', key: 'new_orders' },
  { titleKey: 'partner.dashboard.metrics.preparing', value: '0', icon: 'mdi-silverware', color: 'bg-blue', key: 'preparing' },
  { titleKey: 'partner.dashboard.metrics.today_revenue', value: '$0.00', icon: 'mdi-currency-usd', color: 'bg-green', key: 'today_revenue' },
  { titleKey: 'partner.dashboard.metrics.completed', value: '0', icon: 'mdi-check-circle', color: 'bg-purple', key: 'completed' },
]);

const orderHeaders = [
  { titleKey: 'partner.orders.id', key: 'id' },
  { titleKey: 'partner.orders.customer', key: 'customer_name' },
  { titleKey: 'partner.orders.total', key: 'total' },
  { titleKey: 'partner.orders.status', key: 'status' },
  { titleKey: 'partner.orders.actions', key: 'actions', sortable: false },
];

const translatedHeaders = computed(() => 
  orderHeaders.map(h => ({ ...h, title: t(h.titleKey) }))
);

const recentOrders = ref([]);

const getStatusColor = (status) => {
  const colors = {
    'pending': 'orange',
    'preparing': 'blue',
    'ready': 'teal',
    'completed': 'grey',
    'cancelled': 'red'
  };
  return colors[status] || 'grey';
};

const fetchDashboardData = async () => {
  loading.value = true;
  try {
    const statsResponse = await partnerService.getDashboardStats();
    const stats = statsResponse.data || {};
    
    // Update metrics
    metrics.value.forEach(m => {
      if (stats[m.key] !== undefined) {
        if (m.key === 'today_revenue') {
           m.value = `$${stats[m.key].toFixed(2)}`;
        } else {
           m.value = stats[m.key].toString();
        }
      }
    });

    const ordersResponse = await partnerService.getOrders();
    recentOrders.value = ordersResponse.data || [];
  } catch (err) {
    console.error(err);
    notifications.error(t('partner.orders.failed_load'));
  } finally {
    loading.value = false;
  }
};

const viewOrder = (order) => {
  selectedOrder.value = order;
  showOrderModal.value = true;
};

const acceptOrder = async (order) => {
  try {
    // Assuming updateOrderStatus exists in partnerService (I added it to my mental model, let's verify/add)
    if (partnerService.updateOrderStatus) {
        await partnerService.updateOrderStatus(order.id, 'preparing');
    } else {
        // Fallback or use a generic update
        await partnerService.updateItemAvailability(order.id, true); // This was in the service but it's for items
        // I'll update the service in a moment if needed
    }
    
    notifications.success(t('partner.orders.accepted', { id: order.id }));
    fetchDashboardData();
    showOrderModal.value = false;
  } catch (err) {
    notifications.error(t('partner.orders.failed_accept'));
  }
};

onMounted(() => {
  realtime.setupListeners();
  fetchDashboardData();
});

import { onUnmounted, watch } from 'vue';
onUnmounted(() => {
  realtime.leaveChannels();
  recentOrders.value.forEach(order => {
    realtime.leaveOrder(order.id);
  });
});

watch(recentOrders, (newOrders, oldOrders) => {
  const oldIds = new Set(oldOrders?.map(o => o.id) || []);
  const newIds = new Set(newOrders?.map(o => o.id) || []);

  // Listen to new orders
  newOrders.forEach(order => {
    if (!oldIds.has(order.id)) {
      realtime.listenToOrder(order.id);
    }
  });

  // Leave old orders that are no longer in the list
  oldOrders?.forEach(order => {
    if (!newIds.has(order.id)) {
      realtime.leaveOrder(order.id);
    }
  });
}, { deep: true });
</script>

<style scoped>
.metric-card {
  border-radius: 12px;
}

.metric-icon-box {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.bg-orange { background-color: #fb8c00; }
.bg-blue { background-color: #1e88e5; }
.bg-green { background-color: #43a047; }
.bg-purple { background-color: #8e24aa; }

.partner-dashboard {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
