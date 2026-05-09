<template>
  <AdminLayout>
    <div class="user-management">
      <div class="d-flex align-center justify-space-between mb-6">
        <h1 class="text-h4 font-weight-bold">User Management</h1>
        <v-btn
          color="primary"
          prepend-icon="mdi-account-plus"
          data-cy="create-user-btn"
        >
          Add User
        </v-btn>
      </div>

      <DataTable
        :headers="headers"
        :items="users"
        :loading="loading"
        data-cy="users-table"
      >
        <template #item.role="{ item }">
          <v-chip
            :color="getRoleColor(item.role)"
            size="small"
            class="text-uppercase font-weight-bold"
          >
            {{ item.role }}
          </v-chip>
        </template>

        <template #item.status="{ item }">
          <v-chip
            :color="item.active ? 'success' : 'error'"
            size="x-small"
            variant="flat"
          >
            {{ item.active ? 'Active' : 'Suspended' }}
          </v-chip>
        </template>

        <template #item.actions="{ item }">
          <v-btn
            variant="text"
            color="primary"
            icon="mdi-pencil"
            data-cy="edit-user-btn"
          ></v-btn>
          <v-btn
            v-if="item.active"
            variant="text"
            color="error"
            icon="mdi-account-off"
            @click="toggleUserStatus(item, 'deactivate')"
            data-cy="deactivate-user-btn"
          ></v-btn>
          <v-btn
            v-else
            variant="text"
            color="success"
            icon="mdi-account-check"
            @click="toggleUserStatus(item, 'activate')"
            data-cy="activate-user-btn"
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
const users = ref([]);

const headers = [
  { title: 'ID', key: 'id', width: '80px' },
  { title: 'Name', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Role', key: 'role' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false, align: 'end' },
];

const fetchUsers = async () => {
  loading.value = true;
  try {
    const response = await adminService.getUsers();
    users.value = response.data;
  } catch (err) {
    notifications.error('Failed to load users');
  } finally {
    loading.value = false;
  }
};

const toggleUserStatus = async (user, action) => {
  try {
    await adminService.manageUser(user.id, action);
    notifications.success(`User ${user.name} has been ${action === 'activate' ? 'activated' : 'suspended'}`);
    fetchUsers();
  } catch (err) {
    notifications.error('Failed to update user status');
  }
};

const getRoleColor = (role) => {
  const colors = {
    'admin': 'red-darken-4',
    'partner': 'blue-darken-2',
    'courier': 'green-darken-2',
    'customer': 'grey-darken-1'
  };
  return colors[role] || 'grey';
};

onMounted(fetchUsers);
</script>

<style scoped>
.user-management {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
