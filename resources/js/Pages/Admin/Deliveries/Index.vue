<template>
  <AdminLayout>
    <div class="deliveries-monitoring">
      <div class="d-flex align-center justify-space-between mb-6">
        <h1 class="text-h4 font-weight-bold">{{ t('admin.deliveries.title') }}</h1>
        <div class="d-flex gap-2">
          <v-btn
            color="secondary"
            variant="outlined"
            prepend-icon="mdi-refresh"
            @click="fetchDeliveries"
            :loading="loading"
          >
            {{ t('partner.orders.refresh') }}
          </v-btn>
        </div>
      </div>

      <v-row class="mb-6">
        <v-col cols="12" md="3">
          <v-card class="pa-4" elevation="1">
            <div class="text-overline text-grey">{{ t('admin.dashboard.metrics.activeDeliveries') }}</div>
            <div class="text-h4 font-weight-bold text-primary">{{ activeCount }}</div>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card class="pa-4" elevation="1">
            <div class="text-overline text-grey">Pending</div>
            <div class="text-h4 font-weight-bold text-warning">{{ pendingCount }}</div>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card class="pa-4" elevation="1">
            <div class="text-overline text-grey">In Transit</div>
            <div class="text-h4 font-weight-bold text-info">{{ inTransitCount }}</div>
          </v-card>
        </v-col>
        <v-col cols="12" md="3">
          <v-card class="pa-4" elevation="1">
            <div class="text-overline text-grey">Delivered Today</div>
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
          >
            {{ t(`courier.status.${item.status}`) }}
          </v-chip>
        </template>

        <template #item.customer="{ item }">
          <div class="d-flex align-center">
            <v-avatar size="24" color="grey-lighten-3" class="mr-2">
              <v-icon size="16">mdi-account</v-icon>
            </v-avatar>
            <span>{{ item.customer?.name || 'Unknown' }}</span>
          </div>
        </template>

        <template #item.courier="{ item }">
          <div v-if="item.courier" class="d-flex align-center">
            <v-avatar size="24" color="grey-lighten-3" class="mr-2">
              <v-icon size="16">mdi-bike</v-icon>
            </v-avatar>
            <span>{{ item.courier?.name }}</span>
          </div>
          <span v-else class="text-grey text-caption">Not assigned</span>
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
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import adminService from '@/services/admin';
import { useNotificationStore } from '@/stores/notification';

const { t } = useI18n();
const notifications = useNotificationStore();
const loading = ref(false);
const deliveries = ref([]);

const headers = computed(() => [
  { title: t('admin.deliveries.table.id'), key: 'id', width: '80px' },
  { title: t('admin.deliveries.table.customer'), key: 'customer' },
  { title: t('admin.deliveries.table.courier'), key: 'courier' },
  { title: t('admin.deliveries.table.status'), key: 'status' },
  { title: t('admin.deliveries.table.type'), key: 'type' },
  { title: t('admin.deliveries.table.value'), key: 'value', align: 'end' },
  { title: t('admin.deliveries.table.actions'), key: 'actions', sortable: false, align: 'end' },
]);

const activeCount = computed(() => deliveries.value.filter(d => !['delivered', 'canceled', 'failed'].includes(d.status)).length);
const pendingCount = computed(() => deliveries.value.filter(d => d.status === 'pending').length);
const inTransitCount = computed(() => deliveries.value.filter(d => d.status === 'in_transit').length);
const deliveredTodayCount = computed(() => {
  const today = new Date().toISOString().split('T')[0];
  return deliveries.value.filter(d => d.status === 'delivered' && d.updated_at.startsWith(today)).length;
});

const fetchDeliveries = async () => {
  loading.value = true;
  try {
    const response = await adminService.getDeliveries();
    // In a real paginated API, this would handle data property
    deliveries.value = response.data.deliveries || response.data;
  } catch (err) {
    notifications.error(t('admin.deliveries.notifications.load_failed'));
  } finally {
    loading.value = false;
  }
};

const getStatusColor = (status) => {
  const colors = {
    'pending': 'warning',
    'accepted': 'info',
    'collected': 'info',
    'in_transit': 'primary',
    'delivered': 'success',
    'canceled': 'error',
    'failed': 'error',
    'drone_launched': 'deep-purple',
    'drone_in_route': 'deep-purple-lighten-1',
    'drone_arrived': 'deep-purple-darken-1'
  };
  return colors[status] || 'grey';
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
};

const viewDelivery = (delivery) => {
  // Navigation to detail page could be implemented here
  console.log('Viewing delivery', delivery.id);
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

.gap-2 {
  gap: 8px;
}
</style>
