<template>
  <CourierLayout>
    <div class="courier-dashboard" data-cy="courier-dashboard">
      <!-- Status Card -->
      <v-card
        class="mb-6 status-card glass-card overflow-hidden"
        :color="isOnline ? 'primary' : 'grey-darken-3'"
        theme="dark"
        data-cy="status-card"
        elevation="0"
      >
        <div class="status-bg-animation" :class="{ online: isOnline }"></div>
        <v-card-text class="d-flex align-center justify-space-between py-6 px-8 relative-z">
          <div>
            <div class="text-overline opacity-70 mb-1">{{ t('courier.status.title') }}</div>
            <div class="text-h4 font-weight-black">
              {{ isOnline ? t('courier.status.online') : t('courier.status.offline') }}
            </div>
          </div>
          <v-switch
            v-model="isOnline"
            color="white"
            hide-details
            inset
            scale="1.2"
            data-cy="availability-toggle"
            @update:model-value="onAvailabilityChange"
          ></v-switch>
        </v-card-text>
      </v-card>

      <v-row>
        <!-- Active Delivery (If any) -->
        <v-col v-if="activeDelivery" cols="12" md="7">
          <h2 class="text-h6 font-weight-bold mb-4 d-flex align-center">
            <v-icon color="primary" class="mr-2">mdi-navigation-variant</v-icon>
            {{ t('courier.activeDelivery.title') }}
          </h2>
          <v-card class="glass-card active-delivery-glow pa-6 mb-6">
            <div class="d-flex justify-space-between align-center mb-6">
              <v-chip
                color="primary"
                variant="flat"
                class="text-uppercase font-weight-black"
                data-cy="active-delivery-status"
              >
                {{ t(`courier.status.${activeDelivery.status}`, activeDelivery.status) }}
              </v-chip>
              <span class="text-h6 font-weight-bold color-primary">
                {{ formatCurrency(activeDelivery.value) }}
              </span>
            </div>

            <div class="location-timeline mb-6">
              <div class="location-item origin">
                <v-icon size="20" color="primary">mdi-circle-slice-8</v-icon>
                <div class="ml-4">
                  <div class="text-caption text-grey">{{ t('courier.activeDelivery.pickup') }}</div>
                  <div class="text-body-1 font-weight-bold" data-cy="active-delivery-origin">
                    {{ activeDelivery.origin_address || activeDelivery.origin?.address }}
                  </div>
                </div>
              </div>
              <div class="location-divider"></div>
              <div class="location-item destination">
                <v-icon size="20" color="red">mdi-map-marker</v-icon>
                <div class="ml-4">
                  <div class="text-caption text-grey">{{ t('courier.activeDelivery.dropoff') }}</div>
                  <div class="text-body-1 font-weight-bold" data-cy="active-delivery-destination">
                    {{ activeDelivery.destination_address || activeDelivery.destination?.address }}
                  </div>
                </div>
              </div>
            </div>

            <div class="map-container mb-6 rounded-xl overflow-hidden elevation-2">
              <DeliveryMap
                v-if="activeDelivery.origin && activeDelivery.destination"
                :origin="activeDelivery.origin"
                :destination="activeDelivery.destination"
                class="fill-height"
                data-cy="active-delivery-map"
              />
            </div>

            <v-btn
              block
              color="primary"
              size="x-large"
              class="font-weight-black text-none rounded-xl"
              :loading="updatingStatus"
              :disabled="updatingStatus || !nextStatus"
              @click="updateStatus"
              data-cy="update-status-btn"
              elevation="4"
            >
              {{ nextStatusLabel }}
            </v-btn>
          </v-card>
        </v-col>

        <!-- Available Deliveries -->
        <v-col :cols="12" :md="activeDelivery ? 5 : 12">
          <div v-if="isOnline" data-cy="available-deliveries-section">
            <div class="d-flex align-center justify-space-between mb-4">
              <h2 class="text-h6 font-weight-bold">
                {{ t('courier.availableDeliveries.title') }}
              </h2>
              <v-progress-circular
                v-if="loading"
                indeterminate
                size="20"
                width="2"
                color="primary"
              ></v-progress-circular>
            </div>

            <v-fade-transition group>
              <v-card
                v-for="delivery in availableDeliveries"
                :key="delivery.id"
                class="mb-4 glass-card delivery-hover-card pa-4"
                data-cy="delivery-card"
              >
                <div class="d-flex justify-space-between align-center mb-4">
                  <div class="d-flex align-center">
                    <v-avatar color="green-lighten-5" class="mr-3" size="48">
                      <v-icon color="green">mdi-currency-usd</v-icon>
                    </v-avatar>
                    <div>
                      <div class="text-h6 font-weight-black">{{ formatCurrency(delivery.value) }}</div>
                      <div class="text-caption text-grey">Est. {{ delivery.estimated_time }} min</div>
                    </div>
                  </div>
                  <v-chip size="small" variant="tonal" color="primary">
                    {{ delivery.type }}
                  </v-chip>
                </div>

                <div class="text-body-2 mb-4 d-flex align-center">
                  <v-icon size="16" class="mr-2" color="grey">mdi-store-outline</v-icon>
                  <span class="font-weight-bold">{{ delivery.logistics_partner?.name || t('courier.availableDeliveries.unknown_partner') }}</span>
                </div>

                <div class="d-flex ga-3">
                  <v-btn
                    class="flex-grow-1 font-weight-bold text-none rounded-lg"
                    color="primary"
                    :loading="acceptingId === delivery.id"
                    @click="acceptDelivery(delivery)"
                    data-cy="accept-delivery-btn"
                  >
                    {{ t('courier.availableDeliveries.accept') }}
                  </v-btn>
                  <v-btn
                    class="font-weight-bold text-none rounded-lg"
                    color="grey-lighten-3"
                    variant="flat"
                    icon="mdi-close"
                    @click="rejectDelivery(delivery)"
                  ></v-btn>
                </div>
              </v-card>
            </v-fade-transition>

            <div
              v-if="availableDeliveries.length === 0 && !loading"
              class="text-center py-12 glass-card opacity-70 rounded-xl"
            >
              <v-icon size="64" class="mb-4 text-primary opacity-30">mdi-moped-electric</v-icon>
              <p class="font-weight-medium">{{ t('courier.availableDeliveries.empty') }}</p>
            </div>
          </div>

          <!-- Offline state -->
          <div
            v-if="!isOnline"
            class="text-center py-12 glass-card rounded-xl"
          >
            <v-icon size="80" class="mb-4 text-grey-lighten-2">mdi-power-sleep</v-icon>
            <h3 class="text-h6 font-weight-bold mb-2">{{ t('courier.status.offline') }}</h3>
            <p class="text-grey px-8">{{ t('courier.actions.goOnline') }}</p>
            <v-btn
              color="primary"
              class="mt-6 px-10 text-none font-weight-bold"
              size="large"
              rounded="xl"
              @click="isOnline = true; onAvailabilityChange(true)"
            >
              Go Online
            </v-btn>
          </div>
        </v-col>
      </v-row>
    </div>
  </CourierLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRealtime } from '@/composables/useRealtime';
