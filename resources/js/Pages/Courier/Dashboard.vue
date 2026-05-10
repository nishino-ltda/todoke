<template>
  <CourierLayout>
    <div class="courier-dashboard" data-cy="courier-dashboard">
      <!-- Status Card -->
      <v-card
        class="mb-6 status-card"
        :color="isOnline ? 'primary' : 'grey-darken-3'"
        theme="dark"
        data-cy="status-card"
      >
        <v-card-text class="d-flex align-center justify-space-between py-4">
          <div>
            <div class="text-subtitle-2 opacity-70">{{ t('courier.status.title') }}</div>
            <div class="text-h5 font-weight-bold">
              {{ isOnline ? t('courier.status.online') : t('courier.status.offline') }}
            </div>
          </div>
          <v-switch
            v-model="isOnline"
            color="white"
            hide-details
            inset
            data-cy="availability-toggle"
            @update:model-value="onAvailabilityChange"
          ></v-switch>
        </v-card-text>
      </v-card>

      <!-- Active Delivery (If any) -->
      <div v-if="activeDelivery" class="mb-6" data-cy="active-delivery-section">
        <h2 class="text-subtitle-1 font-weight-bold mb-3 d-flex align-center">
          <v-icon color="primary" class="mr-2">mdi-navigation-variant</v-icon>
          {{ t('courier.activeDelivery.title') }}
        </h2>
        <v-card border elevation="0" class="delivery-card active-delivery">
          <v-card-text>
            <div class="d-flex justify-space-between mb-4">
              <v-chip
                color="primary"
                size="small"
                class="text-uppercase font-weight-bold"
                data-cy="active-delivery-status"
              >
                {{ t(`courier.status.${activeDelivery.status}`, activeDelivery.status) }}
              </v-chip>
              <span class="text-caption text-grey">
                {{ t('courier.activeDelivery.id') }} #{{ activeDelivery.id }}
              </span>
            </div>

            <div class="location-timeline mb-4">
              <div class="location-item origin">
                <v-icon size="16" color="primary">mdi-circle-slice-8</v-icon>
                <div class="ml-4">
                  <div class="text-caption text-grey">{{ t('courier.activeDelivery.pickup') }}</div>
                  <div class="text-body-2 font-weight-bold" data-cy="active-delivery-origin">
                    {{ activeDelivery.origin_address }}
                  </div>
                </div>
              </div>
              <div class="location-divider"></div>
              <div class="location-item destination">
                <v-icon size="16" color="red">mdi-map-marker</v-icon>
                <div class="ml-4">
                  <div class="text-caption text-grey">{{ t('courier.activeDelivery.dropoff') }}</div>
                  <div class="text-body-2 font-weight-bold" data-cy="active-delivery-destination">
                    {{ activeDelivery.destination_address }}
                  </div>
                </div>
              </div>
            </div>

            <DeliveryMap
              v-if="activeDelivery.origin_lat && activeDelivery.destination_lat"
              :origin="{ lat: activeDelivery.origin_lat, lng: activeDelivery.origin_lng }"
              :destination="{ lat: activeDelivery.destination_lat, lng: activeDelivery.destination_lng }"
              class="mb-4"
              data-cy="active-delivery-map"
            />

            <v-btn
              block
              color="primary"
              size="large"
              class="font-weight-bold"
              :loading="updatingStatus"
              :disabled="updatingStatus || !nextStatus"
              @click="updateStatus"
              data-cy="update-status-btn"
            >
              {{ nextStatusLabel }}
            </v-btn>
          </v-card-text>
        </v-card>
      </div>

      <!-- Available Deliveries -->
      <div v-if="isOnline && !activeDelivery" data-cy="available-deliveries-section">
        <div class="d-flex align-center justify-space-between mb-3">
          <h2 class="text-subtitle-1 font-weight-bold">
            {{ t('courier.availableDeliveries.title') }}
          </h2>
          <v-progress-circular
            v-if="loading"
            indeterminate
            size="16"
            width="2"
            color="primary"
            data-cy="deliveries-loading"
          ></v-progress-circular>
        </div>

        <!-- Error state -->
        <v-alert
          v-if="fetchError"
          type="error"
          class="mb-4"
          closable
          data-cy="deliveries-error"
        >
          {{ t('courier.notifications.load_failed') }}
        </v-alert>

        <v-fade-transition group>
          <v-card
            v-for="delivery in availableDeliveries"
            :key="delivery.id"
            class="mb-4 delivery-card"
            variant="outlined"
            :data-cy="`delivery-card-${delivery.id}`"
          >
            <v-card-text>
              <div class="d-flex justify-space-between align-center mb-4">
                <div class="d-flex align-center">
                  <v-icon color="green" class="mr-2">mdi-currency-usd</v-icon>
                  <span class="text-h6 font-weight-bold" data-cy="delivery-fee">
                    {{ formatCurrency(delivery.fee || delivery.value || 0) }}
                  </span>
                </div>
                <span class="text-caption" data-cy="delivery-distance">
                  {{ t('courier.availableDeliveries.distance', { distance: delivery.distance || '?' }) }}
                </span>
              </div>

              <div class="text-body-2 mb-4 text-grey-darken-2" data-cy="delivery-restaurant">
                <v-icon size="16" class="mr-1">mdi-store</v-icon>
                {{ delivery.restaurant_name || delivery.partner_name || t('courier.availableDeliveries.unknown_partner') }}
              </div>

              <div class="d-flex ga-2">
                <v-btn
                  class="flex-grow-1"
                  color="primary"
                  variant="flat"
                  :loading="acceptingId === delivery.id"
                  :disabled="!!acceptingId"
                  @click="acceptDelivery(delivery)"
                  data-cy="accept-delivery-btn"
                >
                  {{ t('courier.availableDeliveries.accept') }}
                </v-btn>
                <v-btn
                  class="flex-grow-1"
                  color="error"
                  variant="outlined"
                  :disabled="!!acceptingId"
                  @click="rejectDelivery(delivery)"
                  data-cy="reject-delivery-btn"
                >
                  {{ t('courier.availableDeliveries.reject') }}
                </v-btn>
              </div>
            </v-card-text>
          </v-card>
        </v-fade-transition>

        <div
          v-if="availableDeliveries.length === 0 && !loading && !fetchError"
          class="text-center py-12 opacity-50"
          data-cy="no-deliveries-empty"
        >
          <v-icon size="64" class="mb-4">mdi-moped-electric</v-icon>
          <p>{{ t('courier.availableDeliveries.empty') }}</p>
        </div>
      </div>

      <!-- Offline state -->
      <div
        v-if="!isOnline"
        class="text-center py-12 opacity-50"
        data-cy="offline-state"
      >
        <v-icon size="64" class="mb-4">mdi-sleep</v-icon>
        <p>{{ t('courier.actions.goOnline') }}</p>
      </div>
    </div>
  </CourierLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import CourierLayout from '@/Layouts/CourierLayout.vue';
