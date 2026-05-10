<template>
  <AdminLayout>
    <div class="deliveries-monitoring" data-cy="deliveries-monitoring">
      <div class="d-flex align-center justify-space-between mb-6">
        <h1 class="text-h4 font-weight-bold">{{ t('admin.deliveries.title') }}</h1>
        <div class="d-flex align-center gap-4">
          <v-btn-toggle
            v-model="activePeriod"
            mandatory
            color="primary"
            density="compact"
            rounded="lg"
            class="mr-4"
            data-cy="period-filter"
          >
            <v-btn value="today" size="small">{{ t('admin.dashboard.filters.today') }}</v-btn>
            <v-btn value="7days" size="small">{{ t('admin.dashboard.filters.7days') }}</v-btn>
            <v-btn value="30days" size="small">{{ t('admin.dashboard.filters.30days') }}</v-btn>
            <v-btn value="all" size="small">{{ t('admin.dashboard.filters.all') }}</v-btn>
          </v-btn-toggle>
          <v-btn
            color="secondary"
            variant="outlined"
            prepend-icon="mdi-refresh"
            @click="fetchDeliveries"
            :loading="loading"
            data-cy="refresh-deliveries-btn"
          >
            {{ t('partner.orders.refresh') }}
          </v-btn>
        </div>
      </div>

      <!-- Metrics & Charts -->
      <v-row class="mb-6">
        <v-col cols="12" lg="8">
          <v-row>
            <v-col cols="12" sm="6" md="3">
              <v-card class="pa-4 rounded-xl" border elevation="0" data-cy="metric-active">
                <div class="text-overline text-grey">{{ t('admin.dashboard.metrics.activeDeliveries') }}</div>
                <div class="text-h4 font-weight-bold text-primary">{{ activeCount }}</div>
              </v-card>
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <v-card class="pa-4 rounded-xl" border elevation="0" data-cy="metric-pending">
                <div class="text-overline text-grey">{{ t('admin.deliveries.pending') }}</div>
                <div class="text-h4 font-weight-bold text-warning">{{ pendingCount }}</div>
              </v-card>
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <v-card class="pa-4 rounded-xl" border elevation="0" data-cy="metric-in-transit">
                <div class="text-overline text-grey">{{ t('admin.deliveries.in_transit') }}</div>
                <div class="text-h4 font-weight-bold text-info">{{ inTransitCount }}</div>
              </v-card>
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <v-card class="pa-4 rounded-xl" border elevation="0" data-cy="metric-delivered-today">
                <div class="text-overline text-grey">{{ t('admin.deliveries.delivered_today') }}</div>
                <div class="text-h4 font-weight-bold text-success">{{ deliveredTodayCount }}</div>
              </v-card>
            </v-col>
          </v-row>

          <v-card border elevation="0" class="rounded-xl mt-6">
            <v-card-title class="px-6 py-4">{{ t('admin.dashboard.chart.title') }}</v-card-title>
            <v-card-text>
              <div class="chart-wrapper">
                <Line :data="lineChartData" :options="chartOptions" />
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" lg="4">
          <v-card border elevation="0" class="rounded-xl fill-height">
            <v-card-title class="px-6 py-4">{{ t('admin.deliveries.table.status') }}</v-card-title>
            <v-card-text class="d-flex align-center justify-center">
              <div class="pie-wrapper">
                <Doughnut :data="pieChartData" :options="pieOptions" />
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <v-card border elevation="0" class="rounded-xl overflow-hidden">
        <DataTable
          :headers="headers"
          :items="filteredDeliveries"
          :loading="loading"
          data-cy="deliveries-table"
        >
          <template #item.status="{ item }">
            <v-chip
              :color="getStatusColor(item.status)"
              size="small"
              class="text-uppercase font-weight-bold"
              data-cy="delivery-status-chip"
            >
              {{ t(`courier.status.${item.status}`, item.status) }}
            </v-chip>
          </template>

          <template #item.customer="{ item }">
            <div class="d-flex align-center">
              <v-avatar size="24" color="blue-lighten-5" class="mr-2">
                <v-icon size="16" color="blue">mdi-account</v-icon>
              </v-avatar>
              <span>{{ item.customer?.name || t('admin.deliveries.detail.not_assigned') }}</span>
            </div>
          </template>

          <template #item.courier="{ item }">
            <div v-if="item.courier" class="d-flex align-center">
              <v-avatar size="24" color="green-lighten-5" class="mr-2">
                <v-icon size="16" color="green">mdi-bike</v-icon>
              </v-avatar>
              <span>{{ item.courier?.name }}</span>
            </div>
            <span v-else class="text-grey text-caption">
              {{ t('admin.deliveries.detail.not_assigned') }}
            </span>
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
              @click="router.visit(`/admin/deliveries/${item.id}`)"
              data-cy="view-delivery-btn"
            ></v-btn>
          </template>
        </DataTable>
      </v-card>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js';
