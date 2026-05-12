<template>
  <PartnerLayout>
    <div class="addon-edit" data-cy="partner-addons-edit">
      <v-row justify="center">
        <v-col cols="12" md="6">
          <div v-if="loading" class="d-flex justify-center pa-12">
            <v-progress-circular indeterminate color="primary"></v-progress-circular>
          </div>
          
          <v-card v-else class="glass-card pa-6" rounded="xl">
            <h1 class="text-h4 font-weight-bold mb-6">{{ t('partner.addons.edit') }}</h1>
            
            <v-form ref="addonForm" @submit.prevent="saveAddon">
              <v-text-field
                v-model="form.name"
                :label="t('partner.addons.name')"
                required
                :rules="[v => !!v || t('auth.validation.required', { field: t('partner.addons.name') })]"
                data-cy="addon-name-input"
              ></v-text-field>

              <v-text-field
                v-model.number="form.price"
                :label="t('partner.addons.price')"
                type="number"
                prefix="$"
                required
                :rules="[
                  v => v !== null && v !== undefined || t('auth.validation.required', { field: t('partner.addons.price') }),
                  v => v >= 0 || t('partner.addons.validation.price_non_negative')
                ]"
                data-cy="addon-price-input"
              ></v-text-field>

              <div class="d-flex gap-3 mt-6">
                <v-btn
                  color="primary"
                  rounded="lg"
                  size="large"
                  :loading="saving"
                  @click="saveAddon"
                  data-cy="save-addon-btn"
                >
                  {{ t('partner.actions.update') }}
                </v-btn>
                <v-btn
                  variant="outlined"
                  color="primary"
                  rounded="lg"
                  size="large"
                  @click="router.visit('/partner/addons')"
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
const saving = ref(false);
const addonForm = ref(null);

const form = ref({
  name: '',
  price: 0
});

const fetchAddon = async () => {
  loading.value = true;
  try {
    const response = await partnerService.getAddons();
    const addon = response.data.find(a => a.id == props.addonId);
    if (addon) {
      form.value = { ...addon };
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

const saveAddon = async () => {
  const { valid } = await addonForm.value.validate();
  if (!valid) return;

  saving.value = true;
  try {
    await partnerService.updateAddon(props.addonId, form.value);
    notifications.success(t('partner.addons.success.updated'));
    router.visit('/partner/addons');
  } catch (err) {
    notifications.error(t('partner.addons.error.save'));
  } finally {
    saving.value = false;
  }
};

onMounted(fetchAddon);
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
