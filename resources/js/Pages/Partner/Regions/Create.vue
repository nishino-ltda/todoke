<template>
  <PartnerLayout>
    <div class="region-create-page" data-cy="partner-region-create">
      <div class="d-flex align-center mb-6">
        <v-btn
          icon="mdi-arrow-left"
          variant="text"
          class="mr-4"
          @click="router.visit(route('partner.regions.index'))"
        ></v-btn>
        <div>
          <h1 class="text-h4 font-weight-bold">{{ t('partner.regions.new') }}</h1>
          <p class="text-subtitle-1 text-grey">{{ t('partner.regions.create_subtitle', 'Define your delivery coverage area') }}</p>
        </div>
      </div>

      <v-row>
        <v-col cols="12" md="8">
          <v-card border elevation="0" class="rounded-xl">
            <v-card-text class="pa-0">
              <MapRegionEditor v-model="form.polygon" height="600px" />
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card border elevation="0" class="rounded-xl pa-8">
            <h2 class="text-h6 font-weight-bold mb-6">{{ t('partner.regions.details', 'Region Details') }}</h2>
            
            <v-form ref="formRef" @submit.prevent="submit">
              <v-text-field
                v-model="form.name"
                :label="t('partner.regions.name')"
                variant="outlined"
                class="mb-4"
                required
                :rules="[v => !!v || t('auth.validation.required', { field: t('partner.regions.name') })]"
                data-cy="region-name-input"
              ></v-text-field>

              <v-switch
                v-model="form.active"
                :label="t('partner.regions.active')"
                color="success"
                class="mb-6"
                hide-details
                data-cy="region-active-switch"
              ></v-switch>

              <v-divider class="mb-6"></v-divider>

              <v-btn
                color="primary"
                block
                size="large"
                class="rounded-lg"
                :loading="form.processing"
                @click="submit"
                data-cy="save-region-btn"
              >
                {{ t('partner.actions.create') }}
              </v-btn>
              
              <v-btn
                variant="text"
                block
                size="large"
                class="rounded-lg mt-2"
                @click="router.visit(route('partner.regions.index'))"
              >
                {{ t('partner.actions.cancel') }}
              </v-btn>
            </v-form>
          </v-card>
        </v-col>
      </v-row>
    </div>
  </PartnerLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useForm, router } from '@inertiajs/vue3';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';
import MapRegionEditor from '@/Components/MapRegionEditor.vue';
import partnerService from '@/services/partner';
import { useNotificationStore } from '@/stores/notification';

const { t } = useI18n();
const notifications = useNotificationStore();

const formRef = ref(null);
const form = useForm({
  name: '',
  polygon: { type: 'Polygon', coordinates: [[]] },
  active: true
});

const submit = async () => {
  const { valid } = await formRef.value.validate();
  if (!valid) return;

  if (form.polygon.coordinates[0].length === 0) {
    notifications.error(t('admin.regions.error.no_polygon', 'Please draw a region on the map'));
    return;
  }

  form.processing = true;
  try {
    const payload = {
      name: form.name,
      polygon: form.polygon,
      status: form.active ? 'active' : 'inactive'
    };
    await partnerService.createRegion(payload);
    notifications.success(t('partner.regions.success.created'));
    router.visit(route('partner.regions.index'));
  } catch (err) {
    notifications.error(t('partner.regions.error.save'));
  } finally {
    form.processing = false;
  }
};
</script>

<style scoped>
.region-create-page {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
