<template>
  <CourierLayout>
    <div class="settings-container" data-cy="courier-settings">
      <v-row>
        <v-col cols="12" md="8">
          <!-- Notification Settings -->
          <v-card class="glass-card pa-6 mb-6">
            <h3 class="text-h6 font-weight-bold mb-4 d-flex align-center">
              <v-icon color="primary" class="mr-2">mdi-bell-outline</v-icon>
              {{ t('courier.settings.notifications') }}
            </h3>
            <v-list bg-color="transparent">
              <v-list-item class="px-0">
                <template v-slot:prepend>
                  <v-icon>mdi-cellphone-message</v-icon>
                </template>
                <v-list-item-title>{{ t('courier.settings.push') }}</v-list-item-title>
                <template v-slot:append>
                  <v-switch v-model="settings.push" color="primary" hide-details></v-switch>
                </template>
              </v-list-item>
              <v-list-item class="px-0">
                <template v-slot:prepend>
                  <v-icon>mdi-email-outline</v-icon>
                </template>
                <v-list-item-title>{{ t('courier.settings.email') }}</v-list-item-title>
                <template v-slot:append>
                  <v-switch v-model="settings.email" color="primary" hide-details></v-switch>
                </template>
              </v-list-item>
            </v-list>
          </v-card>

          <!-- Preferences -->
          <v-card class="glass-card pa-6">
            <h3 class="text-h6 font-weight-bold mb-4 d-flex align-center">
              <v-icon color="primary" class="mr-2">mdi-tune</v-icon>
              {{ t('courier.nav.settings') }}
            </h3>
            <v-row>
              <v-col cols="12" md="6">
                <v-select
                  v-model="settings.distance_unit"
                  :items="distanceUnits"
                  :label="t('courier.settings.distance_unit')"
                  variant="outlined"
                  density="comfortable"
                ></v-select>
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="settings.language"
                  :items="languages"
                  :label="t('courier.settings.language')"
                  variant="outlined"
                  density="comfortable"
                  @update:model-value="changeLanguage"
                ></v-select>
              </v-col>
              <v-col cols="12">
                <v-checkbox
                  v-model="settings.auto_online"
                  :label="t('courier.settings.auto_online')"
                  color="primary"
                  hide-details
                ></v-checkbox>
              </v-col>
            </v-row>

            <div class="d-flex justify-end mt-6">
              <v-btn
                color="primary"
                size="large"
                class="text-none px-8 font-weight-bold"
                :loading="saving"
                @click="saveSettings"
                data-cy="save-settings-btn"
              >
                {{ t('courier.profile.save') }}
              </v-btn>
            </div>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card class="glass-card pa-6 mb-6">
            <h3 class="text-h6 font-weight-bold mb-2">{{ t('app.title') }} Courier</h3>
            <p class="text-caption text-grey mb-4">Version 2.4.0 (Sprint 14)</p>
            <v-divider class="mb-4"></v-divider>
            <v-btn block variant="outlined" color="primary" prepend-icon="mdi-help-circle-outline" class="mb-2">
              Support Center
            </v-btn>
            <v-btn block variant="text" color="grey" size="small">
              Privacy Policy
            </v-btn>
          </v-card>
        </v-col>
      </v-row>
    </div>
  </CourierLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import CourierLayout from '@/Layouts/CourierLayout.vue';
import { useNotificationStore } from '@/stores/notification';

const { t, locale } = useI18n();
const notifications = useNotificationStore();
const saving = ref(false);

const settings = ref({
  push: true,
  email: false,
  distance_unit: 'km',
  language: locale.value,
  auto_online: true
});

const distanceUnits = [
  { title: t('courier.settings.km'), value: 'km' },
  { title: t('courier.settings.mi'), value: 'mi' },
];

const languages = [
  { title: 'Português', value: 'pt-BR' },
  { title: 'English', value: 'en' },
];

const changeLanguage = (lang) => {
  locale.value = lang;
  localStorage.setItem('locale', lang);
};

const saveSettings = async () => {
  saving.value = true;
  // Simulate API call
  setTimeout(() => {
    saving.value = false;
    notifications.success(t('courier.settings.save_success'));
  }, 800);
};

onMounted(() => {
  const saved = localStorage.getItem('courier_settings');
  if (saved) {
    settings.value = { ...settings.value, ...JSON.parse(saved) };
  }
});
</script>

<style scoped>
.glass-card {
  background: rgba(255, 255, 255, 0.7) !important;
  backdrop-filter: blur(12px);
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.settings-container {
  animation: slideIn 0.4s ease-out;
}

@keyframes slideIn {
  from { opacity: 0; transform: translateX(20px); }
  to { opacity: 1; transform: translateX(0); }
}
</style>
