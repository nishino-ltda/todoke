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
            <v-card-title class="px-6 py-4">System Activity</v-card-title>
            <v-card-text>
              <div class="py-12 text-center text-grey opacity-50">
                <v-icon size="64" class="mb-4">mdi-chart-areaspline</v-icon>
                <p>Real-time activity charts will be integrated here.</p>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" lg="4">
          <v-card border elevation="0" class="rounded-xl">
            <v-card-title class="px-6 py-4">Quick Actions</v-card-title>
            <v-list density="comfortable">
              <v-list-item
                prepend-icon="mdi-account-plus"
                title="Review New Users"
                @click="router.visit('/admin/users')"
                link
              ></v-list-item>
              <v-list-item
                prepend-icon="mdi-lan-check"
                title="Approve Pending Nodes"
                @click="router.visit('/admin/nodes')"
                link
              ></v-list-item>
              <v-list-item
                prepend-icon="mdi-alert-circle"
                title="System Health Check"
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
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import MetricsWidget from '@/Components/MetricsWidget.vue';
import adminService from '@/services/admin';
import { useNotificationStore } from '@/stores/notification';

const notifications = useNotificationStore();
const metrics = ref([
  { title: 'Total Users', value: '0', icon: 'mdi-account-group', color: 'blue' },
  { title: 'Active Deliveries', value: '0', icon: 'mdi-moped-electric', color: 'green' },
  { title: 'System Nodes', value: '0', icon: 'mdi-lan', color: 'purple' },
  { title: 'Issues Reported', value: '0', icon: 'mdi-alert-circle', color: 'red' },
]);

const fetchStats = async () => {
  try {
    const response = await adminService.getSystemStats();
    const data = response.data || {};
    metrics.value[0].value = data.total_users || '0';
    metrics.value[1].value = data.active_deliveries || '0';
    metrics.value[2].value = data.total_nodes || '0';
    metrics.value[3].value = data.reported_issues || '0';
  } catch (err) {
    notifications.error('Failed to load system stats');
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
