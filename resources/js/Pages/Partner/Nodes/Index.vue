<template>
  <PartnerLayout>
    <div class="nodes-index">
      <div class="d-flex align-center justify-space-between mb-6">
        <h1 class="text-h4 font-weight-bold">Nodes Management</h1>
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          @click="openCreateModal"
          data-cy="create-node-btn"
        >
          Add Node
        </v-btn>
      </div>

      <DataTable
        :headers="headers"
        :items="nodes"
        :loading="loading"
        data-cy="nodes-table"
      >
        <template #item.type="{ item }">
          <v-chip
            :color="getTypeColor(item.type)"
            size="small"
            variant="tonal"
            class="text-uppercase font-weight-bold"
          >
            {{ item.type }}
          </v-chip>
        </template>

        <template #item.actions="{ item }">
          <v-btn
            variant="text"
            color="primary"
            icon="mdi-pencil"
            @click="editNode(item)"
            data-cy="edit-node-btn"
          ></v-btn>
          <v-btn
            variant="text"
            color="error"
            icon="mdi-delete"
            @click="confirmDelete(item)"
            data-cy="delete-node-btn"
          ></v-btn>
        </template>
      </DataTable>

      <!-- Node Form Modal -->
      <AppModal
        v-model="showFormModal"
        :title="isEditing ? 'Edit Node' : 'Add New Node'"
        maxWidth="600"
      >
        <v-form ref="nodeForm" @submit.prevent="saveNode">
          <v-row>
            <v-col cols="12">
              <v-text-field
                v-model="form.name"
                label="Node Name"
                required
                :rules="[v => !!v || 'Name is required']"
                data-cy="node-name-input"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-select
                v-model="form.region_id"
                :items="regions"
                item-title="name"
                item-value="id"
                label="Region"
                required
                :rules="[v => !!v || 'Region is required']"
                data-cy="node-region-select"
              ></v-select>
            </v-col>
            <v-col cols="12" sm="6">
              <v-select
                v-model="form.type"
                :items="['hub', 'store', 'locker']"
                label="Node Type"
                required
                data-cy="node-type-select"
              ></v-select>
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model="form.address"
                label="Address"
                required
                data-cy="node-address-input"
              ></v-text-field>
            </v-col>
          </v-row>
        </v-form>
        <template #actions>
          <v-btn variant="text" @click="showFormModal = false">Cancel</v-btn>
          <v-btn color="primary" @click="saveNode" :loading="saving" data-cy="save-node-btn">
            {{ isEditing ? 'Update' : 'Create' }}
          </v-btn>
        </template>
      </AppModal>

      <!-- Delete Confirmation -->
      <AppModal v-model="showDeleteModal" title="Confirm Delete" maxWidth="400">
        <p>Are you sure you want to delete <strong>{{ selectedNode?.name }}</strong>?</p>
        <template #actions>
          <v-btn variant="text" @click="showDeleteModal = false">Cancel</v-btn>
          <v-btn color="error" @click="doDelete" :loading="saving" data-cy="confirm-delete-btn">Delete</v-btn>
        </template>
      </AppModal>
    </div>
  </PartnerLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import AppModal from '@/Components/AppModal.vue';
import partnerService from '@/services/partner';
import { useNotificationStore } from '@/stores/notification';

const notifications = useNotificationStore();
const loading = ref(false);
const saving = ref(false);
const nodes = ref([]);
const regions = ref([]);
const showFormModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const selectedNode = ref(null);
const nodeForm = ref(null);

const headers = [
  { title: 'Name', key: 'name' },
  { title: 'Type', key: 'type' },
  { title: 'Region', key: 'region_name' },
  { title: 'Address', key: 'address' },
  { title: 'Actions', key: 'actions', sortable: false, align: 'end' },
];

const form = ref({
  name: '',
  region_id: null,
  type: 'store',
  address: ''
});

const fetchNodes = async () => {
  loading.value = true;
  try {
    const response = await partnerService.getNodes();
    nodes.value = response.data;
  } catch (err) {
    notifications.error('Failed to load nodes');
  } finally {
    loading.value = false;
  }
};

const fetchRegions = async () => {
  try {
    const response = await partnerService.getRegions();
    regions.value = response.data;
  } catch (err) {
    console.error('Failed to load regions');
  }
};

const openCreateModal = () => {
  isEditing.value = false;
  form.value = { name: '', region_id: null, type: 'store', address: '' };
  showFormModal.value = true;
};

const editNode = (node) => {
  isEditing.value = true;
  selectedNode.value = node;
  form.value = { ...node };
  showFormModal.value = true;
};

const saveNode = async () => {
  const { valid } = await nodeForm.value.validate();
  if (!valid) return;

  saving.value = true;
  try {
    if (isEditing.value) {
      await partnerService.updateNode(selectedNode.value.id, form.value);
      notifications.success('Node updated successfully');
    } else {
      await partnerService.createNode(form.value);
      notifications.success('Node created successfully');
    }
    showFormModal.value = false;
    fetchNodes();
  } catch (err) {
    notifications.error('Failed to save node');
  } finally {
    saving.value = false;
  }
};

const confirmDelete = (node) => {
  selectedNode.value = node;
  showDeleteModal.value = true;
};

const doDelete = async () => {
  saving.value = true;
  try {
    await partnerService.deleteNode(selectedNode.value.id);
    notifications.success('Node deleted successfully');
    showDeleteModal.value = false;
    fetchNodes();
  } catch (err) {
    notifications.error('Failed to delete node');
  } finally {
    saving.value = false;
  }
};

const getTypeColor = (type) => {
  const colors = {
    'hub': 'primary',
    'store': 'success',
    'locker': 'orange'
  };
  return colors[type] || 'grey';
};

onMounted(() => {
  fetchNodes();
  fetchRegions();
});
</script>

<style scoped>
.nodes-index {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