import DeliveryMap from '@/Components/DeliveryMap.vue';
import deliveryService from '@/services/delivery';
import { useNotificationStore } from '@/stores/notification';

const { t, locale } = useI18n();
const notifications = useNotificationStore();

// ─── State ────────────────────────────────────────────────────────────────────
const isOnline = ref(true);
const loading = ref(false);
const updatingStatus = ref(false);
const acceptingId = ref(null);
const fetchError = ref(false);
const availableDeliveries = ref([]);
const activeDelivery = ref(null);

// ─── Computed ─────────────────────────────────────────────────────────────────

/**
 * Maps current delivery status to the next status string expected by the API.
 * Backend accepts: 'collected' | 'in_transit' | 'delivered'
 * Dashboard uses:  'accepted' → 'arrived' (local stage), 'picked_up' → 'delivered'
 */
const nextStatus = computed(() => {
  if (!activeDelivery.value) return null;
  switch (activeDelivery.value.status) {
    case 'accepted': return 'collected';
    case 'arrived':  return 'collected';   // fallback if backend uses 'collected'
    case 'collected':return 'in_transit';
    case 'picked_up': return 'in_transit';
    case 'in_transit': return 'delivered';
    default: return null;
  }
});

const nextStatusLabel = computed(() => {
  if (!activeDelivery.value) return '';
  switch (activeDelivery.value.status) {
    case 'accepted':   return t('courier.stages.arrived');
    case 'arrived':    return t('courier.stages.picked_up');
    case 'collected':  return t('courier.stages.picked_up');
    case 'picked_up':  return t('courier.stages.delivered');
    case 'in_transit': return t('courier.stages.delivered');
    default: return t('courier.stages.next');
  }
});

