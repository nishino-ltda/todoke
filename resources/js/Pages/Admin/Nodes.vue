<template>
  <AdminLayout>
    <div class="node-management">
      <h1 class="text-h4 font-weight-bold mb-6">{{ t('admin.nodes.title') }}</h1>

      <DataTable
        :headers="headers"
        :items="nodes"
        :loading="loading"
        data-cy="nodes-table"
      >
        <template #item.status="{ item }">
          <v-chip
            :color="getStatusColor(item.status)"
            size="small"
            class="text-uppercase font-weight-bold"
          >
            {{ t(`admin.nodes.status.${item.status}`) }}
          </v-chip>
        </template>

        <template #item.actions="{ item }">
          <v-btn
            v-if="item.status === 'pending'"
            variant="flat"
            color="success"
            size="small"
            class="mr-2"
            @click="approveNode(item)"
            data-cy="approve-node-btn"
          >
            {{ t('admin.nodes.actions.approve') }}
          </v-btn>
          <v-btn
            v-if="item.status === 'pending'"
            variant="outlined"
            color="error"
            size="small"
            @click="rejectNode(item)"
            data-cy="reject-node-btn"
          >
            {{ t('admin.nodes.actions.reject') }}
          </v-btn>
          <v-btn
            v-if="item.status !== 'pending'"
            variant="text"
            color="primary"
            icon="mdi-pencil"
            data-cy="edit-node-btn"
          ></v-btn>
        </template>
      </DataTable>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import adminService from '@/services/admin';
import { useNotificationStore } from '@/stores/notification';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const notifications = useNotificationStore();
const loading = ref(false);
const nodes = ref([]);

const headers = computed(() => [
  { title: t('admin.nodes.table.id'), key: 'id' },
  { title: t('admin.nodes.table.name'), key: 'name' },
  { title: t('admin.nodes.table.type'), key: 'type' },
  { title: t('admin.nodes.table.owner'), key: 'owner_name' },
  { title: t('admin.nodes.table.status'), key: 'status' },
  { title: t('admin.nodes.table.actions'), key: 'actions', sortable: false, align: 'end' },
]);

const fetchNodes = async () => {
  loading.value = true;
  try {
    const response = await adminService.getNodes();
    nodes.value = response.data;
  } catch (err) {
    notifications.error(t('admin.nodes.notifications.load_failed'));
  } finally {
    loading.value = false;
  }
};

const approveNode = async (node) => {
  try {
    await adminService.updateNodeStatus(node.id, 'approved');
    notifications.success(t('admin.nodes.notifications.approve_success', { name: node.name }));
    fetchNodes();
  } catch (err) {
    notifications.error(t('admin.nodes.notifications.approve_failed'));
  }
};

const rejectNode = async (node) => {
  try {
    await adminService.updateNodeStatus(node.id, 'rejected');
    notifications.warning(t('admin.nodes.notifications.reject_success', { name: node.name }));
    fetchNodes();
  } catch (err) {
    notifications.error(t('admin.nodes.notifications.reject_failed'));
  }
};

const getStatusColor = (status) => {
  const colors = {
    'pending': 'orange',
    'approved': 'success',
    'rejected': 'error',
    'maintenance': 'warning'
  };
  return colors[status] || 'grey';
};

onMounted(fetchNodes);
</script>

<style scoped>
.node-management {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
