<template>
  <CourierLayout>
    <div class="courier-dashboard">
      <!-- Status Card -->
      <v-card class="mb-6 status-card" :color="isOnline ? 'primary' : 'grey-darken-3'" theme="dark">
        <v-card-text class="d-flex align-center justify-space-between py-4">
          <div>
            <div class="text-subtitle-2 opacity-70">{{ t('courier.status.title') }}</div>
            <div class="text-h5 font-weight-bold">{{ isOnline ? t('courier.status.online') : t('courier.status.offline') }}</div>
          </div>
          <v-switch
            v-model="isOnline"
            color="white"
            hide-details
            inset
            data-cy="availability-toggle"
          ></v-switch>
        </v-card-text>
      </v-card>

      <!-- Active Delivery (If any) -->
      <div v-if="activeDelivery" class="mb-6">
        <h2 class="text-subtitle-1 font-weight-bold mb-3 d-flex align-center">
          <v-icon color="primary" class="mr-2">mdi-navigation-variant</v-icon>
          {{ t('courier.activeDelivery.title') }}
        </h2>
        <v-card border elevation="0" class="delivery-card active-delivery">
          <v-card-text>
            <div class="d-flex justify-space-between mb-4">
              <v-chip color="primary" size="small" class="text-uppercase font-weight-bold">
                {{ t(`courier.status.${activeDelivery.status}`) }}
              </v-chip>
              <span class="text-caption text-grey">{{ t('courier.activeDelivery.id') }} #{{ activeDelivery.id }}</span>
            </div>

            <div class="location-timeline mb-4">
              <div class="location-item origin">
                <v-icon size="16" color="primary">mdi-circle-slice-8</v-icon>
                <div class="ml-4">
                  <div class="text-caption text-grey">{{ t('courier.activeDelivery.pickup') }}</div>
                  <div class="text-body-2 font-weight-bold">{{ activeDelivery.origin_address }}</div>
                </div>
              </div>
              <div class="location-divider"></div>
              <div class="location-item destination">
                <v-icon size="16" color="red">mdi-map-marker</v-icon>
                <div class="ml-4">
                  <div class="text-caption text-grey">{{ t('courier.activeDelivery.dropoff') }}</div>
                  <div class="text-body-2 font-weight-bold">{{ activeDelivery.destination_address }}</div>
                </div>
              </div>
            </div>

            <v-btn
              block
              color="primary"
              size="large"
              class="font-weight-bold"
              @click="updateStatus"
              data-cy="update-status-btn"
            >
              {{ nextStatusLabel }}
            </v-btn>
          </v-card-text>
        </v-card>
      </div>

      <!-- Available Deliveries -->
      <div v-if="isOnline && !activeDelivery">
        <div class="d-flex align-center justify-space-between mb-3">
          <h2 class="text-subtitle-1 font-weight-bold">{{ t('courier.availableDeliveries.title') }}</h2>
          <v-progress-circular v-if="loading" indeterminate size="16" width="2" color="primary"></v-progress-circular>
        </div>

        <v-fade-transition group>
          <v-card
            v-for="delivery in availableDeliveries"
            :key="delivery.id"
            class="mb-4 delivery-card"
            variant="outlined"
          >
            <v-card-text>
              <div class="d-flex justify-space-between align-center mb-4">
                <div class="d-flex align-center">
                  <v-icon color="green" class="mr-2">mdi-currency-usd</v-icon>
                  <span class="text-h6 font-weight-bold">{{ formatCurrency(delivery.fee) }}</span>
                </div>
                <span class="text-caption">{{ t('courier.availableDeliveries.distance', { distance: delivery.distance }) }}</span>
              </div>

              <div class="text-body-2 mb-4 text-grey-darken-2">
                <v-icon size="16" class="mr-1">mdi-store</v-icon> {{ delivery.restaurant_name }}
              </div>

              <v-btn
                block
                color="primary"
                variant="flat"
                @click="acceptDelivery(delivery)"
                data-cy="accept-delivery-btn"
              >
                {{ t('courier.availableDeliveries.accept') }}
              </v-btn>
            </v-card-text>
          </v-card>
        </v-fade-transition>

        <div v-if="availableDeliveries.length === 0 && !loading" class="text-center py-12 opacity-50">
          <v-icon size="64" class="mb-4">mdi-moped-electric</v-icon>
          <p>{{ t('courier.availableDeliveries.empty') }}</p>
        </div>
      </div>

      <div v-if="!isOnline" class="text-center py-12 opacity-50">
        <v-icon size="64" class="mb-4">mdi-sleep</v-icon>
        <p>{{ t('courier.actions.goOnline') }}</p>
      </div>
    </div>
  </CourierLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import CourierLayout from '@/Layouts/CourierLayout.vue';
