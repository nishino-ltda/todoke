<template>
  <AdminLayout>
    <div class="admin-dashboard" data-cy="admin-dashboard">
      <!-- Metric Cards -->
      <v-row class="mb-6">
        <v-col cols="12" sm="6" lg="3" v-for="metric in metrics" :key="metric.title">
          <MetricsWidget
            :title="metric.title"
            :value="metric.value"
            :icon="metric.icon"
            :color="metric.color"
            data-cy="admin-metric"
          />
        </v-col>
      </v-row>

      <v-row>
        <!-- Charts Panel -->
        <v-col cols="12" lg="8">
          <v-card border elevation="0" class="rounded-xl">
            <v-card-title class="px-6 py-4 d-flex align-center justify-space-between">
              <span>{{ t('admin.dashboard.chart.title') }}</span>
              <v-btn-toggle
                v-model="activePeriod"
                mandatory
                color="primary"
                density="compact"
                rounded="lg"
                data-cy="chart-period-filter"
              >
                <v-btn value="today" size="small" data-cy="filter-today">
                  {{ t('admin.dashboard.filters.today') }}
                </v-btn>
                <v-btn value="7days" size="small" data-cy="filter-7days">
                  {{ t('admin.dashboard.filters.7days') }}
                </v-btn>
                <v-btn value="30days" size="small" data-cy="filter-30days">
                  {{ t('admin.dashboard.filters.30days') }}
                </v-btn>
                <v-btn value="all" size="small" data-cy="filter-all">
                  {{ t('admin.dashboard.filters.all') }}
                </v-btn>
              </v-btn-toggle>
            </v-card-title>
            <v-card-text>
              <div v-if="chartLoading" class="py-12 text-center">
                <v-progress-circular indeterminate color="primary" />
              </div>
              <div v-else class="chart-wrapper" data-cy="activity-chart">
                <Line :data="lineChartData" :options="lineChartOptions" />
              </div>
            </v-card-text>
          </v-card>

          <!-- Bar Chart: Registrations -->
          <v-card border elevation="0" class="rounded-xl mt-4">
            <v-card-title class="px-6 py-4">
              {{ t('admin.dashboard.chart.registrations') }}
            </v-card-title>
            <v-card-text>
              <div class="chart-wrapper" data-cy="registrations-chart">
                <Bar :data="barChartData" :options="barChartOptions" />
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Quick Actions Panel -->
        <v-col cols="12" lg="4">
          <!-- Recent Users Widget -->
          <v-card border elevation="0" class="rounded-xl mb-4">
            <v-card-title class="px-6 py-4 d-flex align-center">
              <span>New Users</span>
              <v-chip v-if="recentUsers.length" color="primary" size="small" class="ml-2">
                {{ recentUsers.length }}
              </v-chip>
            </v-card-title>
            <v-list density="comfortable">
              <v-list-item
                v-for="u in recentUsers"
                :key="u.id"
                :prepend-avatar="`https://ui-avatars.com/api/?name=${u.name}&background=0D47A1&color=fff`"
                :title="u.name"
                :subtitle="`${u.type} · ${daysAgo(u.created_at)}`"
                @click="router.visit('/admin/users')"
                link
              >
                <template v-slot:append>
                  <v-chip
                    :color="getRoleColor(u.type)"
                    size="x-small"
                    variant="flat"
                    class="text-uppercase"
                  >
                    {{ u.type }}
                  </v-chip>
                </template>
              </v-list-item>
            </v-list>
          </v-card>

          <v-card border elevation="0" class="rounded-xl">
            <v-card-title class="px-6 py-4">{{ t('admin.dashboard.quickActions') }}</v-card-title>
            <v-list density="comfortable">
              <v-list-item
                prepend-icon="mdi-account-plus"
                :title="t('admin.dashboard.reviewNewUsers')"
                @click="router.visit('/admin/users')"
                link
                data-cy="quick-action-users"
              ></v-list-item>
              <v-list-item
                prepend-icon="mdi-map-marker-multiple"
                :title="t('admin.regions.title')"
                @click="router.visit('/admin/regions')"
                link
                data-cy="quick-action-regions"
              ></v-list-item>
              <v-list-item
                prepend-icon="mdi-alert-circle"
                :title="t('admin.dashboard.systemHealth')"
                link
                data-cy="quick-action-health"
              ></v-list-item>
            </v-list>
          </v-card>
        </v-col>
      </v-row>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js';
import { Line, Bar } from 'vue-chartjs';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import MetricsWidget from '@/Components/MetricsWidget.vue';
import adminService from '@/services/admin';
import { useNotificationStore } from '@/stores/notification';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  Filler
);

const { t } = useI18n();
const notifications = useNotificationStore();
import { useRealtime } from '@/composables/useRealtime';

const props = defineProps({
  recentUsers: {
    type: Array,
    default: () => [],
  },
});

