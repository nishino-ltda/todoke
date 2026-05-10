<template>
  <AdminLayout>
    <div class="deliveries-monitor-page" data-cy="deliveries-monitor-page">
      <div class="d-flex align-center justify-space-between mb-6">
        <h1 class="text-h4 font-weight-bold">
          <v-icon color="primary" class="mr-2">mdi-radar</v-icon>
          Live Monitoring
        </h1>
        <v-btn
          color="secondary"
          variant="outlined"
          prepend-icon="mdi-refresh"
          @click="fetchDeliveries"
          :loading="loading"
        >
          Refresh Map
        </v-btn>
      </div>

      <v-card border elevation="0" class="rounded-xl overflow-hidden mb-6">
        <div class="monitor-map-container">
          <v-alert
            v-if="!hasActiveDeliveries"
            type="info"
            variant="tonal"
            class="ma-4"
          >
            No active deliveries to monitor right now.
          </v-alert>
          <div v-else class="pa-12 text-center grey-lighten-4">
            <v-icon size="64" color="grey-lighten-1">mdi-map-search</v-icon>
            <p class="text-h6 text-grey mt-4">Real-time global delivery map would be rendered here.</p>
            <p class="text-body-2 text-grey">Showing {{ activeDeliveries.length }} active routes.</p>
          </div>
        </div>
      </v-card>

      <v-row>
        <v-col cols="12" md="4" v-for="delivery in activeDeliveries" :key="delivery.id">
          <v-card border elevation="0" class="rounded-xl pa-4" @click="router.visit(`/admin/deliveries/${delivery.id}`)" hover>
            <div class="d-flex justify-space-between align-center mb-2">
              <span class="text-overline text-grey">#{{ delivery.id }}</span>
              <v-chip :color="getStatusColor(delivery.status)" size="x-small" class="text-uppercase font-weight-bold">
                {{ delivery.status }}
              </v-chip>
            </div>
            <div class="text-subtitle-1 font-weight-bold truncate mb-1">
              {{ delivery.customer?.name }}
            </div>
            <div class="text-caption text-grey d-flex align-center">
              <v-icon size="12" class="mr-1">mdi-bike</v-icon>
              {{ delivery.courier?.name || 'Searching courier...' }}
            </div>
          </v-row>
        </v-col>
      </v-row>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import adminService from '@/services/admin';

const loading = ref(false);
const deliveries = ref([]);

const activeDeliveries = computed(() => 
  deliveries.value.filter(d => !['delivered', 'canceled', 'failed'].includes(d.status))
);

const hasActiveDeliveries = computed(() => activeDeliveries.value.length > 0);

const fetchDeliveries = async () => {
  loading.value = true;
  try {
    const response = await adminService.getDeliveries();
    deliveries.value = response.data?.deliveries || response.data || [];
  } catch (err) {
    console.error('Failed to load deliveries', err);
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
    canceled: 'error'
  };
  return colors[status] || 'grey';
};

onMounted(fetchDeliveries);
</script>

<style scoped>
.monitor-map-container {
  height: 500px;
  background-color: #f5f5f5;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.truncate {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