// ─── Helpers ──────────────────────────────────────────────────────────────────

const formatCurrency = (value) => {
  const loc = locale.value || 'pt-BR';
  return new Intl.NumberFormat(loc, {
    style: 'currency',
    currency: 'BRL'
  }).format(value || 0);
};

// ─── Data fetching ────────────────────────────────────────────────────────────

const fetchDeliveries = async () => {
  if (!isOnline.value) return;
  loading.value = true;
  fetchError.value = false;
  try {
    const response = await deliveryService.getAvailableDeliveries();
    const all = response.data?.deliveries || response.data || [];
    // Separate pending (available) from active
    availableDeliveries.value = all.filter(d => d.status === 'pending');
    const found = all.find(d =>
      ['accepted', 'arrived', 'collected', 'picked_up', 'in_transit'].includes(d.status)
    );
    if (found) {
      activeDelivery.value = found;
    }
  } catch (err) {
    fetchError.value = true;
    notifications.error(t('courier.notifications.load_failed'));
  } finally {
    loading.value = false;
  }
};

const fetchActiveDelivery = async () => {
  try {
    const response = await deliveryService.getMyActiveDelivery();
    const all = response.data?.deliveries || response.data || [];
    activeDelivery.value = all.length > 0 ? all[0] : null;
  } catch {
    // Silently fail — active delivery check is secondary
  }
};

// ─── Actions ──────────────────────────────────────────────────────────────────

const onAvailabilityChange = (online) => {
  if (online) {
    fetchDeliveries();
  } else {
    availableDeliveries.value = [];
    fetchError.value = false;
  }
};

const acceptDelivery = async (delivery) => {
  acceptingId.value = delivery.id;
  try {
    await deliveryService.acceptDelivery(delivery.id);
    notifications.success(t('courier.notifications.accept_success'));
    // Move from available list to active
    availableDeliveries.value = availableDeliveries.value.filter(d => d.id !== delivery.id);
    activeDelivery.value = { ...delivery, status: 'accepted' };
  } catch (err) {
    notifications.error(t('courier.notifications.accept_failed'));
  } finally {
    acceptingId.value = null;
  }
};

const rejectDelivery = async (delivery) => {
  try {
    await deliveryService.rejectDelivery(delivery.id);
    availableDeliveries.value = availableDeliveries.value.filter(d => d.id !== delivery.id);
    notifications.info(t('courier.notifications.reject_success'));
  } catch {
    notifications.error(t('courier.notifications.reject_failed'));
  }
};

const updateStatus = async () => {
  if (!activeDelivery.value || !nextStatus.value) return;
  updatingStatus.value = true;
  try {
    const res = await deliveryService.updateDeliveryStatus(
      activeDelivery.value.id,
      nextStatus.value
    );
    const updatedStatus = res.data?.status || nextStatus.value;
    notifications.success(
      t('courier.notifications.update_success', {
        status: t(`courier.status.${updatedStatus}`, updatedStatus)
      })
    );
    if (updatedStatus === 'delivered') {
      // Delivery complete — clear active and refresh list
      activeDelivery.value = null;
      fetchDeliveries();
    } else {
      activeDelivery.value = { ...activeDelivery.value, status: updatedStatus };
    }
  } catch (err) {
    notifications.error(t('courier.notifications.update_failed'));
  } finally {
    updatingStatus.value = false;
  }
};

// ─── Lifecycle ────────────────────────────────────────────────────────────────

onMounted(async () => {
  await fetchActiveDelivery();
  if (!activeDelivery.value) {
    await fetchDeliveries();
  }
});
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
  to   { opacity: 1; transform: translateY(0); }
}
</style>
