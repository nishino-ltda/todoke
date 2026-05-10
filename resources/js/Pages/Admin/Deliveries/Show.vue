<template>
  <AdminLayout>
    <div class="delivery-detail-page" data-cy="delivery-detail-page">
      <div class="d-flex align-center mb-6">
        <v-btn
          icon="mdi-arrow-left"
          variant="text"
          @click="router.visit('/admin/deliveries')"
          class="mr-4"
          data-cy="back-btn"
        ></v-btn>
        <h1 class="text-h4 font-weight-bold">
          {{ t('admin.deliveries.detail.title') }} #{{ deliveryId }}
        </h1>
        <v-spacer />
        <v-chip
          v-if="delivery"
          :color="getStatusColor(delivery.status)"
          size="large"
          class="text-uppercase font-weight-bold"
          data-cy="status-chip"
        >
          {{ t(`courier.status.${delivery.status}`, delivery.status) }}
        </v-chip>
      </div>

      <v-row v-if="loading" class="justify-center py-12">
        <v-progress-circular indeterminate color="primary" size="64" />
      </v-row>

      <template v-else-if="delivery">
        <v-row>
          <!-- Main Info -->
          <v-col cols="12" lg="8">
            <v-card border elevation="0" class="rounded-xl mb-6 pa-6">
              <v-row>
                <v-col cols="12" md="4">
                  <div class="text-overline text-grey">{{ t('admin.deliveries.table.customer') }}</div>
                  <div class="d-flex align-center mt-1">
                    <v-avatar size="40" color="blue-lighten-5" class="mr-3">
                      <v-icon color="blue">mdi-account</v-icon>
                    </v-avatar>
                    <div>
                      <div class="text-subtitle-1 font-weight-bold">{{ delivery.customer?.name }}</div>
                      <div class="text-caption text-grey">{{ delivery.customer?.email }}</div>
                    </div>
                  </div>
                </v-col>
                <v-col cols="12" md="4">
                  <div class="text-overline text-grey">{{ t('admin.deliveries.table.courier') }}</div>
                  <div v-if="delivery.courier" class="d-flex align-center mt-1">
                    <v-avatar size="40" color="green-lighten-5" class="mr-3">
                      <v-icon color="green">mdi-bike</v-icon>
                    </v-avatar>
                    <div>
                      <div class="text-subtitle-1 font-weight-bold">{{ delivery.courier?.name }}</div>
                      <div class="text-caption text-grey">{{ delivery.courier?.phone }}</div>
                    </div>
                  </div>
                  <div v-else class="mt-2 text-grey">
                    {{ t('admin.deliveries.detail.not_assigned') }}
                  </div>
                </v-col>
                <v-col cols="12" md="4">
                  <div class="text-overline text-grey">{{ t('admin.deliveries.table.value') }}</div>
                  <div class="text-h4 font-weight-bold text-primary mt-1">
                    {{ formatCurrency(delivery.value) }}
                  </div>
                </v-col>
              </v-row>
            </v-card>

            <!-- Map -->
            <v-card border elevation="0" class="rounded-xl mb-6 overflow-hidden">
              <v-card-title class="px-6 py-4">
                {{ t('admin.deliveries.detail.route_map') }}
              </v-card-title>
              <div class="map-container">
                <DeliveryMap
                  v-if="delivery.origin_lat"
                  :origin="{ lat: delivery.origin_lat, lng: delivery.origin_lng }"
                  :destination="{ lat: delivery.destination_lat, lng: delivery.destination_lng }"
                  data-cy="delivery-map"
                />
                <div v-else class="pa-12 text-center text-grey">
                  {{ t('courier.map.error') }}
                </div>
              </div>
              <v-card-text class="bg-grey-lighten-4 pa-4">
                <v-row>
                  <v-col cols="12" md="6">
                    <div class="d-flex align-center">
                      <v-icon color="primary" class="mr-2">mdi-circle-slice-8</v-icon>
                      <div>
                        <div class="text-caption text-grey">{{ t('courier.activeDelivery.pickup') }}</div>
                        <div class="text-body-2">{{ delivery.origin_address }}</div>
                      </div>
                    </div>
                  </v-col>
                  <v-col cols="12" md="6">
                    <div class="d-flex align-center">
                      <v-icon color="error" class="mr-2">mdi-map-marker</v-icon>
                      <div>
                        <div class="text-caption text-grey">{{ t('courier.activeDelivery.dropoff') }}</div>
                        <div class="text-body-2">{{ delivery.destination_address }}</div>
                      </div>
                    </div>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-col>

          <!-- Sidebar: History + Actions -->
          <v-col cols="12" lg="4">
            <v-card border elevation="0" class="rounded-xl mb-6">
              <v-card-title class="px-6 py-4">
                {{ t('admin.deliveries.detail.status_history') }}
              </v-card-title>
              <v-card-text>
                <v-timeline density="compact" side="end">
                  <v-timeline-item
                    v-for="(entry, i) in delivery.status_history"
                    :key="i"
                    :dot-color="getStatusColor(entry.status)"
                    size="x-small"
                  >
                    <div class="d-flex flex-column">
                      <div class="d-flex align-center justify-space-between">
                        <v-chip :color="getStatusColor(entry.status)" size="x-small" class="font-weight-bold">
                          {{ t(`courier.status.${entry.status}`, entry.status) }}
                        </v-chip>
                        <span class="text-caption text-grey">
                          {{ formatTime(entry.created_at) }}
                        </span>
                      </div>
                      <div class="text-caption mt-1">
                        {{ formatDate(entry.created_at) }}
                      </div>
                    </div>
                  </v-timeline-item>
                </v-timeline>
                <div v-if="!delivery.status_history?.length" class="text-center py-4 text-grey">
                  No history available
                </div>
              </v-card-text>
            </v-card>

            <!-- Order Items -->
            <v-card v-if="delivery.order?.items?.length" border elevation="0" class="rounded-xl">
              <v-card-title class="px-6 py-4">
                {{ t('partner.orders.items') }}
              </v-card-title>
              <v-list density="compact">
                <v-list-item
                  v-for="item in delivery.order.items"
                  :key="item.id"
                  :title="item.product?.name || item.name"
                  :subtitle="`${item.quantity}x ${formatCurrency(item.price)}`"
                >
                  <template #append>
                    <span class="font-weight-bold">
                      {{ formatCurrency(item.price * item.quantity) }}
                    </span>
                  </template>
                </v-list-item>
              </v-list>
            </v-card>
          </v-col>
        </v-row>
      </template>

      <v-row v-else class="justify-center py-12">
        <v-col cols="12" md="6" class="text-center">
          <v-icon size="64" color="grey-lighten-2">mdi-alert-circle-outline</v-icon>
          <h2 class="text-h5 text-grey mt-4">Delivery Not Found</h2>
          <v-btn color="primary" class="mt-6" @click="router.visit('/admin/deliveries')">
            Back to List
          </v-btn>
        </v-col>
      </v-row>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DeliveryMap from '@/Components/DeliveryMap.vue';
import adminService from '@/services/admin';
import { useNotificationStore } from '@/stores/notification';

const props = defineProps({
  deliveryId: {
    type: [String, Number],
    required: true
  }
});

const { t, locale } = useI18n();
const notifications = useNotificationStore();
const loading = ref(true);
const delivery = ref(null);

const fetchDelivery = async () => {
  loading.value = true;
  try {
    const response = await adminService.getDelivery(props.deliveryId);
    delivery.value = response.data?.delivery || response.data;
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
  return new Date(dateStr).toLocaleDateString(locale.value || 'pt-BR');
};

const formatTime = (dateStr) => {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleTimeString(locale.value || 'pt-BR', { hour: '2-digit', minute: '2-digit' });
};

onMounted(fetchDelivery);
</script>

<style scoped>
.delivery-detail-page {
  animation: fadeIn 0.4s ease-out;
}

.map-container {
  height: 400px;
  width: 100%;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
