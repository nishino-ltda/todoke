<template>
  <PartnerLayout>
    <div class="regions-index">
      <div class="d-flex align-center justify-space-between mb-6">
        <h1 class="text-h4 font-weight-bold">{{ t('partner.regions.title') }}</h1>
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          @click="openCreateModal"
          data-cy="create-region-btn"
        >
          {{ t('partner.regions.add') }}
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
            {{ item.active ? t('partner.regions.active') : t('partner.regions.inactive') }}
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
        :title="isEditing ? t('partner.regions.edit') : t('partner.regions.new')"
        maxWidth="600"
      >
        <v-form ref="regionForm" @submit.prevent="saveRegion">
          <v-row>
            <v-col cols="12">
              <v-text-field
                v-model="form.name"
                :label="t('partner.regions.name')"
                required
                :rules="[v => !!v || t('auth.validation.required', { field: t('partner.regions.name') })]"
                data-cy="region-name-input"
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model="form.coordinates"
                :label="t('partner.regions.coordinates')"
                placeholder='[[lat, lng], ...]'
                data-cy="region-coordinates-input"
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-switch
                v-model="form.active"
                :label="t('partner.regions.active')"
                color="success"
                data-cy="region-active-switch"
              ></v-switch>
            </v-col>
          </v-row>
        </v-form>
        <template #actions>
          <v-btn variant="text" @click="showFormModal = false">{{ t('partner.actions.cancel') }}</v-btn>
          <v-btn color="primary" @click="saveRegion" :loading="saving" data-cy="save-region-btn">
            {{ isEditing ? t('partner.actions.update') : t('partner.actions.create') }}
          </v-btn>
        </template>
      </AppModal>

      <!-- Delete Confirmation -->
      <AppModal v-model="showDeleteModal" :title="t('partner.actions.confirm_delete')" maxWidth="400">
        <p>{{ t('partner.products.confirm_delete', { name: selectedRegion?.name }) }}</p>
        <template #actions>
          <v-btn variant="text" @click="showDeleteModal = false">{{ t('partner.actions.cancel') }}</v-btn>
          <v-btn color="error" @click="doDelete" :loading="saving" data-cy="confirm-delete-btn">{{ t('partner.actions.delete') }}</v-btn>
        </template>
      </AppModal>
    </div>
  </PartnerLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import AppModal from '@/Components/AppModal.vue';
import partnerService from '@/services/partner';
import { useNotificationStore } from '@/stores/notification';

const { t } = useI18n();
const notifications = useNotificationStore();
const loading = ref(false);
const saving = ref(false);
const regions = ref([]);
const showFormModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const selectedRegion = ref(null);
const regionForm = ref(null);

const headers = computed(() => [
  { title: t('partner.regions.name'), key: 'name' },
  { title: t('partner.regions.nodes_count'), key: 'nodes_count' },
  { title: t('partner.orders.status'), key: 'active' },
  { title: t('partner.orders.actions'), key: 'actions', sortable: false, align: 'end' },
]);

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
    notifications.error(t('partner.regions.error.load'));
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
      notifications.success(t('partner.regions.success.updated'));
    } else {
      await partnerService.createRegion(form.value);
      notifications.success(t('partner.regions.success.created'));
    }
    showFormModal.value = false;
    fetchRegions();
  } catch (err) {
    notifications.error(t('partner.regions.error.save'));
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
    notifications.success(t('partner.regions.success.deleted'));
    showDeleteModal.value = false;
    fetchRegions();
  } catch (err) {
    notifications.error(t('partner.regions.error.delete'));
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