import CourierLayout from '@/Layouts/CourierLayout.vue';
import DeliveryMap from '@/Components/DeliveryMap.vue';
import deliveryService from '@/services/delivery';
import { useNotificationStore } from '@/stores/notification';

const { t, locale } = useI18n();
const notifications = useNotificationStore();
const realtime = useRealtime();

// ─── State ────────────────────────────────────────────────────────────────────
const isOnline = ref(true);
const loading = ref(false);
const updatingStatus = ref(false);
const acceptingId = ref(null);
const availableDeliveries = ref([]);
const activeDelivery = ref(null);

// ─── Computed ─────────────────────────────────────────────────────────────────
const nextStatus = computed(() => {
  if (!activeDelivery.value) return null;
  switch (activeDelivery.value.status) {
    case 'accepted': return 'collected';
    case 'collected': return 'in_transit';
    case 'picked_up': return 'in_transit';
    case 'in_transit': return 'delivered';
    default: return null;
  }
});

const nextStatusLabel = computed(() => {
  if (!activeDelivery.value) return '';
  switch (activeDelivery.value.status) {
    case 'accepted': return t('courier.stages.arrived');
    case 'collected': return t('courier.stages.picked_up');
    case 'picked_up': return t('courier.stages.picked_up');
    case 'in_transit': return t('courier.stages.delivered');
    default: return t('courier.stages.next');
  }
});

// ─── Helpers ──────────────────────────────────────────────────────────────────
const formatCurrency = (value) => {
  return new Intl.NumberFormat(locale.value, {
    style: 'currency',
    currency: 'BRL'
  }).format(value || 0);
};

