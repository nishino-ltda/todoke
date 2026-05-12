<template>
  <CourierLayout>
    <div class="deliveries-container" data-cy="courier-deliveries">
      <v-tabs v-model="activeTab" color="primary" class="mb-6">
        <v-tab value="active" class="text-none font-weight-bold">
          {{ t('courier.deliveries.active') }}
        </v-tab>
        <v-tab value="history" class="text-none font-weight-bold">
          {{ t('courier.deliveries.history') }}
        </v-tab>
      </v-tabs>

      <v-window v-model="activeTab">
        <!-- Active Deliveries -->
        <v-window-item value="active">
          <v-row v-if="activeDeliveries.length > 0">
            <v-col v-for="delivery in activeDeliveries" :key="delivery.id" cols="12" md="6">
              <v-card class="glass-card pa-4" link @click="viewDetails(delivery.id)">
                <div class="d-flex justify-space-between align-center mb-4">
                  <v-chip color="primary" size="small" class="text-uppercase font-weight-bold">
                    {{ t(`courier.status.${delivery.status}`, delivery.status) }}
                  </v-chip>
                  <span class="text-caption text-grey">#{{ delivery.id }}</span>
                </div>

                <div class="location-info mb-4">
                  <div class="d-flex align-center mb-2">
                    <v-icon size="16" color="primary" class="mr-2">mdi-circle-slice-8</v-icon>
                    <span class="text-body-2 font-weight-bold truncate">{{ delivery.origin?.address || delivery.origin_address }}</span>
                  </div>
                  <div class="d-flex align-center">
                    <v-icon size="16" color="error" class="mr-2">mdi-map-marker</v-icon>
                    <span class="text-body-2 font-weight-bold truncate">{{ delivery.destination?.address || delivery.destination_address }}</span>
                  </div>
                </div>

                <v-divider class="mb-4"></v-divider>

                <div class="d-flex justify-space-between align-center">
                  <div class="d-flex align-center">
                    <v-icon color="green" size="20" class="mr-1">mdi-currency-usd</v-icon>
                    <span class="text-h6 font-weight-black">{{ formatCurrency(delivery.value) }}</span>
                  </div>
                  <v-btn variant="tonal" color="primary" size="small" class="text-none">
                    {{ t('partner.orders.view') }}
                  </v-btn>
                </div>
              </v-card>
            </v-col>
          </v-row>
          <div v-else class="text-center py-12 opacity-50">
            <v-icon size="64" class="mb-4">mdi-package-variant</v-icon>
            <p>{{ t('courier.activeDelivery.noDelivery') }}</p>
          </div>
        </v-window-item>

        <!-- History -->
        <v-window-item value="history">
          <v-data-table
            :headers="headers"
            :items="historyDeliveries"
            class="glass-card"
            :loading="loading"
          >
            <template v-slot:item.value="{ item }">
              {{ formatCurrency(item.value) }}
            </template>
            <template v-slot:item.status="{ item }">
              <v-chip size="x-small" :color="getStatusColor(item.status)" class="text-uppercase">
                {{ t(`courier.status.${item.status}`, item.status) }}
              </v-chip>
            </template>
            <template v-slot:item.actions="{ item }">
              <v-btn icon="mdi-eye" variant="text" size="small" @click="viewDetails(item.id)"></v-btn>
            </template>
          </v-data-table>
        </v-window-item>
      </v-window>
    </div>
  </CourierLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { router } from '@inertiajs/vue3';
import CourierLayout from '@/Layouts/CourierLayout.vue';
import deliveryService from '@/services/delivery';

const { t, locale } = useI18n();
const activeTab = ref('active');
const loading = ref(false);
const allDeliveries = ref([]);

const activeDeliveries = computed(() => 
  allDeliveries.value.filter(d => ['accepted', 'arrived', 'collected', 'picked_up', 'in_transit'].includes(d.status))
);

const historyDeliveries = computed(() => 
  allDeliveries.value.filter(d => ['delivered', 'cancelled', 'failed'].includes(d.status))
);

const headers = [
  { title: 'ID', key: 'id' },
  { title: t('courier.activeDelivery.dropoff'), key: 'destination.address' },
  { title: t('courier.availableDeliveries.payout'), key: 'value' },
  { title: t('partner.orders.status'), key: 'status' },
  { title: '', key: 'actions', sortable: false },
];

const formatCurrency = (value) => {
  return new Intl.NumberFormat(locale.value, {
    style: 'currency',
    currency: 'BRL'
  }).format(value || 0);
};

const getStatusColor = (status) => {
  switch (status) {
    case 'delivered': return 'success';
    case 'cancelled': return 'error';
    case 'failed': return 'warning';
    default: return 'grey';
  }
};

const fetchDeliveries = async () => {
  loading.value = true;
  try {
    const res = await deliveryService.getAvailableDeliveries(); // Actually returns all associated with courier
    allDeliveries.value = res.data?.deliveries || res.data || [];
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const viewDetails = (id) => {
  router.visit(`/courier/deliveries/${id}`);
};

onMounted(fetchDeliveries);
</script>

<style scoped>
.glass-card {
  background: rgba(255, 255, 255, 0.7) !important;
  backdrop-filter: blur(12px);
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.truncate {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.deliveries-container {
  animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>
