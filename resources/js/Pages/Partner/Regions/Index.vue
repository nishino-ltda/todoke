<template>
  <PartnerLayout>
    <div class="regions-index">
      <div class="d-flex align-center justify-space-between mb-6">
        <h1 class="text-h4 font-weight-bold">Regions Management</h1>
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          @click="openCreateModal"
          data-cy="create-region-btn"
        >
          Add Region
        </v-btn>
      </div>

      <DataTable
        :headers="headers"
        :items="regions"
        :loading="loading"
        data-cy="regions-table"
      >
        <template #item.active="{ item }">
          <v-chip
            :color="item.active ? 'success' : 'grey'"
            size="small"
            class="text-uppercase font-weight-bold"
          >
            {{ item.active ? 'Active' : 'Inactive' }}
          </v-chip>
        </template>

        <template #item.actions="{ item }">
          <v-btn
            variant="text"
            color="primary"
            icon="mdi-pencil"
            @click="editRegion(item)"
            data-cy="edit-region-btn"
          ></v-btn>
          <v-btn
            variant="text"
            color="error"
            icon="mdi-delete"
            @click="confirmDelete(item)"
            data-cy="delete-region-btn"
          ></v-btn>
        </template>
      </DataTable>

      <!-- Region Form Modal -->
      <AppModal
        v-model="showFormModal"
        :title="isEditing ? 'Edit Region' : 'Add New Region'"
        maxWidth="600"
      >
        <v-form ref="regionForm" @submit.prevent="saveRegion">
          <v-row>
            <v-col cols="12">
              <v-text-field
                v-model="form.name"
                label="Region Name"
                required
                :rules="[v => !!v || 'Name is required']"
                data-cy="region-name-input"
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model="form.coordinates"
                label="Coordinates (JSON Boundary)"
                placeholder='[[lat, lng], ...]'
                data-cy="region-coordinates-input"
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-switch
                v-model="form.active"
                label="Active"
                color="success"
                data-cy="region-active-switch"
              ></v-switch>
            </v-col>
          </v-row>
        </v-form>
        <template #actions>
          <v-btn variant="text" @click="showFormModal = false">Cancel</v-btn>
          <v-btn color="primary" @click="saveRegion" :loading="saving" data-cy="save-region-btn">
            {{ isEditing ? 'Update' : 'Create' }}
          </v-btn>
        </template>
      </AppModal>

      <!-- Delete Confirmation -->
      <AppModal v-model="showDeleteModal" title="Confirm Delete" maxWidth="400">
        <p>Are you sure you want to delete <strong>{{ selectedRegion?.name }}</strong>?</p>
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
const regions = ref([]);
const showFormModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const selectedRegion = ref(null);
const regionForm = ref(null);

const headers = [
  { title: 'Name', key: 'name' },
  { title: 'Nodes Count', key: 'nodes_count' },
  { title: 'Status', key: 'active' },
  { title: 'Actions', key: 'actions', sortable: false, align: 'end' },
];

const form = ref({
  name: '',
  coordinates: '',
  active: true
});

const fetchRegions = async () => {
  loading.value = true;
  try {
    const response = await partnerService.getRegions();
    regions.value = response.data;
  } catch (err) {
    notifications.error('Failed to load regions');
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  isEditing.value = false;
  form.value = { name: '', coordinates: '', active: true };
  showFormModal.value = true;
};

const editRegion = (region) => {
  isEditing.value = true;
  selectedRegion.value = region;
  form.value = { ...region };
  showFormModal.value = true;
};

const saveRegion = async () => {
  const { valid } = await regionForm.value.validate();
  if (!valid) return;

  saving.value = true;
  try {
    if (isEditing.value) {
      await partnerService.updateRegion(selectedRegion.value.id, form.value);
      notifications.success('Region updated successfully');
    } else {
      await partnerService.createRegion(form.value);
      notifications.success('Region created successfully');
    }
    showFormModal.value = false;
    fetchRegions();
  } catch (err) {
    notifications.error('Failed to save region');
  } finally {
    saving.value = false;
  }
};

const confirmDelete = (region) => {
  selectedRegion.value = region;
  showDeleteModal.value = true;
};

const doDelete = async () => {
  saving.value = true;
  try {
    await partnerService.deleteRegion(selectedRegion.value.id);
    notifications.success('Region deleted successfully');
    showDeleteModal.value = false;
    fetchRegions();
  } catch (err) {
    notifications.error('Failed to delete region');
  } finally {
    saving.value = false;
  }
};

onMounted(fetchRegions);
</script>

<style scoped>
.regions-index {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
