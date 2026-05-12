<template>
  <PartnerLayout>
    <div class="order-batch-create" data-cy="partner-order-batch-create-page">
      <v-row>
        <v-col cols="12">
          <v-card class="glass-card mb-6" rounded="xl">
            <v-card-text class="pa-6">
              <div class="d-flex align-center justify-space-between mb-6">
                <div>
                  <h1 class="text-h4 font-weight-bold">{{ t('partner.orders.batch_create') || 'Batch Create Orders' }}</h1>
                  <p class="text-body-2 text-medium-emphasis">Upload a CSV or enter multiple orders manually.</p>
                </div>
                <div class="d-flex gap-2">
                  <v-btn
                    prepend-icon="mdi-upload"
                    color="primary"
                    variant="tonal"
                    rounded="lg"
                    @click="triggerFileInput"
                  >
                    {{ t('common.upload_csv') || 'Upload CSV' }}
                  </v-btn>
                  <v-btn
                    prepend-icon="mdi-plus"
                    color="primary"
                    rounded="lg"
                    @click="addRow"
                  >
                    {{ t('common.add_row') || 'Add Row' }}
                  </v-btn>
                </div>
              </div>

              <input
                type="file"
                ref="fileInput"
                accept=".csv"
                class="d-none"
                @change="handleFileUpload"
              />

              <v-table class="bg-transparent">
                <thead>
                  <tr>
                    <th>{{ t('partner.profile.name') }}</th>
                    <th>{{ t('partner.profile.phone') }}</th>
                    <th>{{ t('partner.profile.address') }}</th>
                    <th width="200">{{ t('partner.orders.items') }}</th>
                    <th width="80"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(order, index) in batchOrders" :key="index">
                    <td>
                      <v-text-field
                        v-model="order.name"
                        variant="underlined"
                        density="compact"
                        hide-details
                      ></v-text-field>
                    </td>
                    <td>
                      <v-text-field
                        v-model="order.phone"
                        variant="underlined"
                        density="compact"
                        hide-details
                      ></v-text-field>
                    </td>
                    <td>
                      <v-text-field
                        v-model="order.address"
                        variant="underlined"
                        density="compact"
                        hide-details
                      ></v-text-field>
                    </td>
                    <td>
                      <v-select
                        v-model="order.product_ids"
                        :items="products"
                        item-title="name"
                        item-value="id"
                        multiple
                        chips
                        variant="underlined"
                        density="compact"
                        hide-details
                      ></v-select>
                    </td>
                    <td class="text-right">
                      <v-btn icon="mdi-delete" variant="text" color="error" size="small" @click="removeRow(index)"></v-btn>
                    </td>
                  </tr>
                </tbody>
              </v-table>

              <div class="d-flex justify-end mt-8">
                <v-btn
                  color="primary"
                  size="large"
                  rounded="xl"
                  class="px-12 elevation-4"
                  :loading="saving"
                  :disabled="!batchOrders.length"
                  @click="submitBatch"
                  data-cy="submit-batch-btn"
                >
                  {{ t('partner.actions.create_all') || 'Create All Orders' }}
                </v-btn>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>
  </PartnerLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { router } from '@inertiajs/vue3';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';
import partnerService from '@/services/partner';
import api from '@/services/api';
import { useNotificationStore } from '@/stores/notification';

const { t } = useI18n();
const notifications = useNotificationStore();
const saving = ref(false);
const fileInput = ref(null);
const products = ref([]);
const batchOrders = ref([]);

const triggerFileInput = () => {
  fileInput.value.click();
};

const addRow = () => {
  batchOrders.value.push({
    name: '',
    phone: '',
    address: '',
    product_ids: []
  });
};

const removeRow = (index) => {
  batchOrders.value.splice(index, 1);
};

const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = (e) => {
    const text = e.target.result;
    const lines = text.split('\n');
    const newOrders = [];
    
    // Simple CSV parsing (skip header)
    for (let i = 1; i < lines.length; i++) {
      const parts = lines[i].split(',');
      if (parts.length >= 3) {
        newOrders.push({
          name: parts[0].trim(),
          phone: parts[1].trim(),
          address: parts[2].trim(),
          product_ids: []
        });
      }
    }
    
    batchOrders.value = [...batchOrders.value, ...newOrders];
  };
  reader.readAsText(file);
};

const fetchProducts = async () => {
  try {
    const response = await partnerService.getMenu();
    products.value = response.data;
  } catch (err) {
    console.error('Failed to load products', err);
  }
};

const submitBatch = async () => {
  saving.value = true;
  try {
    await api.post('/partner/orders/batch', { orders: batchOrders.value });
    notifications.success(t('partner.orders.status_updated'));
    router.visit('/partner/orders');
  } catch (err) {
    notifications.error(t('partner.orders.failed_update'));
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchProducts();
  addRow(); // Start with one empty row
});
</script>

<style scoped>
.glass-card {
  background: rgba(255, 255, 255, 0.7) !important;
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07) !important;
}

.gap-2 {
  gap: 0.5rem;
}
</style>
