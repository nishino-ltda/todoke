<template>
  <CourierLayout>
    <div v-if="loading" class="text-center py-12">
      <v-progress-circular indeterminate color="primary"></v-progress-circular>
    </div>

    <div v-else-if="delivery" class="delivery-details" data-cy="delivery-show">
      <v-row>
        <v-col cols="12" md="7">
          <!-- Status Card -->
          <v-card class="glass-card pa-6 mb-6">
            <div class="d-flex justify-space-between align-center mb-6">
              <div>
                <span class="text-caption text-grey text-uppercase">{{ t('courier.activeDelivery.id') }} #{{ delivery.id }}</span>
                <h2 class="text-h5 font-weight-black">{{ t(`courier.status.${delivery.status}`, delivery.status) }}</h2>
              </div>
              <v-chip :color="getStatusColor(delivery.status)" size="large" class="font-weight-bold">
                {{ formatCurrency(delivery.value) }}
              </v-chip>
            </div>

            <div class="location-timeline mb-6">
              <div class="location-item pb-6">
                <v-icon color="primary" class="mr-4">mdi-circle-slice-8</v-icon>
                <div>
                  <div class="text-caption text-grey">{{ t('courier.activeDelivery.pickup') }}</div>
                  <div class="text-body-1 font-weight-bold">{{ delivery.origin?.address }}</div>
                </div>
              </div>
              <div class="location-item">
                <v-icon color="error" class="mr-4">mdi-map-marker</v-icon>
                <div>
                  <div class="text-caption text-grey">{{ t('courier.activeDelivery.dropoff') }}</div>
                  <div class="text-body-1 font-weight-bold">{{ delivery.destination?.address }}</div>
                </div>
              </div>
            </div>

            <v-divider class="mb-6"></v-divider>

            <div v-if="nextStatus" class="actions">
              <v-btn
                block
                color="primary"
                size="large"
                class="font-weight-bold text-none"
                :loading="updating"
                @click="updateStatus"
                data-cy="update-status-btn"
              >
                {{ nextStatusLabel }}
              </v-btn>
            </div>
          </v-card>

          <!-- Customer Info -->
          <v-card class="glass-card pa-6">
            <h3 class="text-subtitle-1 font-weight-bold mb-4">{{ t('partner.orders.customer_info') }}</h3>
            <div class="d-flex align-center">
              <v-avatar color="primary" class="mr-4">
                <v-img v-if="delivery.customer?.photo_url" :src="delivery.customer.photo_url"></v-img>
                <span v-else class="text-white">{{ delivery.customer?.name?.[0] }}</span>
              </v-avatar>
              <div>
                <div class="font-weight-bold">{{ delivery.customer?.name }}</div>
                <div class="text-caption text-grey">{{ delivery.customer?.phone }}</div>
              </div>
              <v-spacer></v-spacer>
              <v-btn icon="mdi-phone" variant="tonal" color="primary" size="small" :href="`tel:${delivery.customer?.phone}`"></v-btn>
            </div>
          </v-card>
        </v-col>

        <v-col cols="12" md="5">
          <!-- Map -->
          <v-card class="glass-card overflow-hidden" height="400">
            <DeliveryMap
              :origin="delivery.origin"
              :destination="delivery.destination"
              class="fill-height"
            />
          </v-card>
          
          <!-- Items description -->
          <v-card class="glass-card pa-6 mt-6">
            <h3 class="text-subtitle-1 font-weight-bold mb-2">{{ t('partner.orders.items') }}</h3>
            <p class="text-body-2">{{ delivery.item_description }}</p>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <div v-else class="text-center py-12">
      <v-icon size="64" color="grey">mdi-alert-circle-outline</v-icon>
      <p class="mt-4">{{ t('partner.orders.not_found') }}</p>
      <v-btn class="mt-4" @click="router.visit('/courier/deliveries')">Back to List</v-btn>
    </div>
  </CourierLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { router } from '@inertiajs/vue3';
import CourierLayout from '@/Layouts/CourierLayout.vue';
import DeliveryMap from '@/Components/DeliveryMap.vue';
import deliveryService from '@/services/delivery';
import { useNotificationStore } from '@/stores/notification';

const props = defineProps({
  deliveryId: { type: [String, Number], required: true }
});

const { t, locale } = useI18n();
const notifications = useNotificationStore();
const delivery = ref(null);
const loading = ref(true);
const updating = ref(false);

const nextStatus = computed(() => {
  if (!delivery.value) return null;
  switch (delivery.value.status) {
    case 'accepted': return 'collected';
    case 'collected': return 'in_transit';
    case 'picked_up': return 'in_transit';
    case 'in_transit': return 'delivered';
    default: return null;
  }
});

const nextStatusLabel = computed(() => {
  if (!delivery.value) return '';
  switch (delivery.value.status) {
    case 'accepted': return t('courier.stages.arrived');
    case 'collected': return t('courier.stages.picked_up');
    case 'picked_up': return t('courier.stages.picked_up');
    case 'in_transit': return t('courier.stages.delivered');
    default: return '';
  }
});

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

const fetchDetails = async () => {
  loading.value = true;
  try {
    const res = await deliveryService.getDeliveryDetails(props.deliveryId);
    delivery.value = res.data;
  } catch (err) {
    notifications.error(t('courier.notifications.load_failed'));
  } finally {
    loading.value = false;
  }
};

const updateStatus = async () => {
  if (!nextStatus.value) return;
  updating.value = true;
  try {
    const res = await deliveryService.updateDeliveryStatus(delivery.value.id, nextStatus.value);
    delivery.value.status = res.data.status || nextStatus.value;
    notifications.success(t('courier.notifications.update_success', { status: delivery.value.status }));
  } catch (err) {
    notifications.error(t('courier.notifications.update_failed'));
  } finally {
    updating.value = false;
  }
};

onMounted(fetchDetails);
</script>

<style scoped>
.glass-card {
  background: rgba(255, 255, 255, 0.7) !important;
  backdrop-filter: blur(12px);
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.location-timeline {
  position: relative;
  padding-left: 10px;
}

.location-timeline::before {
  content: '';
  position: absolute;
  left: 21px;
  top: 30px;
  bottom: 20px;
  width: 2px;
  background: rgba(var(--v-theme-primary), 0.2);
  border-left: 2px dashed rgba(var(--v-theme-primary), 0.4);
}

.location-item {
  display: flex;
  align-items: flex-start;
  position: relative;
  z-index: 1;
}

.delivery-details {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
