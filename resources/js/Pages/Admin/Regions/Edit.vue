<template>
  <AdminLayout>
    <div class="region-edit-page" data-cy="admin-region-edit">
      <div class="d-flex align-center mb-6">
        <v-btn
          icon="mdi-arrow-left"
          variant="text"
          class="mr-4"
          @click="router.visit(route('admin.regions.index'))"
        ></v-btn>
        <div>
          <h1 class="text-h4 font-weight-bold">{{ t('admin.regions.edit') }}</h1>
          <p class="text-subtitle-1 text-grey">{{ region.name }}</p>
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
            <h2 class="text-h6 font-weight-bold mb-6">{{ t('admin.regions.details', 'Region Details') }}</h2>
            
            <v-form ref="formRef" @submit.prevent="submit">
              <v-text-field
                v-model="form.name"
                :label="t('admin.regions.table.name')"
                variant="outlined"
                class="mb-4"
                required
                :rules="[v => !!v || t('auth.validation.required', { field: t('admin.regions.table.name') })]"
                data-cy="region-name-input"
              ></v-text-field>

              <v-select
                v-model="form.partner_id"
                :items="partners"
                item-title="name"
                item-value="id"
                :label="t('admin.regions.table.partner')"
                variant="outlined"
                class="mb-4"
                required
                :rules="[v => !!v || t('auth.validation.required', { field: t('admin.regions.table.partner') })]"
                data-cy="region-partner-select"
              ></v-select>

              <v-select
                v-model="form.status"
                :items="statusOptions"
                :label="t('admin.regions.table.status')"
                variant="outlined"
                class="mb-6"
                data-cy="region-status-select"
              ></v-select>

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
                {{ t('partner.actions.update') }}
              </v-btn>
              
              <v-btn
                variant="text"
                block
                size="large"
                class="rounded-lg mt-2"
                @click="router.visit(route('admin.regions.index'))"
              >
                {{ t('partner.actions.cancel') }}
              </v-btn>
            </v-form>
          </v-card>
        </v-col>
      </v-row>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import MapRegionEditor from '@/Components/MapRegionEditor.vue';
import adminService from '@/services/admin';
import { useNotificationStore } from '@/stores/notification';

const { t } = useI18n();
const notifications = useNotificationStore();

const props = defineProps({
  region: Object,
  partners: Array
});

const formRef = ref(null);

const parsePolygon = (polygon) => {
  if (typeof polygon === 'string') {
    try {
      return JSON.parse(polygon);
    } catch (e) {
      return { type: 'Polygon', coordinates: [[]] };
    }
  }
  return polygon || { type: 'Polygon', coordinates: [[]] };
};

const form = useForm({
  name: props.region.name,
  partner_id: props.region.partner_id,
  polygon: parsePolygon(props.region.polygon),
  status: props.region.status
});

const statusOptions = computed(() => [
  { title: t('partner.regions.active'), value: 'active' },
  { title: t('partner.regions.inactive'), value: 'inactive' },
]);

const submit = async () => {
  const { valid } = await formRef.value.validate();
  if (!valid) return;

  if (form.polygon.coordinates[0].length === 0) {
    notifications.error(t('admin.regions.error.no_polygon', 'Please draw a region on the map'));
    return;
  }

  form.processing = true;
  try {
    await adminService.updateRegion(props.region.id, form.data());
    notifications.success(t('admin.regions.notifications.save_success'));
    router.visit(route('admin.regions.index'));
  } catch (err) {
    notifications.error(t('admin.regions.notifications.save_failed'));
  } finally {
    form.processing = false;
  }
};
</script>

<style scoped>
.region-edit-page {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
