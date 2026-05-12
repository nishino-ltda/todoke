<template>
  <PartnerLayout>
    <div class="product-show" data-cy="partner-products-show">
      <div v-if="loading" class="d-flex justify-center pa-12">
        <v-progress-circular indeterminate color="primary"></v-progress-circular>
      </div>
      
      <v-row v-else-if="product">
        <v-col cols="12" md="5">
          <v-card class="glass-card overflow-hidden" rounded="xl">
            <v-img
              :src="resolveImageUrl(product.image)"
              height="400"
              cover
            >
              <template v-slot:placeholder>
                <div class="d-flex align-center justify-center fill-height bg-grey-lighten-2">
                  <v-icon size="64" color="grey">mdi-food</v-icon>
                </div>
              </template>
            </v-img>
          </v-card>
        </v-col>
        
        <v-col cols="12" md="7">
          <v-card class="glass-card pa-6" rounded="xl">
            <div class="d-flex justify-space-between align-start mb-4">
              <div>
                <v-chip color="primary" size="small" class="mb-2">{{ product.category }}</v-chip>
                <h1 class="text-h4 font-weight-bold">{{ product.name }}</h1>
              </div>
              <div class="text-right">
                <div class="text-h4 font-weight-bold primary--text">
                  {{ t('common.currency_symbol', { value: product.price.toFixed(2) }, '$' + product.price.toFixed(2)) }}
                </div>
                <v-chip
                  :color="product.available ? 'success' : 'error'"
                  size="small"
                  class="mt-2"
                >
                  {{ product.available ? t('partner.status.available') : t('partner.status.unavailable') }}
                </v-chip>
              </div>
            </div>

            <p class="text-body-1 text-medium-emphasis mb-6">
              {{ product.description || t('cart.no_description') }}
            </p>

            <v-divider class="mb-6"></v-divider>

            <h3 class="text-subtitle-1 font-weight-bold mb-3">{{ t('partner.products.select_addons') }}</h3>
            <div v-if="product.addons?.length" class="d-flex flex-wrap gap-2 mb-6">
              <v-chip
                v-for="addon in product.addons"
                :key="addon.id"
                variant="tonal"
                color="secondary"
              >
                {{ addon.name }} (+{{ t('common.currency_symbol', { value: addon.price.toFixed(2) }, '$' + addon.price.toFixed(2)) }})
              </v-chip>
            </div>
            <p v-else class="text-body-2 text-medium-emphasis italic mb-6">
              {{ t('partner.addons.empty') }}
            </p>

            <div class="d-flex gap-3">
              <v-btn
                color="primary"
                prepend-icon="mdi-pencil"
                rounded="lg"
                @click="router.visit(`/partner/products/${product.id}/edit`)"
                data-cy="edit-product-btn"
              >
                {{ t('partner.actions.edit') }}
              </v-btn>
              <v-btn
                variant="outlined"
                color="primary"
                rounded="lg"
                @click="router.visit('/partner/products')"
              >
                {{ t('partner.orders.back_to_orders') }}
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
import partnerService from '@/services/partner';
import { useNotificationStore } from '@/stores/notification';

const props = defineProps({
  productId: {
    type: [String, Number],
    required: true
  }
});

const { t } = useI18n();
const notifications = useNotificationStore();
const loading = ref(true);
const product = ref(null);

const resolveImageUrl = (path) => {
  if (!path) return null;
  if (path.startsWith('http')) return path;
  return `/storage/${path}`;
};

const fetchProduct = async () => {
  loading.value = true;
  try {
    // In a real app, we might have a specific getProduct endpoint
    // For now, we'll filter from getMenu or use a generic API if available
    const response = await partnerService.getMenu();
    product.value = response.data.find(p => p.id == props.productId);
    if (!product.value) {
      notifications.error(t('partner.products.error.load'));
    }
  } catch (err) {
    notifications.error(t('partner.products.error.load'));
  } finally {
    loading.value = false;
  }
};

onMounted(fetchProduct);
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
.gap-3 {
  gap: 0.75rem;
}
</style>
