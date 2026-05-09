<template>
  <AdminLayout>
    <div class="admin-dashboard">
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
        <v-col cols="12" lg="8">
          <v-card border elevation="0" class="rounded-xl">
            <v-card-title class="px-6 py-4">{{ t('admin.dashboard.systemActivity') }}</v-card-title>
            <v-card-text>
              <div class="py-12 text-center text-grey opacity-50">
                <v-icon size="64" class="mb-4">mdi-chart-areaspline</v-icon>
                <p>{{ t('admin.dashboard.chartPlaceholder') }}</p>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" lg="4">
          <v-card border elevation="0" class="rounded-xl">
            <v-card-title class="px-6 py-4">{{ t('admin.dashboard.quickActions') }}</v-card-title>
            <v-list density="comfortable">
              <v-list-item
                prepend-icon="mdi-account-plus"
                :title="t('admin.dashboard.reviewNewUsers')"
                @click="router.visit('/admin/users')"
                link
              ></v-list-item>
              <v-list-item
                prepend-icon="mdi-lan-check"
                :title="t('admin.dashboard.approvePendingNodes')"
                @click="router.visit('/admin/nodes')"
                link
              ></v-list-item>
              <v-list-item
                prepend-icon="mdi-alert-circle"
                :title="t('admin.dashboard.systemHealth')"
                link
              ></v-list-item>
            </v-list>
          </v-card>
        </v-col>
      </v-row>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import MetricsWidget from '@/Components/MetricsWidget.vue';
import adminService from '@/services/admin';
import { useNotificationStore } from '@/stores/notification';

const { t } = useI18n();
const notifications = useNotificationStore();

const stats = ref({
  total_users: '0',
  active_deliveries: '0',
  total_nodes: '0',
  reported_issues: '0'
});

const metrics = computed(() => [
  { title: t('admin.dashboard.metrics.totalUsers'), value: stats.value.total_users, icon: 'mdi-account-group', color: 'blue' },
  { title: t('admin.dashboard.metrics.activeDeliveries'), value: stats.value.active_deliveries, icon: 'mdi-moped-electric', color: 'green' },
  { title: t('admin.dashboard.metrics.systemNodes'), value: stats.value.total_nodes, icon: 'mdi-lan', color: 'purple' },
  { title: t('admin.dashboard.metrics.issuesReported'), value: stats.value.reported_issues, icon: 'mdi-alert-circle', color: 'red' },
]);

const fetchStats = async () => {
  try {
    const response = await adminService.getSystemStats();
    const data = response.data || {};
    stats.value = {
      total_users: data.total_users?.toString() || '0',
      active_deliveries: data.active_deliveries?.toString() || '0',
      total_nodes: data.total_nodes?.toString() || '0',
      reported_issues: data.reported_issues?.toString() || '0'
    };
  } catch (err) {
    notifications.error(t('admin.dashboard.error_load'));
  }
};

onMounted(fetchStats);
</script>

<style scoped>
.admin-dashboard {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.98); }
  to { opacity: 1; transform: scale(1); }
}
</style>
