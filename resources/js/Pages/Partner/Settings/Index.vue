<template>
  <PartnerLayout>
    <div class="partner-settings" data-cy="partner-settings-index">
      <v-row>
        <v-col cols="12" md="8">
          <!-- Business Status -->
          <v-card class="glass-card mb-6" rounded="xl">
            <v-card-text class="pa-6">
              <div class="d-flex align-center justify-space-between mb-4">
                <div>
                  <h2 class="text-h6 font-weight-bold mb-1">{{ t('partner.settings.status') }}</h2>
                  <p class="text-body-2 text-medium-emphasis">{{ t('partner.settings.status_description') }}</p>
                </div>
                <v-switch
                  v-model="settings.is_open"
                  :color="settings.is_open ? 'success' : 'error'"
                  :label="settings.is_open ? t('partner.settings.open') : t('partner.settings.closed')"
                  hide-details
                  inset
                ></v-switch>
              </div>
            </v-card-text>
          </v-card>

          <!-- Delivery Settings -->
          <v-card class="glass-card mb-6" rounded="xl">
            <v-card-text class="pa-6">
              <h2 class="text-h6 font-weight-bold mb-4">{{ t('partner.settings.delivery_fee') }}</h2>
              <v-row>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="settings.delivery_fee"
                    :label="t('partner.settings.delivery_fee')"
                    prefix="$"
                    type="number"
                    variant="outlined"
                    density="comfortable"
                    hide-details="auto"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="settings.prep_time"
                    :label="t('partner.settings.prep_time')"
                    suffix="min"
                    type="number"
                    variant="outlined"
                    density="comfortable"
                    hide-details="auto"
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>

          <!-- Opening Hours -->
          <v-card class="glass-card mb-6" rounded="xl">
            <v-card-text class="pa-6">
              <h2 class="text-h6 font-weight-bold mb-4">{{ t('partner.settings.opening_hours') }}</h2>
              <v-list class="bg-transparent">
                <v-list-item v-for="(day, index) in daysOfWeek" :key="index" class="px-0">
                  <v-row align="center" no-gutters>
                    <v-col cols="4">
                      <span class="font-weight-medium">{{ t(`common.days.${day}`) }}</span>
                    </v-col>
                    <v-col cols="8">
                      <div class="d-flex align-center gap-2">
                        <v-text-field
                          v-model="settings.opening_hours[day].open"
                          type="time"
                          variant="outlined"
                          density="compact"
                          hide-details
                        ></v-text-field>
                        <span>-</span>
                        <v-text-field
                          v-model="settings.opening_hours[day].close"
                          type="time"
                          variant="outlined"
                          density="compact"
                          hide-details
                        ></v-text-field>
                        <v-checkbox
                          v-model="settings.opening_hours[day].closed"
                          :label="t('partner.settings.closed')"
                          hide-details
                          density="compact"
                          class="ml-2"
                        ></v-checkbox>
                      </div>
                    </v-col>
                  </v-row>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <!-- Payment Methods -->
          <v-card class="glass-card mb-6" rounded="xl">
            <v-card-text class="pa-6">
              <h2 class="text-h6 font-weight-bold mb-4">{{ t('partner.settings.payment_methods') }}</h2>
              <v-checkbox
                v-for="method in paymentMethods"
                :key="method.value"
                v-model="settings.payment_methods"
                :value="method.value"
                :label="method.label"
                color="primary"
                hide-details
                density="comfortable"
              ></v-checkbox>
            </v-card-text>
          </v-card>

          <v-btn
            color="primary"
            block
            size="large"
            rounded="xl"
            :loading="saving"
            @click="saveSettings"
            data-cy="save-settings-btn"
            class="elevation-4"
          >
            {{ t('partner.actions.save') }}
          </v-btn>
        </v-col>
      </v-row>
    </div>
  </PartnerLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';
import partnerService from '@/services/partner';
import { useNotificationStore } from '@/stores/notification';

const { t } = useI18n();
const notifications = useNotificationStore();
const loading = ref(true);
const saving = ref(false);

const daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
const paymentMethods = [
  { label: 'Credit Card (Online)', value: 'card_online' },
  { label: 'Debit Card (Online)', value: 'debit_online' },
  { label: 'Cash on Delivery', value: 'cash' },
  { label: 'Card on Delivery', value: 'card_delivery' },
  { label: 'Pix', value: 'pix' }
];

const settings = ref({
  is_open: true,
  delivery_fee: 0,
  prep_time: 30,
  payment_methods: [],
  opening_hours: daysOfWeek.reduce((acc, day) => {
    acc[day] = { open: '08:00', close: '22:00', closed: false };
    return acc;
  }, {})
});

const fetchSettings = async () => {
  loading.value = true;
  try {
    const response = await partnerService.getSettings();
    if (response.data) {
      settings.value = { ...settings.value, ...response.data };
    }
  } catch (err) {
    // If not found, we keep defaults
    console.error('Failed to load settings', err);
  } finally {
    loading.value = false;
  }
};

const saveSettings = async () => {
  saving.value = true;
  try {
    await partnerService.updateSettings(settings.value);
    notifications.success(t('partner.settings.save_success'));
  } catch (err) {
    notifications.error(t('partner.settings.save_error'));
  } finally {
    saving.value = false;
  }
};

onMounted(fetchSettings);
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
