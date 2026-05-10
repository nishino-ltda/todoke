<template>
  <CourierLayout>
    <div class="mb-6">
      <h1 class="text-h4 font-weight-bold mb-2">{{ t('courier.service_area.title', 'My Service Area') }}</h1>
      <p class="text-subtitle-1 text-grey">
        {{ t('courier.service_area.description', 'Define the area where you want to receive delivery requests.') }}
      </p>
    </div>

    <v-row>
      <v-col cols="12" md="9">
        <v-card border elevation="0" class="rounded-xl overflow-hidden">
          <v-card-text class="pa-0">
            <MapRegionEditor v-model="form.polygon" height="600px" />
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="3">
        <v-card border elevation="0" class="rounded-xl pa-6 bg-white">
          <h3 class="text-h6 font-weight-bold mb-4">{{ t('courier.service_area.settings', 'Settings') }}</h3>
          
          <v-form @submit.prevent="submit">
            <v-switch
              v-model="form.is_active"
              :label="t('courier.service_area.active', 'Active')"
              color="success"
              class="mb-6"
              hide-details
            ></v-switch>

            <v-divider class="mb-6"></v-divider>

            <v-btn
              color="primary"
              block
              size="large"
              class="rounded-lg"
              :loading="form.processing"
              @click="submit"
              data-cy="save-service-area-btn"
            >
              {{ t('partner.actions.save', 'Save Area') }}
            </v-btn>
          </v-form>

          <v-alert
            v-if="!form.polygon.coordinates[0].length"
            type="info"
            variant="tonal"
            class="mt-6 text-caption"
            density="compact"
          >
            {{ t('courier.service_area.hint', 'You need to draw a polygon on the map to define your service area.') }}
          </v-alert>
        </v-card>
      </v-col>
    </v-row>
  </CourierLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';
import CourierLayout from '@/Layouts/CourierLayout.vue';
import MapRegionEditor from '@/Components/MapRegionEditor.vue';
import { useNotificationStore } from '@/stores/notification';

const { t } = useI18n();
const notifications = useNotificationStore();

const props = defineProps({
  serviceArea: Object
});

const form = useForm({
  polygon: props.serviceArea?.polygon || { type: 'Polygon', coordinates: [[]] },
  is_active: props.serviceArea ? !!props.serviceArea.is_active : true
});

const submit = () => {
  if (form.polygon.coordinates[0].length === 0) {
    notifications.error(t('admin.regions.error.no_polygon', 'Please draw a region on the map'));
    return;
  }

  form.post(route('courier.service-area.update'), {
    onSuccess: () => {
      notifications.success(t('courier.service_area.success', 'Service area updated successfully'));
    },
    onError: () => {
      notifications.error(t('courier.service_area.error', 'Failed to update service area'));
    }
  });
};
</script>

<style scoped>
.shadow-sm {
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05) !important;
}
</style>