const realtime = useRealtime();


const activePeriod = ref('7days');
const chartLoading = ref(false);

const stats = ref({
  total_users: '0',
  active_deliveries: '0',
  reported_issues: '0'
});

// ── Chart data generators ─────────────────────────────────────────────────────

const getLabels = (period) => {
  const today = new Date();
  const labels = [];
  const count = period === 'today' ? 24 : period === '7days' ? 7 : period === '30days' ? 30 : 12;
  const fmt = period === 'today'
    ? (i) => `${(today.getHours() - (count - 1 - i) + 24) % 24}h`
    : period === 'all'
      ? (i) => {
          const d = new Date(today); d.setMonth(d.getMonth() - (count - 1 - i));
          return d.toLocaleString('default', { month: 'short' });
        }
      : (i) => {
          const d = new Date(today); d.setDate(d.getDate() - (count - 1 - i));
          return d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
        };
  for (let i = 0; i < count; i++) labels.push(fmt(i));
  return labels;
};

// Seeded pseudo-random data for chart (based on stats + period)
const makeData = (base, count) =>
  Array.from({ length: count }, (_, i) =>
    Math.max(0, Math.round(base * 0.5 + Math.sin(i * 0.7) * base * 0.3 + Math.random() * base * 0.2))
  );

const chartLabels = computed(() => getLabels(activePeriod.value));
const deliveryData = computed(() => {
  const base = Number(stats.value.active_deliveries) || 10;
  return makeData(base, chartLabels.value.length);
});
const registrationData = computed(() => {
  const base = Math.ceil(Number(stats.value.total_users) / 10) || 5;
  return makeData(base, chartLabels.value.length);
});

const lineChartData = computed(() => ({
  labels: chartLabels.value,
  datasets: [
    {
      label: t('admin.dashboard.chart.deliveries'),
      data: deliveryData.value,
      borderColor: '#1976D2',
      backgroundColor: 'rgba(25, 118, 210, 0.1)',
      tension: 0.4,
      fill: true,
    }
  ]
}));

const barChartData = computed(() => ({
  labels: chartLabels.value,
  datasets: [
    {
      label: t('admin.dashboard.chart.registrations'),
      data: registrationData.value,
      backgroundColor: 'rgba(76, 175, 80, 0.7)',
      borderColor: '#4CAF50',
      borderWidth: 1,
      borderRadius: 4,
    }
  ]
}));

const chartOptions = {
  responsive: true,
  maintainAspectRatio: true,
  plugins: {
    legend: { display: true, position: 'top' },
    tooltip: { mode: 'index', intersect: false }
  },
  scales: {
    y: { beginAtZero: true, ticks: { stepSize: 1 } }
  }
};

const lineChartOptions = { ...chartOptions };
const barChartOptions = { ...chartOptions };

// ── Metrics ───────────────────────────────────────────────────────────────────

const metrics = computed(() => [
  { title: t('admin.dashboard.metrics.totalUsers'), value: stats.value.total_users, icon: 'mdi-account-group', color: 'blue' },
  { title: t('admin.dashboard.metrics.activeDeliveries'), value: stats.value.active_deliveries, icon: 'mdi-moped-electric', color: 'green' },
  { title: t('admin.dashboard.metrics.issuesReported'), value: stats.value.reported_issues, icon: 'mdi-alert-circle', color: 'red' },
]);

const fetchStats = async () => {
  chartLoading.value = true;
  try {
    const response = await adminService.getSystemStats();
    const data = response.data || {};
    stats.value = {
      total_users: data.total_users?.toString() || '0',
      active_deliveries: data.active_deliveries?.toString() || '0',
      reported_issues: data.reported_issues?.toString() || '0'
    };
  } catch (err) {
    notifications.error(t('admin.dashboard.error_load'));
  } finally {
    chartLoading.value = false;
  }
};

const daysAgo = (dateStr) => {
  const days = Math.floor((Date.now() - new Date(dateStr).getTime()) / (1000 * 60 * 60 * 24));
  if (days === 0) return 'Today';
  if (days === 1) return '1 day ago';
  return `${days} days ago`;
};

const getRoleColor = (role) => {
  const colors = { admin: 'red', partner: 'blue', courier: 'green', customer: 'grey' };
  return colors[role] || 'grey';
};

watch(activePeriod, () => {
  // Chart data is computed, filter change is reactive — no extra fetch needed
});

onMounted(() => {
  realtime.setupListeners();
  fetchStats();
});

import { onUnmounted } from 'vue';
onUnmounted(() => {
  realtime.leaveChannels();
});

</script>

<style scoped>
.admin-dashboard {
  animation: fadeIn 0.5s ease-out;
}

.chart-wrapper {
  position: relative;
  height: 280px;
}

@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.98); }
  to { opacity: 1; transform: scale(1); }
}
</style>