// ─── Actions ──────────────────────────────────────────────────────────────────
const fetchDeliveries = async () => {
  if (!isOnline.value) return;
  loading.value = true;
  try {
    const res = await deliveryService.getAvailableDeliveries();
    const all = res.data?.deliveries || res.data || [];
    
    // Separate active from available
    activeDelivery.value = all.find(d => ['accepted', 'arrived', 'collected', 'picked_up', 'in_transit'].includes(d.status));
    availableDeliveries.value = all.filter(d => d.status === 'pending');
  } catch (err) {
    notifications.error(t('courier.notifications.load_failed'));
  } finally {
    loading.value = false;
  }
};

const onAvailabilityChange = (online) => {
  if (online) {
    fetchDeliveries();
    realtime.listenToAvailableDeliveries((delivery) => {
      availableDeliveries.value.unshift(delivery);
      notifications.info(t('notifications.new_delivery_available'));
    });
  } else {
    realtime.leaveAvailableDeliveries();
    availableDeliveries.value = [];
  }
};

const acceptDelivery = async (delivery) => {
  acceptingId.value = delivery.id;
  try {
    const res = await deliveryService.acceptDelivery(delivery.id);
    notifications.success(t('courier.notifications.accept_success'));
    activeDelivery.value = { ...delivery, status: 'accepted' };
    availableDeliveries.value = availableDeliveries.value.filter(d => d.id !== delivery.id);
  } catch (err) {
    notifications.error(t('courier.notifications.accept_failed'));
  } finally {
    acceptingId.value = null;
  }
};

const rejectDelivery = (delivery) => {
  availableDeliveries.value = availableDeliveries.value.filter(d => d.id !== delivery.id);
  notifications.info(t('courier.notifications.reject_success'));
};

const updateStatus = async () => {
  if (!activeDelivery.value || !nextStatus.value) return;
  updatingStatus.value = true;
  try {
    const res = await deliveryService.updateDeliveryStatus(activeDelivery.value.id, nextStatus.value);
    const updatedStatus = res.data?.status || nextStatus.value;
    notifications.success(t('courier.notifications.update_success', { status: updatedStatus }));
    
    if (updatedStatus === 'delivered') {
      activeDelivery.value = null;
      fetchDeliveries();
    } else {
      activeDelivery.value.status = updatedStatus;
    }
  } catch (err) {
    notifications.error(t('courier.notifications.update_failed'));
  } finally {
    updatingStatus.value = false;
  }
};

// ─── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(() => {
  fetchDeliveries();
  if (isOnline.value) onAvailabilityChange(true);
});

onUnmounted(() => {
  realtime.leaveAvailableDeliveries();
  if (activeDelivery.value) realtime.leaveDelivery(activeDelivery.value.id);
});

watch(activeDelivery, (newVal, oldVal) => {
  if (oldVal) realtime.leaveDelivery(oldVal.id);
  if (newVal) {
    realtime.listenToDelivery(newVal.id, (update) => {
      activeDelivery.value.status = update.status;
    });
  }
});
</script>

<style scoped>
.glass-card {
  background: rgba(255, 255, 255, 0.7) !important;
  backdrop-filter: blur(12px);
  border-radius: 24px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05) !important;
}

.status-card {
  position: relative;
  border-radius: 24px;
  transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.status-bg-animation {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(45deg, #424242, #212121);
  opacity: 1;
  transition: all 0.5s ease;
}

.status-bg-animation.online {
  background: linear-gradient(45deg, #1565C0, #0D47A1);
}

.relative-z {
  position: relative;
  z-index: 1;
}

.active-delivery-glow {
  border: 2px solid rgba(var(--v-theme-primary), 0.3);
  box-shadow: 0 0 20px rgba(var(--v-theme-primary), 0.1) !important;
}

.map-container {
  height: 300px;
  border: 1px solid rgba(0,0,0,0.05);
}

.location-timeline {
  position: relative;
  padding-left: 10px;
}

.location-divider {
  width: 2px;
  height: 30px;
  background: rgba(0,0,0,0.05);
  margin-left: 9px;
  border-left: 2px dashed rgba(var(--v-theme-primary), 0.2);
}

.location-item {
  display: flex;
  align-items: flex-start;
}

.delivery-hover-card {
  transition: all 0.3s ease;
}

.delivery-hover-card:hover {
  transform: translateY(-4px);
  background: rgba(255, 255, 255, 0.9) !important;
}

.courier-dashboard {
  animation: fadeIn 0.6s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.color-primary {
  color: #1565C0;
}
</style>
