<template>
  <AdminLayout>
    <div class="deliveries-monitoring" data-cy="deliveries-monitoring">
      <div class="d-flex align-center justify-space-between mb-6">
        <h1 class="text-h4 font-weight-bold">{{ t('admin.deliveries.title') }}</h1>
        <v-btn
          color="secondary"
          variant="outlined"
          prepend-icon="mdi-refresh"
          @click="fetchDeliveries"
          :loading="loading"
          data-cy="refresh-deliveries-btn"
        >
          {{ t('partner.orders.refresh') }}
        </v-btn>
      </div>

      <!-- Metric Cards -->
      <v-row class="mb-6">
        <v-col cols="12" md="3">
          <v-card class="pa-4" elevation="1" data-cy="metric-active">
            <div class="text-overline text-grey">{{ t('admin.dashboard.metrics.activeDeliveries') }}</div>
            <div class="text-h4 font-weight-bold text-primary">{{ activeCount }}</div>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card class="pa-4" elevation="1" data-cy="metric-pending">
            <div class="text-overline text-grey">{{ t('admin.deliveries.pending') }}</div>
            <div class="text-h4 font-weight-bold text-warning">{{ pendingCount }}</div>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card class="pa-4" elevation="1" data-cy="metric-in-transit">
            <div class="text-overline text-grey">{{ t('admin.deliveries.in_transit') }}</div>
            <div class="text-h4 font-weight-bold text-info">{{ inTransitCount }}</div>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card class="pa-4" elevation="1" data-cy="metric-delivered-today">
            <div class="text-overline text-grey">{{ t('admin.deliveries.delivered_today') }}</div>
            <div class="text-h4 font-weight-bold text-success">{{ deliveredTodayCount }}</div>
          </v-card>
        </v-col>
      </v-row>

      <DataTable
        :headers="headers"
        :items="deliveries"
        :loading="loading"
        data-cy="deliveries-table"
      >
        <template #item.status="{ item }">
          <v-chip
            :color="getStatusColor(item.status)"
            size="small"
            class="text-uppercase font-weight-bold"
            data-cy="delivery-status-chip"
          >
            {{ t(`courier.status.${item.status}`, item.status) }}
          </v-chip>
        </template>

        <template #item.customer="{ item }">
          <div class="d-flex align-center">
            <v-avatar size="24" color="grey-lighten-3" class="mr-2">
              <v-icon size="16">mdi-account</v-icon>
            </v-avatar>
            <span>{{ item.customer?.name || t('admin.deliveries.detail.not_assigned') }}</span>
          </div>
        </template>

        <template #item.courier="{ item }">
          <div v-if="item.courier" class="d-flex align-center">
            <v-avatar size="24" color="grey-lighten-3" class="mr-2">
              <v-icon size="16">mdi-bike</v-icon>
            </v-avatar>
            <span>{{ item.courier?.name }}</span>
          </div>
          <span v-else class="text-grey text-caption">
            {{ t('admin.deliveries.detail.not_assigned') }}
          </span>
        </template>

        <template #item.value="{ item }">
          <span class="font-weight-bold text-primary">
            {{ formatCurrency(item.value) }}
          </span>
        </template>

        <template #item.actions="{ item }">
          <v-btn
            variant="text"
            color="primary"
            icon="mdi-eye"
            @click="viewDelivery(item)"
            data-cy="view-delivery-btn"
          ></v-btn>
        </template>
      </DataTable>

      <!-- Delivery Detail Modal -->
      <AppModal
        v-model="showDetailModal"
        :title="t('admin.deliveries.detail.title')"
        maxWidth="700"
        data-cy="delivery-detail-modal"
      >
        <div v-if="selectedDelivery">
          <!-- Header: status + ID -->
          <div class="d-flex align-center justify-space-between mb-4">
            <v-chip
              :color="getStatusColor(selectedDelivery.status)"
              class="text-uppercase font-weight-bold"
            >
              {{ t(`courier.status.${selectedDelivery.status}`, selectedDelivery.status) }}
            </v-chip>
            <span class="text-caption text-grey">
              {{ t('courier.activeDelivery.id') }} #{{ selectedDelivery.id }}
            </span>
          </div>

          <v-row>
            <!-- People info -->
            <v-col cols="12" md="6">
              <v-card variant="outlined" class="pa-3 mb-3">
                <div class="text-overline text-grey mb-1">{{ t('admin.deliveries.detail.customer') }}</div>
                <div class="d-flex align-center">
                  <v-icon class="mr-2" color="blue">mdi-account</v-icon>
                  <span>{{ selectedDelivery.customer?.name || t('admin.deliveries.detail.not_assigned') }}</span>
                </div>
              </v-card>
            </v-col>
            <v-col cols="12" md="6">
              <v-card variant="outlined" class="pa-3 mb-3">
                <div class="text-overline text-grey mb-1">{{ t('admin.deliveries.detail.courier') }}</div>
                <div class="d-flex align-center">
                  <v-icon class="mr-2" color="green">mdi-bike</v-icon>
                  <span>{{ selectedDelivery.courier?.name || t('admin.deliveries.detail.not_assigned') }}</span>
                </div>
              </v-card>
            </v-col>
          </v-row>

          <!-- Addresses -->
          <div class="location-timeline mb-4">
            <div class="location-item mb-2">
              <v-icon size="16" color="primary" class="mr-3">mdi-circle-slice-8</v-icon>
              <div>
                <div class="text-caption text-grey">{{ t('courier.activeDelivery.pickup') }}</div>
                <div class="text-body-2 font-weight-bold" data-cy="detail-origin">
                  {{ selectedDelivery.origin_address || '—' }}
                </div>
              </div>
            </div>
            <div class="location-item">
              <v-icon size="16" color="error" class="mr-3">mdi-map-marker</v-icon>
              <div>
                <div class="text-caption text-grey">{{ t('courier.activeDelivery.dropoff') }}</div>
                <div class="text-body-2 font-weight-bold" data-cy="detail-destination">
                  {{ selectedDelivery.destination_address || '—' }}
                </div>
              </div>
            </div>
          </div>

          <!-- Route Map (only if lat/lng available) -->
          <div
            v-if="selectedDelivery.origin_lat && selectedDelivery.destination_lat"
            class="mb-4"
          >
            <div class="text-overline text-grey mb-2">{{ t('admin.deliveries.detail.route_map') }}</div>
            <DeliveryMap
              :origin="{ lat: selectedDelivery.origin_lat, lng: selectedDelivery.origin_lng }"
              :destination="{ lat: selectedDelivery.destination_lat, lng: selectedDelivery.destination_lng }"
              data-cy="detail-map"
            />
          </div>

          <!-- Status History -->
          <div v-if="selectedDelivery.status_history?.length" class="mb-2">
            <div class="text-overline text-grey mb-2">{{ t('admin.deliveries.detail.status_history') }}</div>
            <v-timeline density="compact" side="end">
              <v-timeline-item
                v-for="(entry, i) in selectedDelivery.status_history"
                :key="i"
                :dot-color="getStatusColor(entry.status)"
                size="x-small"
              >
                <div class="d-flex align-center justify-space-between">
                  <v-chip :color="getStatusColor(entry.status)" size="x-small">
                    {{ t(`courier.status.${entry.status}`, entry.status) }}
                  </v-chip>
                  <span class="text-caption text-grey ml-2">
                    {{ formatDate(entry.created_at) }}
                  </span>
                </div>
              </v-timeline-item>
            </v-timeline>
          </div>
        </div>
        <template #actions>
          <v-btn variant="text" @click="showDetailModal = false">
            {{ t('partner.actions.close') }}
          </v-btn>
        </template>
      </AppModal>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import AppModal from '@/Components/AppModal.vue';
