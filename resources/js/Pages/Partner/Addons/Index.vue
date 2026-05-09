<template>
  <PartnerLayout>
    <div class="addons-index">
      <div class="d-flex align-center justify-space-between mb-6">
        <h1 class="text-h4 font-weight-bold">Addons Management</h1>
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          @click="openCreateModal"
          data-cy="create-addon-btn"
        >
          Add Addon
        </v-btn>
      </div>

      <DataTable
        :headers="headers"
        :items="addons"
        :loading="loading"
        data-cy="addons-table"
      >
        <template #item.price="{ item }">
          <span class="font-weight-bold">+${{ item.price.toFixed(2) }}</span>
        </template>

        <template #item.actions="{ item }">
          <v-btn
            variant="text"
            color="primary"
            icon="mdi-pencil"
            @click="editAddon(item)"
            data-cy="edit-addon-btn"
          ></v-btn>
          <v-btn
            variant="text"
            color="error"
            icon="mdi-delete"
            @click="confirmDelete(item)"
            data-cy="delete-addon-btn"
          ></v-btn>
        </template>
      </DataTable>

      <!-- Addon Form Modal -->
      <AppModal
        v-model="showFormModal"
        :title="isEditing ? 'Edit Addon' : 'Add New Addon'"
        maxWidth="500"
      >
        <v-form ref="addonForm" @submit.prevent="saveAddon">
          <v-row>
            <v-col cols="12">
              <v-text-field
                v-model="form.name"
                label="Addon Name"
                required
                :rules="[v => !!v || 'Name is required']"
                data-cy="addon-name-input"
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model.number="form.price"
                label="Additional Price"
                type="number"
                prefix="$"
                required
                :rules="[v => v >= 0 || 'Price must be non-negative']"
                data-cy="addon-price-input"
              ></v-text-field>
            </v-col>
          </v-row>
        </v-form>
        <template #actions>
          <v-btn variant="text" @click="showFormModal = false">Cancel</v-btn>
          <v-btn color="primary" @click="saveAddon" :loading="saving" data-cy="save-addon-btn">
            {{ isEditing ? 'Update' : 'Create' }}
          </v-btn>
        </template>
      </AppModal>

      <!-- Delete Confirmation -->
      <AppModal v-model="showDeleteModal" title="Confirm Delete" maxWidth="400">
        <p>Are you sure you want to delete <strong>{{ selectedAddon?.name }}</strong>?</p>
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
const addons = ref([]);
const showFormModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const selectedAddon = ref(null);
const addonForm = ref(null);

const headers = [
  { title: 'Name', key: 'name' },
  { title: 'Price', key: 'price' },
  { title: 'Actions', key: 'actions', sortable: false, align: 'end' },
];

const form = ref({
  name: '',
  price: 0
});

const fetchAddons = async () => {
  loading.value = true;
  try {
    const response = await partnerService.getAddons();
    addons.value = response.data;
  } catch (err) {
    notifications.error('Failed to load addons');
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  isEditing.value = false;
  form.value = { name: '', price: 0 };
  showFormModal.value = true;
};

const editAddon = (addon) => {
  isEditing.value = true;
  selectedAddon.value = addon;
  form.value = { ...addon };
  showFormModal.value = true;
};

const saveAddon = async () => {
  const { valid } = await addonForm.value.validate();
  if (!valid) return;

  saving.value = true;
  try {
    if (isEditing.value) {
      await partnerService.updateAddon(selectedAddon.value.id, form.value);
      notifications.success('Addon updated successfully');
    } else {
      await partnerService.createAddon(form.value);
      notifications.success('Addon created successfully');
    }
    showFormModal.value = false;
    fetchAddons();
  } catch (err) {
    notifications.error('Failed to save addon');
  } finally {
    saving.value = false;
  }
};

const confirmDelete = (addon) => {
  selectedAddon.value = addon;
  showDeleteModal.value = true;
};

const doDelete = async () => {
  saving.value = true;
  try {
    await partnerService.deleteAddon(selectedAddon.value.id);
    notifications.success('Addon deleted successfully');
    showDeleteModal.value = false;
    fetchAddons();
  } catch (err) {
    notifications.error('Failed to delete addon');
  } finally {
    saving.value = false;
  }
};

onMounted(fetchAddons);
</script>

<style scoped>
.addons-index {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
