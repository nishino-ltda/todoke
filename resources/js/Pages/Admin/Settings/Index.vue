<template>
  <AdminLayout>
    <div class="settings-page">
      <div class="d-flex align-center justify-space-between mb-6">
        <h1 class="text-h4 font-weight-bold">{{ t('admin.settings.title') }}</h1>
        <v-btn
          color="primary"
          prepend-icon="mdi-content-save"
          @click="saveSettings"
          :loading="saving"
          data-cy="save-settings-btn"
        >
          {{ t('admin.settings.save') }}
        </v-btn>
      </div>

      <v-row>
        <v-col cols="12" md="8">
          <v-card class="mb-6" elevation="1">
            <v-card-title class="pa-8 pb-0">
              <v-icon start color="primary">mdi-cog</v-icon>
              {{ t('admin.settings.general') }}
            </v-card-title>
            <v-card-text class="pa-8">
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.site_name"
                    :label="t('admin.settings.site_name')"
                    variant="outlined"
                    density="comfortable"
                    data-cy="setting-site-name"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.support_email"
                    :label="t('admin.settings.support_email')"
                    type="email"
                    variant="outlined"
                    density="comfortable"
                    data-cy="setting-support-email"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.contact_phone"
                    :label="t('admin.settings.contact_phone')"
                    variant="outlined"
                    density="comfortable"
                    data-cy="setting-contact-phone"
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-divider class="my-4"></v-divider>
                </v-col>
                <v-col cols="12" md="6">
                  <v-switch
                    v-model="form.maintenance_mode"
                    :label="t('admin.settings.maintenance_mode')"
                    color="error"
                    hide-details
                    inset
                    data-cy="setting-maintenance-mode"
                  ></v-switch>
                </v-col>
                <v-col cols="12" md="6">
                  <v-switch
                    v-model="form.allow_registration"
                    :label="t('admin.settings.allow_registration')"
                    color="success"
                    hide-details
                    inset
                    data-cy="setting-allow-registration"
                  ></v-switch>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>

          <v-card elevation="1">
            <v-card-title class="pa-8 pb-0">
              <v-icon start color="primary">mdi-cash-multiple</v-icon>
              {{ t('admin.settings.fees') }}
            </v-card-title>
            <v-card-text class="pa-8">
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.delivery_fee_base"
                    :label="t('admin.settings.base_fee')"
                    type="number"
                    prefix="R$"
                    variant="outlined"
                    density="comfortable"
                    data-cy="setting-base-fee"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.delivery_fee_per_km"
                    :label="t('admin.settings.km_fee')"
                    type="number"
                    prefix="R$"
                    variant="outlined"
                    density="comfortable"
                    data-cy="setting-km-fee"
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card elevation="1" class="bg-blue-lighten-5">
            <v-card-title class="text-subtitle-1 font-weight-bold">
              System Info
            </v-card-title>
            <v-card-text>
              <div class="d-flex justify-space-between mb-2">
                <span>Version</span>
                <span class="font-weight-bold">v1.0.0-mvp</span>
              </div>
              <div class="d-flex justify-space-between mb-2">
                <span>Environment</span>
                <span class="font-weight-bold text-uppercase">Production</span>
              </div>
              <div class="d-flex justify-space-between mb-2">
                <span>Last Updated</span>
                <span class="font-weight-bold">2024-05-09</span>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import adminService from '@/services/admin';
import { useNotificationStore } from '@/stores/notification';

const { t } = useI18n();
const notifications = useNotificationStore();
const loading = ref(false);
const saving = ref(false);

const form = ref({
  site_name: '',
  maintenance_mode: false,
  allow_registration: true,
  delivery_fee_base: 0,
  delivery_fee_per_km: 0,
  support_email: '',
  contact_phone: ''
});

const fetchSettings = async () => {
  loading.value = true;
  try {
    const response = await adminService.getSettings();
    form.value = { ...response.data };
  } catch (err) {
    notifications.error(t('admin.settings.notifications.load_failed'));
  } finally {
    loading.value = false;
  }
};

const saveSettings = async () => {
  saving.value = true;
  try {
    await adminService.updateSettings(form.value);
    notifications.success(t('admin.settings.notifications.update_success'));
  } catch (err) {
    notifications.error(t('admin.settings.notifications.update_failed'));
  } finally {
    saving.value = false;
  }
};

onMounted(fetchSettings);
</script>

<style scoped>
.settings-page {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