import DeliveryMap from '@/Components/DeliveryMap.vue';
import adminService from '@/services/admin';
import { useNotificationStore } from '@/stores/notification';

const { t, locale } = useI18n();
const notifications = useNotificationStore();
const loading = ref(false);
const deliveries = ref([]);
const showDetailModal = ref(false);
const selectedDelivery = ref(null);

const headers = computed(() => [
  { title: t('admin.deliveries.table.id'), key: 'id', width: '80px' },
  { title: t('admin.deliveries.table.customer'), key: 'customer' },
  { title: t('admin.deliveries.table.courier'), key: 'courier' },
  { title: t('admin.deliveries.table.status'), key: 'status' },
  { title: t('admin.deliveries.table.type'), key: 'type' },
  { title: t('admin.deliveries.table.value'), key: 'value', align: 'end' },
  { title: t('admin.deliveries.table.actions'), key: 'actions', sortable: false, align: 'end' },
]);

const activeCount = computed(() =>
  deliveries.value.filter(d => !['delivered', 'canceled', 'failed'].includes(d.status)).length
);
const pendingCount = computed(() =>
  deliveries.value.filter(d => d.status === 'pending').length
);
const inTransitCount = computed(() =>
  deliveries.value.filter(d => d.status === 'in_transit' || d.status === 'collected').length
);
const deliveredTodayCount = computed(() => {
  const today = new Date().toISOString().split('T')[0];
  return deliveries.value.filter(
    d => d.status === 'delivered' && d.updated_at?.startsWith(today)
  ).length;
});

const fetchDeliveries = async () => {
  loading.value = true;
  try {
    const response = await adminService.getDeliveries();
    deliveries.value = response.data?.deliveries || response.data || [];
  } catch (err) {
    notifications.error(t('admin.deliveries.notifications.load_failed'));
  } finally {
    loading.value = false;
  }
};

const getStatusColor = (status) => {
  const colors = {
    pending: 'warning',
    accepted: 'info',
    collected: 'info',
    in_transit: 'primary',
    delivered: 'success',
    canceled: 'error',
    failed: 'error',
    drone_launched: 'deep-purple',
    drone_in_route: 'deep-purple-lighten-1',
    drone_arrived: 'deep-purple-darken-1'
  };
  return colors[status] || 'grey';
};

const formatCurrency = (value) =>
  new Intl.NumberFormat(locale.value || 'pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0);

const formatDate = (dateStr) => {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleString(locale.value || 'pt-BR');
};

const viewDelivery = (delivery) => {
  selectedDelivery.value = delivery;
  showDetailModal.value = true;
};

onMounted(fetchDeliveries);
</script>

<style scoped>
.deliveries-monitoring {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.location-item {
  display: flex;
  align-items: flex-start;
}
</style>
