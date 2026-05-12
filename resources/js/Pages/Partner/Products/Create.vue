<template>
  <PartnerLayout>
    <div class="product-create" data-cy="partner-products-create">
      <v-row justify="center">
        <v-col cols="12" md="8">
          <v-card class="glass-card pa-6" rounded="xl">
            <h1 class="text-h4 font-weight-bold mb-6">{{ t('partner.products.new') }}</h1>
            
            <ProductForm
              ref="productForm"
              v-model="form"
              v-model:imageFile="imageFile"
              :availableAddons="availableAddons"
            />
            
            <div class="d-flex gap-3 mt-6">
              <v-btn
                color="primary"
                rounded="lg"
                size="large"
                :loading="saving"
                @click="saveProduct"
                data-cy="save-product-btn"
              >
                {{ t('partner.actions.create') }}
              </v-btn>
              <v-btn
                variant="outlined"
                color="primary"
                rounded="lg"
                size="large"
                @click="router.visit('/partner/products')"
              >
                {{ t('partner.actions.cancel') }}
              </v-btn>
            </div>
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
import ProductForm from '@/Components/ProductForm.vue';
import partnerService from '@/services/partner';
import api from '@/services/api';
import { useNotificationStore } from '@/stores/notification';

const { t } = useI18n();
const notifications = useNotificationStore();
const saving = ref(false);
const productForm = ref(null);
const availableAddons = ref([]);
const imageFile = ref(null);

const form = ref({
  name: '',
  price: 0,
  category: '',
  description: '',
  image: '',
  available: true,
  addon_ids: []
});

const fetchAddons = async () => {
  try {
    const response = await partnerService.getAddons();
    availableAddons.value = response.data;
  } catch (err) {
    console.error('Failed to load addons', err);
  }
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
    
    if (form.value.addon_ids && form.value.addon_ids.length) {
      form.value.addon_ids.forEach((id, index) => {
        formData.append(`addon_ids[${index}]`, id);
      });
    }

    await api.post('/partner/products', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    
    notifications.success(t('partner.products.success.created'));
    router.visit('/partner/products');
  } catch (err) {
    notifications.error(t('partner.products.error.save'));
  } finally {
    saving.value = false;
  }
};

onMounted(fetchAddons);
</script>

<style scoped>
.glass-card {
  background: rgba(255, 255, 255, 0.7) !important;
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07) !important;
}

.gap-3 {
  gap: 0.75rem;
}
</style>
