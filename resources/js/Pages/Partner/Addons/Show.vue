<template>
  <PartnerLayout>
    <div class="addon-show" data-cy="partner-addons-show">
      <div v-if="loading" class="d-flex justify-center pa-12">
        <v-progress-circular indeterminate color="primary"></v-progress-circular>
      </div>
      
      <v-row v-else-if="addon">
        <v-col cols="12" md="6">
          <v-card class="glass-card pa-6" rounded="xl">
            <div class="d-flex justify-space-between align-start mb-6">
              <div>
                <h1 class="text-h4 font-weight-bold">{{ addon.name }}</h1>
                <p class="text-subtitle-1 text-medium-emphasis">{{ t('partner.addons.title') }}</p>
              </div>
              <div class="text-h4 font-weight-bold primary--text">
                {{ t('common.currency_symbol', { value: addon.price.toFixed(2) }, '$' + addon.price.toFixed(2)) }}
              </div>
            </div>

            <v-divider class="mb-6"></v-divider>

            <div class="d-flex gap-3">
              <v-btn
                color="primary"
                prepend-icon="mdi-pencil"
                rounded="lg"
                @click="router.visit(`/partner/addons/${addon.id}/edit`)"
                data-cy="edit-addon-btn"
              >
                {{ t('partner.actions.edit') }}
              </v-btn>
              <v-btn
                variant="outlined"
                color="primary"
                rounded="lg"
                @click="router.visit('/partner/addons')"
              >
                {{ t('common.back') || 'Back' }}
              </v-btn>
            </div>
          </v-card>
        </v-col>

        <v-col cols="12" md="6">
          <v-card class="glass-card pa-6" rounded="xl">
            <h2 class="text-h6 font-weight-bold mb-4">{{ t('partner.products.title') }}</h2>
            <p class="text-body-2 text-medium-emphasis mb-4">Products that use this addon:</p>
            
            <v-list class="bg-transparent">
              <v-list-item
                v-for="product in linkedProducts"
                :key="product.id"
                :title="product.name"
                :subtitle="product.category"
                @click="router.visit(`/partner/products/${product.id}`)"
                link
              >
                <template v-slot:prepend>
                  <v-avatar size="40" rounded="lg">
                    <v-img :src="resolveImageUrl(product.image)">
                      <template v-slot:placeholder>
                        <v-icon icon="mdi-food" size="small"></v-icon>
                      </template>
                    </v-img>
                  </v-avatar>
                </template>
              </v-list-item>
              <v-list-item v-if="!linkedProducts.length">
                <v-list-item-title class="text-medium-emphasis">No products linked</v-list-item-title>
              </v-list-item>
            </v-list>
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
  addonId: {
    type: [String, Number],
    required: true
  }
});

const { t } = useI18n();
const notifications = useNotificationStore();
const loading = ref(true);
const addon = ref(null);
const linkedProducts = ref([]);

const resolveImageUrl = (path) => {
  if (!path) return null;
  if (path.startsWith('http')) return path;
  return `/storage/${path}`;
};

const fetchData = async () => {
  loading.value = true;
  try {
    const [addonsResponse, menuResponse] = await Promise.all([
      partnerService.getAddons(),
      partnerService.getMenu()
    ]);
    
    addon.value = addonsResponse.data.find(a => a.id == props.addonId);
    if (addon.value) {
      linkedProducts.value = menuResponse.data.filter(p => 
        p.addons?.some(a => a.id == props.addonId)
      );
    } else {
      notifications.error(t('partner.addons.error.load'));
      router.visit('/partner/addons');
    }
  } catch (err) {
    notifications.error(t('partner.addons.error.load'));
  } finally {
    loading.value = false;
  }
};

onMounted(fetchData);
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
