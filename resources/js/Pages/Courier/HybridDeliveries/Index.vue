<template>
  <CourierLayout>
    <div class="hybrid-deliveries-container" data-cy="hybrid-deliveries-index">
      <div class="d-flex justify-space-between align-center mb-6">
        <h2 class="text-h5 font-weight-black">{{ t('courier.hybrid_deliveries.index_title') }}</h2>
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          class="text-none font-weight-bold"
          rounded="lg"
          @click="router.visit('/courier/hybrid-deliveries/create')"
        >
          New Hybrid Request
        </v-btn>
      </div>

      <v-row v-if="deliveries.length > 0">
        <v-col v-for="delivery in deliveries" :key="delivery.id" cols="12" md="6">
          <v-card class="glass-card pa-4" @click="viewDetails(delivery.id)">
            <div class="d-flex justify-space-between align-center mb-4">
              <v-chip color="secondary" variant="flat" size="small" class="font-weight-bold">
                HYBRID #{{ delivery.id }}
              </v-chip>
              <v-chip size="x-small" :color="getStatusColor(delivery.status)">
                {{ t(`courier.status.${delivery.status}`, delivery.status) }}
              </v-chip>
            </div>

            <div class="stages-indicator mb-4">
              <div class="d-flex align-center ga-2">
                <v-icon color="primary" size="20">mdi-store</v-icon>
                <div class="stage-line flex-grow-1"></div>
                <v-icon :color="delivery.status === 'delivered' ? 'success' : 'grey'" size="20">mdi-warehouse</v-icon>
                <div class="stage-line flex-grow-1"></div>
                <v-icon :color="delivery.status === 'delivered' ? 'success' : 'grey'" size="20">mdi-map-marker-radius</v-icon>
              </div>
            </div>

            <div class="location-info mb-4">
              <div class="text-body-2 mb-1 truncate">
                <v-icon size="14" color="grey">mdi-circle-outline</v-icon>
                {{ delivery.origin?.address }}
              </div>
              <div class="text-body-2 truncate">
                <v-icon size="14" color="grey">mdi-map-marker-outline</v-icon>
                {{ delivery.destination?.address }}
              </div>
            </div>

            <div class="d-flex justify-space-between align-center">
              <span class="text-h6 font-weight-black">{{ formatCurrency(delivery.value) }}</span>
              <v-btn variant="text" color="primary" size="small" append-icon="mdi-chevron-right">
                {{ t('partner.orders.view') }}
              </v-btn>
            </div>
          </v-card>
        </v-col>
      </v-row>

      <div v-else-if="!loading" class="text-center py-12 opacity-50">
        <v-icon size="64" class="mb-4">mdi-truck-fast-outline</v-icon>
        <p>{{ t('courier.hybrid_deliveries.index_description') }}</p>
      </div>
    </div>
  </CourierLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { router } from '@inertiajs/vue3';
import CourierLayout from '@/Layouts/CourierLayout.vue';
import deliveryService from '@/services/delivery';

const { t, locale } = useI18n();
const deliveries = ref([]);
const loading = ref(false);

const formatCurrency = (value) => {
  return new Intl.NumberFormat(locale.value, {
    style: 'currency',
    currency: 'BRL'
  }).format(value || 0);
};

const getStatusColor = (status) => {
  if (['delivered', 'completed'].includes(status)) return 'success';
  if (['cancelled', 'failed'].includes(status)) return 'error';
  return 'primary';
};

const fetchDeliveries = async () => {
  loading.value = true;
  try {
    const res = await deliveryService.getAvailableDeliveries();
    const all = res.data?.deliveries || res.data || [];
    deliveries.value = all.filter(d => d.is_hybrid);
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const viewDetails = (id) => {
  router.visit(`/courier/hybrid-deliveries/${id}`);
};

onMounted(fetchDeliveries);
</script>

<style scoped>
.glass-card {
  background: rgba(255, 255, 255, 0.7) !important;
  backdrop-filter: blur(12px);
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  cursor: pointer;
  transition: transform 0.2s ease;
}

.glass-card:hover {
  transform: translateY(-4px);
}

.stage-line {
  height: 2px;
  background: rgba(var(--v-theme-primary), 0.1);
}

.truncate {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.hybrid-deliveries-container {
  animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>
