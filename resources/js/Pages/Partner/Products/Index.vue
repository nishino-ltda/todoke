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
          <v-avatar size="40" rounded="lg" class="mr-2 elevation-1">
            <v-img :src="resolveImageUrl(item.image)">
              <template v-slot:placeholder>
                <v-icon icon="mdi-food" size="small"></v-icon>
              </template>
            </v-img>
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
        maxWidth="700"
      >
        <ProductForm
          ref="productForm"
          v-model="form"
          v-model:imageFile="imageFile"
          :availableAddons="availableAddons"
          showAvailable
        />
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
import ProductForm from '@/Components/ProductForm.vue';
import partnerService from '@/services/partner';
import api from '@/services/api';
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
const imageFile = ref(null);

const resolveImageUrl = (path) => {
  if (!path) return null;
  if (path.startsWith('http')) return path;
  return `/storage/${path}`;
};

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
    const formData = new FormData();
    formData.append('name', form.value.name);
    formData.append('price', form.value.price);
    formData.append('category', form.value.category);
    formData.append('description', form.value.description || '');
    formData.append('available', form.value.available ? 1 : 0);
    
    if (imageFile.value) {
      formData.append('image', imageFile.value);
    }
    
    // Addons
    if (form.value.addon_ids && form.value.addon_ids.length) {
      form.value.addon_ids.forEach((id, index) => {
        formData.append(`addon_ids[${index}]`, id);
      });
    }

    if (isEditing.value) {
      formData.append('_method', 'PUT');
      await api.post(`/partner/products/${selectedProduct.value.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      notifications.success(t('partner.products.success.updated'));
    } else {
      await api.post('/partner/products', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      notifications.success(t('partner.products.success.created'));
    }
    showFormModal.value = false;
    imageFile.value = null;
    fetchProducts();
  } catch (err) {
    notifications.error(t('partner.products.error.save'));
    console.error(err);
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
