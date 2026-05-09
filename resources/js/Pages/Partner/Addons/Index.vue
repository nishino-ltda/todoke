<template>
  <PartnerLayout>
    <div class="addons-index">
      <div class="d-flex align-center justify-space-between mb-6">
        <h1 class="text-h4 font-weight-bold">{{ t('partner.addons.title') }}</h1>
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          @click="openCreateModal"
          data-cy="create-addon-btn"
        >
          {{ t('partner.addons.add') }}
        </v-btn>
      </div>

      <DataTable
        :headers="headers"
        :items="addons"
        :loading="loading"
        data-cy="addons-table"
      >
        <template #item.price="{ item }">
          <span class="font-weight-bold">+{{ t('common.currency_symbol', { value: item.price.toFixed(2) }, '$' + item.price.toFixed(2)) }}</span>
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
        :title="isEditing ? t('partner.addons.edit') : t('partner.addons.new')"
        maxWidth="500"
      >
        <v-form ref="addonForm" @submit.prevent="saveAddon">
          <v-row>
            <v-col cols="12">
              <v-text-field
                v-model="form.name"
                :label="t('partner.addons.name')"
                required
                :rules="[v => !!v || t('auth.validation.required', { field: t('partner.addons.name') })]"
                data-cy="addon-name-input"
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model.number="form.price"
                :label="t('partner.addons.price')"
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
          <v-btn variant="text" @click="showFormModal = false">{{ t('partner.actions.cancel') }}</v-btn>
          <v-btn color="primary" @click="saveAddon" :loading="saving" data-cy="save-addon-btn">
            {{ isEditing ? t('partner.actions.update') : t('partner.actions.create') }}
          </v-btn>
        </template>
      </AppModal>

      <!-- Delete Confirmation -->
      <AppModal v-model="showDeleteModal" :title="t('partner.actions.confirm_delete')" maxWidth="400">
        <p>{{ t('partner.products.confirm_delete', { name: selectedAddon?.name }) }}</p>
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
const addons = ref([]);
const showFormModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const selectedAddon = ref(null);
const addonForm = ref(null);

const headers = computed(() => [
  { title: t('partner.addons.name'), key: 'name' },
  { title: t('partner.addons.price'), key: 'price' },
  { title: t('partner.orders.actions'), key: 'actions', sortable: false, align: 'end' },
]);

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
    notifications.error(t('partner.addons.error.load'));
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
      notifications.success(t('partner.addons.success.updated'));
    } else {
      await partnerService.createAddon(form.value);
      notifications.success(t('partner.addons.success.created'));
    }
    showFormModal.value = false;
    fetchAddons();
  } catch (err) {
    notifications.error(t('partner.addons.error.save'));
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
    notifications.success(t('partner.addons.success.deleted'));
    showDeleteModal.value = false;
    fetchAddons();
  } catch (err) {
    notifications.error(t('partner.addons.error.delete'));
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
