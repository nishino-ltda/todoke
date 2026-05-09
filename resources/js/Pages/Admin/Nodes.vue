<template>
  <AdminLayout>
    <div class="node-management">
      <h1 class="text-h4 font-weight-bold mb-6">Node Approval & Management</h1>

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
            {{ item.status }}
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
            Approve
          </v-btn>
          <v-btn
            v-if="item.status === 'pending'"
            variant="outlined"
            color="error"
            size="small"
            @click="rejectNode(item)"
            data-cy="reject-node-btn"
          >
            Reject
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
import { ref, onMounted } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import adminService from '@/services/admin';
import { useNotificationStore } from '@/stores/notification';

const notifications = useNotificationStore();
const loading = ref(false);
const nodes = ref([]);

const headers = [
  { title: 'Node ID', key: 'id' },
  { title: 'Name', key: 'name' },
  { title: 'Type', key: 'type' },
  { title: 'Owner', key: 'owner_name' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false, align: 'end' },
];

const fetchNodes = async () => {
  loading.value = true;
  try {
    const response = await adminService.getNodes();
    nodes.value = response.data;
  } catch (err) {
    notifications.error('Failed to load nodes');
  } finally {
    loading.value = false;
  }
};

const approveNode = async (node) => {
  try {
    await adminService.updateNodeStatus(node.id, 'approved');
    notifications.success(`Node ${node.name} approved`);
    fetchNodes();
  } catch (err) {
    notifications.error('Failed to approve node');
  }
};

const rejectNode = async (node) => {
  try {
    await adminService.updateNodeStatus(node.id, 'rejected');
    notifications.warning(`Node ${node.name} rejected`);
    fetchNodes();
  } catch (err) {
    notifications.error('Failed to reject node');
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
