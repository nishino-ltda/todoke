<template>
  <PartnerLayout>
    <div class="regions-index" data-cy="regions-index">
      <div class="d-flex align-center justify-space-between mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold">{{ t('partner.regions.title') }}</h1>
          <p class="text-subtitle-1 text-grey">{{ t('partner.regions.description', 'Manage your delivery areas') }}</p>
        </div>
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          size="large"
          class="rounded-lg shadow-sm"
          @click="router.visit(route('partner.regions.create'))"
          data-cy="create-region-btn"
        >
          {{ t('partner.regions.add') }}
        </v-btn>
      </div>

      <v-card border elevation="0" class="rounded-xl overflow-hidden">
        <DataTable
          :headers="headers"
          :items="regions"
          :loading="loading"
          data-cy="regions-table"
        >
          <template #item.active="{ item }">
            <v-chip
              :color="item.status === 'active' ? 'success' : 'grey'"
              size="small"
              variant="flat"
              class="text-uppercase font-weight-bold"
            >
              {{ item.status === 'active' ? t('partner.regions.active') : t('partner.regions.inactive') }}
            </v-chip>
          </template>

          <template #item.actions="{ item }">
            <v-btn
              variant="tonal"
              color="primary"
              size="small"
              icon="mdi-pencil"
              class="mr-2"
              @click="router.visit(route('partner.regions.edit', { id: item.id }))"
              data-cy="edit-region-btn"
            ></v-btn>
            <v-btn
              variant="tonal"
              color="error"
              size="small"
              icon="mdi-delete"
              @click="confirmDelete(item)"
              data-cy="delete-region-btn"
            ></v-btn>
          </template>
        </DataTable>
      </v-card>

      <!-- Delete Confirmation -->
      <AppModal v-model="showDeleteModal" :title="t('partner.actions.confirm_delete')" maxWidth="400">
        <div class="text-center pa-4">
          <v-icon color="error" size="64" class="mb-4">mdi-alert-circle-outline</v-icon>
          <p class="text-body-1 mb-6">
            {{ t('partner.products.confirm_delete', { name: selectedRegion?.name }) }}
          </p>
        </div>
        <template #actions>
          <v-btn variant="text" block @click="showDeleteModal = false" class="mb-2">{{ t('partner.actions.cancel') }}</v-btn>
          <v-btn color="error" block @click="doDelete" :loading="saving" data-cy="confirm-delete-btn" class="rounded-lg">
            {{ t('partner.actions.delete') }}
          </v-btn>
        </template>
      </AppModal>
    </div>
  </PartnerLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { router } from '@inertiajs/vue3';
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
const showDeleteModal = ref(false);
const selectedRegion = ref(null);

const headers = computed(() => [
  { title: t('partner.regions.name'), key: 'name' },
  { title: t('partner.regions.nodes_count'), key: 'nodes_count' },
  { title: t('partner.orders.status'), key: 'active' },
  { title: t('partner.orders.actions'), key: 'actions', sortable: false, align: 'end' },
]);

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

.shadow-sm {
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05) !important;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
