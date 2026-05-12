<template>
  <CourierLayout>
    <div v-if="loading" class="text-center py-12">
      <v-progress-circular indeterminate color="primary"></v-progress-circular>
    </div>

    <div v-else-if="delivery" class="hybrid-delivery-details" data-cy="hybrid-delivery-show">
      <v-row>
        <v-col cols="12" md="7">
          <!-- Status Card -->
          <v-card class="glass-card pa-6 mb-6">
            <div class="d-flex justify-space-between align-center mb-6">
              <div>
                <v-chip color="secondary" size="small" class="mb-1 font-weight-bold text-uppercase">Hybrid Delivery</v-chip>
                <h2 class="text-h5 font-weight-black">#{{ delivery.id }}</h2>
              </div>
              <v-chip :color="getStatusColor(delivery.status)" size="large" class="font-weight-bold">
                {{ t(`courier.status.${delivery.status}`, delivery.status) }}
              </v-chip>
            </div>

            <!-- Stages Stepper (Visual) -->
            <div class="stages-stepper mb-8">
              <v-row no-gutters align="center">
                <v-col v-for="(stage, index) in delivery.stages" :key="index" class="text-center">
                  <div class="d-flex flex-column align-center">
                    <v-avatar
                      :color="getStageColor(stage.status)"
                      size="40"
                      class="mb-2 elevation-2"
                    >
                      <v-icon color="white" size="20">
                        {{ getStageIcon(stage.type) }}
                      </v-icon>
                    </v-avatar>
                    <span class="text-caption font-weight-bold">{{ stage.type.replace('_', ' ') }}</span>
                    <v-chip size="x-small" :color="getStageColor(stage.status)" class="mt-1">
                      {{ stage.status }}
                    </v-chip>
                  </div>
                </v-col>
              </v-row>
            </div>

            <v-divider class="mb-6"></v-divider>

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

            <div v-if="canHandoff" class="actions mt-4">
              <v-btn
                block
                color="secondary"
                size="large"
                class="font-weight-bold text-none"
                @click="router.visit(`/courier/hybrid-deliveries/${delivery.id}/handoff`)"
                data-cy="handoff-btn"
              >
                Go to Handoff Point
              </v-btn>
            </div>
          </v-card>
        </v-col>

        <v-col cols="12" md="5">
          <!-- Multi-stage Map placeholder -->
          <v-card class="glass-card overflow-hidden pa-4" height="400">
            <h3 class="text-subtitle-2 mb-2">Stage Tracking Map</h3>
            <DeliveryMap
              :origin="delivery.origin"
              :destination="delivery.destination"
              class="fill-height rounded-lg"
            />
          </v-card>

          <v-card class="glass-card pa-6 mt-6">
            <h3 class="text-subtitle-1 font-weight-bold mb-4">Logistics Information</h3>
            <div class="d-flex align-center mb-4">
              <v-icon color="primary" class="mr-3">mdi-truck-outline</v-icon>
              <div>
                <div class="text-caption text-grey">Vehicle</div>
                <div class="text-body-2 font-weight-bold">{{ delivery.type }}</div>
              </div>
            </div>
            <div class="d-flex align-center">
              <v-icon color="primary" class="mr-3">mdi-weight-kilogram</v-icon>
              <div>
                <div class="text-caption text-grey">Weight</div>
                <div class="text-body-2 font-weight-bold">{{ delivery.estimated_weight }} kg</div>
              </div>
            </div>
          </v-card>
        </v-col>
      </v-row>
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
  hybridDeliveryId: { type: [String, Number], required: true }
});

const { t } = useI18n();
const notifications = useNotificationStore();
const delivery = ref(null);
const loading = ref(true);

const canHandoff = computed(() => {
  return delivery.value?.status === 'in_transit' || delivery.value?.status === 'picked_up';
});

const getStageColor = (status) => {
  switch (status) {
    case 'delivered': return 'success';
    case 'in_transit': return 'primary';
    case 'pending': return 'grey-lighten-1';
    case 'canceled': return 'error';
    default: return 'primary';
  }
};

const getStageIcon = (type) => {
  switch (type) {
    case 'delivery_point': return 'mdi-store';
    case 'distribution_center': return 'mdi-warehouse';
    case 'drone_launch': return 'mdi-drone';
    case 'customer_delivery': return 'mdi-home-map-marker';
    default: return 'mdi-circle';
  }
};

const getStatusColor = (status) => {
  if (['delivered', 'completed'].includes(status)) return 'success';
  if (['cancelled', 'failed'].includes(status)) return 'error';
  return 'primary';
};

const fetchDetails = async () => {
  loading.value = true;
  try {
    const res = await deliveryService.getDeliveryDetails(props.hybridDeliveryId);
    delivery.value = res.data;
  } catch (err) {
    notifications.error(t('courier.notifications.load_failed'));
  } finally {
    loading.value = false;
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

.stages-stepper {
  position: relative;
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
  border-left: 2px dashed rgba(0,0,0,0.1);
}

.location-item {
  display: flex;
  align-items: flex-start;
  position: relative;
  z-index: 1;
}

.hybrid-delivery-details {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
