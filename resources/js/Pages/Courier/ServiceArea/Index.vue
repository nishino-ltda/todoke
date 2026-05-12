<template>
  <CourierLayout>
    <div class="service-area-container" data-cy="courier-service-area">
      <v-row>
        <v-col cols="12" md="8">
          <v-card class="glass-card pa-0 overflow-hidden">
            <div class="pa-6">
              <h3 class="text-h6 font-weight-bold mb-2">{{ t('courier.service_area.title') }}</h3>
              <p class="text-body-2 text-grey mb-4">{{ t('courier.service_area.description') }}</p>
            </div>
            
            <div class="map-wrapper">
              <MapRegionEditor
                v-model="regionPolygon"
                height="450px"
              />
            </div>
            
            <div class="pa-6 d-flex justify-space-between align-center">
              <div class="d-flex align-center">
                <v-switch
                  v-model="isActive"
                  color="success"
                  hide-details
                  class="mr-4"
                ></v-switch>
                <span class="text-body-2 font-weight-bold">{{ t('courier.service_area.active') }}</span>
              </div>
              <v-btn
                color="primary"
                size="large"
                class="text-none px-8 font-weight-bold"
                :loading="saving"
                @click="saveArea"
                data-cy="save-area-btn"
              >
                {{ t('courier.service_area.save') }}
              </v-btn>
            </div>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card class="glass-card pa-6">
            <h4 class="text-subtitle-1 font-weight-bold mb-4">
              <v-icon color="primary" class="mr-2">mdi-information-outline</v-icon>
              Tips
            </h4>
            <v-list bg-color="transparent" density="compact">
              <v-list-item class="px-0">
                <template v-slot:prepend>
                  <v-icon size="small" color="primary">mdi-check-circle-outline</v-icon>
                </template>
                <v-list-item-title class="text-wrap text-caption">
                  {{ t('courier.service_area.hint') }}
                </v-list-item-title>
              </v-list-item>
              <v-list-item class="px-0">
                <template v-slot:prepend>
                  <v-icon size="small" color="primary">mdi-lightning-bolt-outline</v-icon>
                </template>
                <v-list-item-title class="text-wrap text-caption">
                  Smaller areas often lead to more frequent requests.
                </v-list-item-title>
              </v-list-item>
            </v-list>
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
import MapRegionEditor from '@/Components/MapRegionEditor.vue';
import { useNotificationStore } from '@/stores/notification';

const { t } = useI18n();
const notifications = useNotificationStore();

const regionPolygon = ref(null);
const isActive = ref(true);
const saving = ref(false);

const saveArea = async () => {
  if (!regionPolygon.value) {
    notifications.error(t('courier.service_area.error'));
    return;
  }

  saving.value = true;
  // Simulate API call
  setTimeout(() => {
    saving.value = false;
    notifications.success(t('courier.service_area.success'));
    localStorage.setItem('courier_service_area', JSON.stringify({
      polygon: regionPolygon.value,
      active: isActive.value
    }));
  }, 1000);
};

onMounted(() => {
  const saved = localStorage.getItem('courier_service_area');
  if (saved) {
    const data = JSON.parse(saved);
    regionPolygon.value = data.polygon;
    isActive.value = data.active;
  }
});
</script>

<style scoped>
.glass-card {
  background: rgba(255, 255, 255, 0.7) !important;
  backdrop-filter: blur(12px);
  border-radius: 24px;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.map-wrapper {
  height: 500px;
  width: 100%;
  position: relative;
  border-top: 1px solid rgba(0,0,0,0.05);
  border-bottom: 1px solid rgba(0,0,0,0.05);
}

.region-editor {
  height: 100%;
  width: 100%;
}

.service-area-container {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.98); }
  to { opacity: 1; transform: scale(1); }
}
</style>