import deliveryService from '@/services/delivery';
import { useNotificationStore } from '@/stores/notification';

const { t } = useI18n();
const notifications = useNotificationStore();
const isOnline = ref(true);
const loading = ref(false);
const availableDeliveries = ref([]);
const activeDelivery = ref(null);

const nextStatusLabel = computed(() => {
  if (!activeDelivery.value) return '';
  switch (activeDelivery.value.status) {
    case 'accepted': return t('courier.stages.arrived');
    case 'arrived': return t('courier.stages.picked_up');
    case 'picked_up': return t('courier.stages.delivered');
    default: return t('courier.stages.next');
  }
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat(t('locale') === 'locale' ? 'pt-BR' : t('locale'), { 
    style: 'currency', 
    currency: 'BRL' 
  }).format(value)
}

const fetchDeliveries = async () => {
  if (!isOnline.value) return;
  loading.value = true;
  try {
    const response = await deliveryService.getMyDeliveries();
    // In a real app, we'd distinguish between available and active
    // For now, let's mock the logic
    const all = response.data || [];
    activeDelivery.value = all.find(d => ['accepted', 'arrived', 'picked_up'].includes(d.status));
    availableDeliveries.value = all.filter(d => d.status === 'pending');
  } catch (err) {
    notifications.error(t('courier.notifications.load_failed'));
  } finally {
    loading.value = false;
  }
};

const acceptDelivery = async (delivery) => {
  try {
    await deliveryService.updateStatus(delivery.id, 'accepted');
    notifications.success(t('courier.notifications.accept_success'));
    fetchDeliveries();
  } catch (err) {
    notifications.error(t('courier.notifications.accept_failed'));
  }
};

const updateStatus = async () => {
  if (!activeDelivery.value) return;
  
  let nextStatus = '';
  switch (activeDelivery.value.status) {
    case 'accepted': nextStatus = 'arrived'; break;
    case 'arrived': nextStatus = 'picked_up'; break;
    case 'picked_up': nextStatus = 'delivered'; break;
  }

  try {
    await deliveryService.updateStatus(activeDelivery.value.id, nextStatus);
    notifications.success(t('courier.notifications.update_success', { status: t(`courier.status.${nextStatus}`) }));
    fetchDeliveries();
  } catch (err) {
    notifications.error(t('courier.notifications.update_failed'));
  }
};

onMounted(fetchDeliveries);
</script>

<style scoped>
.status-card {
  border-radius: 16px;
  transition: all 0.3s ease;
}

.delivery-card {
  border-radius: 12px;
  background: white;
}

.active-delivery {
  border: 2px solid var(--v-primary-base);
}

.location-timeline {
  position: relative;
}

.location-item {
  display: flex;
  align-items: flex-start;
}

.location-divider {
  width: 2px;
  height: 24px;
  background: #eee;
  margin-left: 7px;
  margin-top: 4px;
  margin-bottom: 4px;
}

.courier-dashboard {
  animation: slideUp 0.4s ease-out;
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
