<template>
  <PartnerLayout>
    <div class="variations-index" data-cy="partner-variations-index">
      <v-row class="mb-6" align="center">
        <v-col>
          <h1 class="text-h4 font-weight-bold">{{ t('partner.variations.title') || 'Product Variations' }}</h1>
          <p class="text-body-1 text-medium-emphasis">Manage sizes, colors, or other variations for this product.</p>
        </v-col>
        <v-col cols="auto">
          <v-btn
            color="primary"
            prepend-icon="mdi-plus"
            rounded="lg"
            @click="router.visit(`/partner/products/${product}/variations/create`)"
            data-cy="add-variation-btn"
          >
            {{ t('partner.variations.add') || 'Add Variation' }}
          </v-btn>
        </v-col>
      </v-row>

      <v-card class="glass-card" rounded="xl">
        <DataTable
          :headers="headers"
          :items="variations"
          :loading="loading"
          data-cy="variations-table"
        >
          <template #item.price_modifier="{ item }">
            <span :class="item.price_modifier >= 0 ? 'text-success' : 'text-error'">
              {{ item.price_modifier >= 0 ? '+' : '' }}{{ t('common.currency_symbol', { value: item.price_modifier.toFixed(2) }, '$' + item.price_modifier.toFixed(2)) }}
            </span>
          </template>

          <template #item.actions="{ item }">
            <v-btn
              variant="text"
              color="primary"
              icon="mdi-pencil"
              @click="router.visit(`/partner/products/${product}/variations/${item.id}/edit`)"
            ></v-btn>
            <v-btn
              variant="text"
              color="error"
              icon="mdi-delete"
              @click="confirmDelete(item)"
            ></v-btn>
          </template>
        </DataTable>
      </v-card>

      <!-- Delete Modal -->
      <AppModal v-model="showDeleteModal" :title="t('partner.actions.confirm_delete')" maxWidth="400">
        <p>Are you sure you want to delete this variation?</p>
        <template #actions>
          <v-btn variant="text" @click="showDeleteModal = false">{{ t('partner.actions.cancel') }}</v-btn>
          <v-btn color="error" @click="doDelete" :loading="saving">
            {{ t('partner.actions.delete') }}
          </v-btn>
        </template>
      </AppModal>
    </div>
  </PartnerLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { router } from '@inertiajs/vue3';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import AppModal from '@/Components/AppModal.vue';
import { useNotificationStore } from '@/stores/notification';

const props = defineProps({
  product: {
    type: [String, Number],
    required: true
  }
});

const { t } = useI18n();
const notifications = useNotificationStore();
const loading = ref(false);
const saving = ref(false);
const variations = ref([]);
const showDeleteModal = ref(false);
const selectedVariation = ref(null);

const headers = [
  { title: 'Name', key: 'name' },
  { title: 'Price Modifier', key: 'price_modifier' },
  { title: 'Actions', key: 'actions', align: 'end', sortable: false }
];

const fetchVariations = async () => {
  loading.value = true;
  // Placeholder as there's no backend yet
  setTimeout(() => {
    variations.value = [
      { id: 1, name: 'Small', price_modifier: 0 },
      { id: 2, name: 'Medium', price_modifier: 2.00 },
      { id: 3, name: 'Large', price_modifier: 4.00 }
    ];
    loading.value = false;
  }, 500);
};

const confirmDelete = (variation) => {
  selectedVariation.value = variation;
  showDeleteModal.value = true;
};

const doDelete = async () => {
  saving.value = true;
  // Placeholder
  setTimeout(() => {
    variations.value = variations.value.filter(v => v.id !== selectedVariation.value.id);
    notifications.success('Variation deleted');
    showDeleteModal.value = false;
    saving.value = false;
  }, 500);
};

onMounted(fetchVariations);
</script>

<style scoped>
.glass-card {
  background: rgba(255, 255, 255, 0.7) !important;
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07) !important;
}
</style>
