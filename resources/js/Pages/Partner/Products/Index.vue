<template>
  <PartnerLayout>
    <div class="products-index">
      <div class="d-flex align-center justify-space-between mb-6">
        <h1 class="text-h4 font-weight-bold">Products Management</h1>
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          @click="openCreateModal"
          data-cy="create-product-btn"
        >
          Add Product
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
          <span class="font-weight-bold">${{ item.price.toFixed(2) }}</span>
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
        :title="isEditing ? 'Edit Product' : 'Add New Product'"
        maxWidth="600"
      >
        <v-form ref="productForm" @submit.prevent="saveProduct">
          <v-row>
            <v-col cols="12">
              <v-text-field
                v-model="form.name"
                label="Product Name"
                required
                :rules="[v => !!v || 'Name is required']"
                data-cy="product-name-input"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model.number="form.price"
                label="Price"
                type="number"
                prefix="$"
                required
                :rules="[v => !!v || 'Price is required', v => v > 0 || 'Price must be positive']"
                data-cy="product-price-input"
              ></v-text-field>
            </v-col>
            <v-col cols="12" sm="6">
              <v-select
                v-model="form.category"
                :items="categories"
                label="Category"
                data-cy="product-category-select"
              ></v-select>
            </v-col>
            <v-col cols="12">
              <v-textarea
                v-model="form.description"
                label="Description"
                rows="3"
                data-cy="product-description-input"
              ></v-textarea>
            </v-col>
            <v-col cols="12">
              <v-text-field
                v-model="form.image"
                label="Image URL"
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
                label="Select Addons"
                multiple
                chips
                data-cy="product-addons-select"
              ></v-select>
            </v-col>
          </v-row>
        </v-form>
        <template #actions>
          <v-btn variant="text" @click="showFormModal = false">Cancel</v-btn>
          <v-btn color="primary" @click="saveProduct" :loading="saving" data-cy="save-product-btn">
            {{ isEditing ? 'Update' : 'Create' }}
          </v-btn>
        </template>
      </AppModal>

      <!-- Delete Confirmation -->
      <AppModal v-model="showDeleteModal" title="Confirm Delete" maxWidth="400">
        <p>Are you sure you want to delete <strong>{{ selectedProduct?.name }}</strong>?</p>
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
const products = ref([]);
const showFormModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const selectedProduct = ref(null);
const productForm = ref(null);
const availableAddons = ref([]);

const categories = ['Pizza', 'Burger', 'Dessert', 'Drinks', 'Sushi'];

const headers = [
  { title: '', key: 'image', sortable: false, width: '60px' },
  { title: 'Name', key: 'name' },
  { title: 'Category', key: 'category' },
  { title: 'Price', key: 'price' },
  { title: 'Available', key: 'available', sortable: false },
  { title: 'Actions', key: 'actions', sortable: false, align: 'end' },
];

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
    notifications.error('Failed to load products');
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
      notifications.success('Product updated successfully');
    } else {
      await partnerService.createProduct(form.value);
      notifications.success('Product created successfully');
    }
    showFormModal.value = false;
    fetchProducts();
  } catch (err) {
    notifications.error('Failed to save product');
  } finally {
    saving.value = false;
  }
};

const toggleAvailability = async (product) => {
  try {
    await partnerService.updateItemAvailability(product.id, product.available);
    notifications.success(`${product.name} is now ${product.available ? 'available' : 'unavailable'}`);
  } catch (err) {
    product.available = !product.available; // Revert
    notifications.error('Failed to update availability');
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
    notifications.success('Product deleted successfully');
    showDeleteModal.value = false;
    fetchProducts();
  } catch (err) {
    notifications.error('Failed to delete product');
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
