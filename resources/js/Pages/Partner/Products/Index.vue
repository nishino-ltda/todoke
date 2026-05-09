<template>
  <PartnerLayout>
    <div class="products-index">
      <div class="d-flex align-center justify-space-between mb-6">
        <h1 class="text-h4 font-weight-bold">{{ t('partner.products.title') }}</h1>
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          @click="openCreateModal"
          data-cy="create-product-btn"
        >
          {{ t('partner.products.add') }}
        </v-btn>
      </div>

      <DataTable
        :headers="headers"
        :items="products"
        :loading="loading"
        data-cy="products-table"
      >
        <template #item.image="{ item }">
          <v-avatar size="40" class="mr-2">
            <v-img :src="item.image || '/images/placeholder-food.jpg'"></v-img>
          </v-avatar>
        </template>

        <template #item.price="{ item }">
          <span class="font-weight-bold">{{ t('common.currency_symbol', { value: item.price.toFixed(2) }, '$' + item.price.toFixed(2)) }}</span>
        </template>

        <template #item.available="{ item }">
          <v-switch
            v-model="item.available"
            color="success"
            hide-details
            density="compact"
            @change="toggleAvailability(item)"
          ></v-switch>
        </template>

        <template #item.actions="{ item }">
          <v-btn
            variant="text"
            color="primary"
            icon="mdi-pencil"
            @click="editProduct(item)"
            data-cy="edit-product-btn"
          ></v-btn>
          <v-btn
            variant="text"
            color="error"
            icon="mdi-delete"
            @click="confirmDelete(item)"
            data-cy="delete-product-btn"
          ></v-btn>
        </template>
      </DataTable>

      <!-- Product Form Modal -->
      <AppModal
        v-model="showFormModal"
        :title="isEditing ? t('partner.products.edit') : t('partner.products.new')"
        maxWidth="600"
      >
        <v-form ref="productForm" @submit.prevent="saveProduct">
          <v-row>
            <v-col cols="12">
              <v-text-field
                v-model="form.name"
                :label="t('partner.products.name')"
                required
                :rules="[v => !!v || t('auth.validation.required', { field: t('partner.products.name') })]"
                data-cy="product-name-input"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model.number="form.price"
                :label="t('partner.products.price')"
                type="number"
                prefix="$"
                required
                :rules="[v => !!v || t('auth.validation.required', { field: t('partner.products.price') }), v => v > 0 || 'Price must be positive']"
                data-cy="product-price-input"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-select
                v-model="form.category"
                :items="categories"
                :label="t('partner.products.category')"
                data-cy="product-category-select"
              ></v-select>
            </v-col>
            <v-col cols="12">
              <v-textarea
                v-model="form.description"
                :label="t('partner.products.description')"
                rows="3"
                data-cy="product-description-input"
              ></v-textarea>
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model="form.image"
                :label="t('partner.products.image_url')"
                placeholder="https://..."
                data-cy="product-image-input"
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-select
                v-model="form.addon_ids"
                :items="availableAddons"
                item-title="name"
                item-value="id"
                :label="t('partner.products.select_addons')"
                multiple
                chips
                data-cy="product-addons-select"
              ></v-select>
            </v-col>
          </v-row>
        </v-form>
        <template #actions>
          <v-btn variant="text" @click="showFormModal = false">{{ t('partner.actions.cancel') }}</v-btn>
          <v-btn color="primary" @click="saveProduct" :loading="saving" data-cy="save-product-btn">
            {{ isEditing ? t('partner.actions.update') : t('partner.actions.create') }}
          </v-btn>
        </template>
      </AppModal>

      <!-- Delete Confirmation -->
      <AppModal v-model="showDeleteModal" :title="t('partner.actions.confirm_delete')" maxWidth="400">
        <p>{{ t('partner.products.confirm_delete', { name: selectedProduct?.name }) }}</p>
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
const products = ref([]);
const showFormModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const selectedProduct = ref(null);
const productForm = ref(null);
const availableAddons = ref([]);

const categories = ['Pizza', 'Burger', 'Dessert', 'Drinks', 'Sushi'];

const headers = computed(() => [
  { title: '', key: 'image', sortable: false, width: '60px' },
  { title: t('partner.products.name'), key: 'name' },
  { title: t('partner.products.category'), key: 'category' },
  { title: t('partner.products.price'), key: 'price' },
  { title: t('partner.products.available'), key: 'available', sortable: false },
  { title: t('partner.orders.actions'), key: 'actions', sortable: false, align: 'end' },
]);

const form = ref({
  name: '',
  price: 0,
  category: '',
  description: '',
  image: '',
  available: true,
  addon_ids: []
});

const fetchProducts = async () => {
  loading.value = true;
  try {
    const response = await partnerService.getMenu();
    products.value = response.data.map(p => ({
      ...p,
      available: p.available ?? true,
      addon_ids: p.addons?.map(a => a.id) || []
    }));
  } catch (err) {
    notifications.error(t('partner.products.error.load'));
  } finally {
    loading.value = false;
  }
};

const fetchAddons = async () => {
  try {
    const response = await partnerService.getAddons();
    availableAddons.value = response.data;
  } catch (err) {
    console.error('Failed to load addons');
  }
};

const openCreateModal = () => {
  isEditing.value = false;
  form.value = { name: '', price: 0, category: '', description: '', image: '', available: true, addon_ids: [] };
  showFormModal.value = true;
};

const editProduct = (product) => {
  isEditing.value = true;
  selectedProduct.value = product;
  form.value = { ...product };
  showFormModal.value = true;
};

const saveProduct = async () => {
  const { valid } = await productForm.value.validate();
  if (!valid) return;

  saving.value = true;
  try {
    if (isEditing.value) {
      await partnerService.updateProduct(selectedProduct.value.id, form.value);
      notifications.success(t('partner.products.success.updated'));
    } else {
      await partnerService.createProduct(form.value);
      notifications.success(t('partner.products.success.created'));
    }
    showFormModal.value = false;
    fetchProducts();
  } catch (err) {
    notifications.error(t('partner.products.error.save'));
  } finally {
    saving.value = false;
  }
};

const toggleAvailability = async (product) => {
  try {
    await partnerService.updateItemAvailability(product.id, product.available);
    notifications.success(t('partner.products.success.availability', { 
      name: product.name, 
      status: t('partner.status.' + (product.available ? 'available' : 'unavailable')) 
    }));
  } catch (err) {
    product.available = !product.available; // Revert
    notifications.error(t('partner.products.error.save'));
  }
};

const confirmDelete = (product) => {
  selectedProduct.value = product;
  showDeleteModal.value = true;
};

const doDelete = async () => {
  saving.value = true;
  try {
    await partnerService.deleteProduct(selectedProduct.value.id);
    notifications.success(t('partner.products.success.deleted'));
    showDeleteModal.value = false;
    fetchProducts();
  } catch (err) {
    notifications.error(t('partner.products.error.delete'));
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchProducts();
  fetchAddons();
});
</script>

<style scoped>
.products-index {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
