<template>
  <PartnerLayout>
    <div class="variation-edit" data-cy="partner-variations-edit">
      <v-row justify="center">
        <v-col cols="12" md="6">
          <div v-if="loading" class="d-flex justify-center pa-12">
            <v-progress-circular indeterminate color="primary"></v-progress-circular>
          </div>
          
          <v-card v-else class="glass-card pa-6" rounded="xl">
            <h1 class="text-h4 font-weight-bold mb-6">{{ t('partner.actions.edit') }} Variation</h1>
            
            <v-form ref="variationForm" @submit.prevent="saveVariation">
              <v-text-field
                v-model="form.name"
                label="Variation Name"
                required
                variant="outlined"
              ></v-text-field>

              <v-text-field
                v-model.number="form.price_modifier"
                label="Price Modifier"
                type="number"
                prefix="$"
                variant="outlined"
                class="mt-4"
              ></v-text-field>

              <div class="d-flex gap-3 mt-8">
                <v-btn
                  color="primary"
                  rounded="lg"
                  size="large"
                  :loading="saving"
                  @click="saveVariation"
                  data-cy="save-variation-btn"
                >
                  {{ t('partner.actions.update') }}
                </v-btn>
                <v-btn
                  variant="outlined"
                  color="primary"
                  rounded="lg"
                  size="large"
                  @click="router.visit(`/partner/products/${product}/variations`)"
                >
                  {{ t('partner.actions.cancel') }}
                </v-btn>
              </div>
            </v-form>
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
import { useNotificationStore } from '@/stores/notification';

const props = defineProps({
  product: {
    type: [String, Number],
    required: true
  },
  variation: {
    type: [String, Number],
    required: true
  }
});

const { t } = useI18n();
const notifications = useNotificationStore();
const loading = ref(true);
const saving = ref(false);
const variationForm = ref(null);

const form = ref({
  name: '',
  price_modifier: 0
});

const fetchVariation = async () => {
  loading.value = true;
  // Placeholder
  setTimeout(() => {
    form.value = {
      name: 'Medium',
      price_modifier: 2.00
    };
    loading.value = false;
  }, 500);
};

const saveVariation = async () => {
  const { valid } = await variationForm.value.validate();
  if (!valid) return;

  saving.value = true;
  // Placeholder
  setTimeout(() => {
    notifications.success('Variation updated successfully');
    router.visit(`/partner/products/${props.product}/variations`);
    saving.value = false;
  }, 1000);
};

onMounted(fetchVariation);
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