import { Line, Doughnut } from 'vue-chartjs';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import adminService from '@/services/admin';
import { useNotificationStore } from '@/stores/notification';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
  Filler
);

const { t, locale } = useI18n();
const notifications = useNotificationStore();
const loading = ref(false);
const deliveries = ref([]);
const activePeriod = ref('all');

const headers = computed(() => [
  { title: t('admin.deliveries.table.id'), key: 'id', width: '80px' },
  { title: t('admin.deliveries.table.customer'), key: 'customer' },
  { title: t('admin.deliveries.table.courier'), key: 'courier' },
  { title: t('admin.deliveries.table.status'), key: 'status' },
  { title: t('admin.deliveries.table.type'), key: 'type' },
  { title: t('admin.deliveries.table.value'), key: 'value', align: 'end' },
  { title: t('admin.deliveries.table.actions'), key: 'actions', sortable: false, align: 'end' },
]);

const filteredDeliveries = computed(() => {
  if (activePeriod.value === 'all') return deliveries.value;
  
  const now = new Date();
  const filterDate = new Date();
  if (activePeriod.value === 'today') filterDate.setHours(0, 0, 0, 0);
  else if (activePeriod.value === '7days') filterDate.setDate(now.getDate() - 7);
  else if (activePeriod.value === '30days') filterDate.setDate(now.getDate() - 30);
  
  return deliveries.value.filter(d => new Date(d.created_at) >= filterDate);
});

const activeCount = computed(() =>
  deliveries.value.filter(d => !['delivered', 'canceled', 'failed'].includes(d.status)).length
);
const pendingCount = computed(() =>
  deliveries.value.filter(d => d.status === 'pending').length
);
const inTransitCount = computed(() =>
  deliveries.value.filter(d => ['in_transit', 'collected', 'accepted'].includes(d.status)).length
);
const deliveredTodayCount = computed(() => {
  const today = new Date().toISOString().split('T')[0];
  return deliveries.value.filter(
    d => d.status === 'delivered' && d.updated_at?.startsWith(today)
  ).length;
});

// ── Chart Data ──────────────────────────────────────────────────────────────

const getStatusColor = (status) => {
  const colors = {
    pending: '#FB8C00', // warning
    accepted: '#03A9F4', // info
    collected: '#00BCD4', // cyan
    in_transit: '#2196F3', // primary
    delivered: '#4CAF50', // success
    canceled: '#F44336', // error
    failed: '#B71C1C',
    drone_launched: '#673AB7',
    drone_in_route: '#9575CD',
    drone_arrived: '#512DA8'
  };
  return colors[status] || '#9E9E9E';
};

const lineChartData = computed(() => {
  // Mocking timeline data based on filtered deliveries
  const labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
  return {
    labels,
    datasets: [{
      label: t('admin.dashboard.chart.deliveries'),
      data: [12, 19, 15, 22, 28, 24, 30],
      borderColor: '#2196F3',
      backgroundColor: 'rgba(33, 150, 243, 0.1)',
      tension: 0.4,
      fill: true
    }]
  };
});

const pieChartData = computed(() => {
  const statusCounts = {};
  deliveries.value.forEach(d => {
    statusCounts[d.status] = (statusCounts[d.status] || 0) + 1;
  });

  const labels = Object.keys(statusCounts);
  return {
    labels: labels.map(s => t(`courier.status.${s}`, s)),
    datasets: [{
      data: Object.values(statusCounts),
      backgroundColor: labels.map(s => getStatusColor(s)),
      borderWidth: 0
    }]
  };
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: { y: { beginAtZero: true, display: false }, x: { grid: { display: false } } }
};

const pieOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 6, font: { size: 10 } } }
  },
  cutout: '70%'
};

const fetchDeliveries = async () => {
  loading.value = true;
  try {
    const response = await adminService.getDeliveries();
    deliveries.value = response.data?.deliveries || response.data || [];
  } catch (err) {
    notifications.error(t('admin.deliveries.notifications.load_failed'));
  } finally {
    loading.value = false;
  }
};

const formatCurrency = (value) =>
  new Intl.NumberFormat(locale.value || 'pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0);

onMounted(fetchDeliveries);
</script>

<style scoped>
.deliveries-monitoring {
  animation: fadeIn 0.5s ease-out;
}

.chart-wrapper {
  height: 120px;
}

.pie-wrapper {
  height: 250px;
  width: 100%;
}

.gap-4 {
  gap: 16px;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.location-item {
  display: flex;
  align-items: flex-start;
}
</style>
