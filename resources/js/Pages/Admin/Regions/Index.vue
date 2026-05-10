<template>
  <AdminLayout>
    <div class="regions-management" data-cy="regions-management">
      <div class="d-flex align-center justify-space-between mb-6">
        <h1 class="text-h4 font-weight-bold">{{ t('admin.regions.title') }}</h1>
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          @click="openCreateModal"
          data-cy="create-region-btn"
        >
          {{ t('admin.regions.add') }}
        </v-btn>
      </div>

      <!-- Regions Map -->
      <v-card border elevation="0" class="rounded-xl mb-6">
        <v-card-title class="px-4 py-3">
          <v-icon start>mdi-map</v-icon>
          {{ t('admin.regions.title') }}
        </v-card-title>
        <v-card-text class="pa-0">
          <div
            id="regions-map"
            ref="mapEl"
            class="regions-map"
            data-cy="regions-map"
            style="height: 360px; width: 100%;"
          ></div>
        </v-card-text>
      </v-card>

      <!-- Regions Table -->
      <DataTable
        :headers="headers"
        :items="regions"
        :loading="loading"
        data-cy="regions-table"
      >
        <template #item.status="{ item }">
          <v-chip
            :color="item.status === 'active' ? 'success' : 'grey'"
            size="small"
            class="text-uppercase font-weight-bold"
          >
            {{ item.status === 'active' ? t('partner.regions.active') : t('partner.regions.inactive') }}
          </v-chip>
        </template>

        <template #item.partner="{ item }">
          <div class="d-flex flex-column">
            <span class="font-weight-medium">{{ item.partner?.name || 'N/A' }}</span>
            <span class="text-caption text-grey">{{ item.partner?.email }}</span>
          </div>
        </template>

        <template #item.actions="{ item }">
          <v-btn
            variant="text"
            color="primary"
            icon="mdi-pencil"
            @click="editRegion(item)"
            data-cy="edit-region-btn"
          ></v-btn>
          <v-btn
            variant="text"
            color="error"
            icon="mdi-delete"
            @click="confirmDelete(item)"
            data-cy="delete-region-btn"
          ></v-btn>
        </template>
      </DataTable>

      <!-- Region Form Modal -->
      <AppModal
        v-model="showFormModal"
        :title="isEditing ? t('admin.regions.edit') : t('admin.regions.new')"
        maxWidth="600"
      >
        <v-form ref="regionForm" @submit.prevent="saveRegion">
          <v-row>
            <v-col cols="12">
              <v-text-field
                v-model="form.name"
                :label="t('admin.regions.table.name')"
                required
                :rules="[v => !!v || t('auth.validation.required', { field: t('admin.regions.table.name') })]"
                data-cy="region-name-input"
              ></v-text-field>
            </v-col>

            <v-col cols="12">
              <v-select
                v-model="form.partner_id"
                :items="partners"
                item-title="name"
                item-value="id"
                :label="t('admin.regions.table.partner')"
                required
                :rules="[v => !!v || t('auth.validation.required', { field: t('admin.regions.table.partner') })]"
                data-cy="region-partner-select"
              ></v-select>
            </v-col>

            <v-col cols="12">
              <v-textarea
                v-model="form.polygon_json"
                :label="t('partner.regions.coordinates')"
                placeholder='{"type": "Polygon", "coordinates": [[[lng, lat], ...]]}'
                rows="4"
                required
                :rules="[v => !!v || t('auth.validation.required', { field: t('partner.regions.coordinates') }), validateJson]"
                data-cy="region-coordinates-input"
              ></v-textarea>
            </v-col>

            <v-col cols="12">
              <v-select
                v-model="form.status"
                :items="statusOptions"
                :label="t('admin.regions.table.status')"
                data-cy="region-status-select"
              ></v-select>
            </v-col>
          </v-row>
        </v-form>
        <template #actions>
          <v-btn variant="text" @click="showFormModal = false">{{ t('partner.actions.cancel') }}</v-btn>
          <v-btn color="primary" @click="saveRegion" :loading="saving" data-cy="save-region-btn">
            {{ isEditing ? t('partner.actions.update') : t('partner.actions.create') }}
          </v-btn>
        </template>
      </AppModal>

      <!-- Delete Confirmation -->
      <AppModal v-model="showDeleteModal" :title="t('partner.actions.confirm_delete')" maxWidth="400">
        <p>{{ t('partner.products.confirm_delete', { name: selectedRegion?.name }) }}</p>
        <template #actions>
          <v-btn variant="text" @click="showDeleteModal = false">{{ t('partner.actions.cancel') }}</v-btn>
          <v-btn color="error" @click="doDelete" :loading="saving" data-cy="confirm-delete-btn">
            {{ t('partner.actions.delete') }}
          </v-btn>
        </template>
      </AppModal>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import AppModal from '@/Components/AppModal.vue';
import adminService from '@/services/admin';
import { useNotificationStore } from '@/stores/notification';

const { t } = useI18n();
const notifications = useNotificationStore();
const loading = ref(false);
const saving = ref(false);
const regions = ref([]);
const partners = ref([]);
const showFormModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const selectedRegion = ref(null);
const regionForm = ref(null);
const mapEl = ref(null);
let map = null;
const polygonLayers = [];

// ── Table ─────────────────────────────────────────────────────────────────────
const headers = computed(() => [
  { title: t('admin.regions.table.id'), key: 'id', width: '80px' },
  { title: t('admin.regions.table.name'), key: 'name' },
  { title: t('admin.regions.table.partner'), key: 'partner' },
  { title: t('admin.regions.table.status'), key: 'status' },
  { title: t('admin.regions.table.actions'), key: 'actions', sortable: false, align: 'end' },
]);

const statusOptions = computed(() => [
  { title: t('partner.regions.active'), value: 'active' },
  { title: t('partner.regions.inactive'), value: 'inactive' },
]);

const form = ref({ name: '', partner_id: null, polygon_json: '', status: 'active' });

const validateJson = (v) => {
  try { JSON.parse(v); return true; }
  catch (e) { return 'Invalid JSON format'; }
};

// ── Map ───────────────────────────────────────────────────────────────────────

const initMap = () => {
  if (!mapEl.value || map) return;
  map = L.map(mapEl.value).setView([-15.78, -47.93], 5); // Brazil center
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);
};

const renderRegionsOnMap = () => {
  if (!map) return;
  // Clear existing layers
  polygonLayers.forEach(l => map.removeLayer(l));
  polygonLayers.length = 0;

  const bounds = [];
  regions.value.forEach(region => {
    if (!region.polygon) return;
    try {
      const geoJson = typeof region.polygon === 'string'
        ? JSON.parse(region.polygon)
        : region.polygon;

      const layer = L.geoJSON(geoJson, {
        style: {
          color: region.status === 'active' ? '#4CAF50' : '#9E9E9E',
          weight: 2,
          fillOpacity: 0.15,
        }
      }).addTo(map);

      layer.bindTooltip(region.name, { permanent: false });
      layer.on('click', () => editRegion(region));
      polygonLayers.push(layer);

      const layerBounds = layer.getBounds();
      if (layerBounds.isValid()) bounds.push(layerBounds);
    } catch (e) {
      console.warn(`Invalid polygon for region ${region.id}`, e);
    }
  });

  if (bounds.length > 0) {
    const combined = bounds.reduce((acc, b) => acc.extend(b));
    map.fitBounds(combined.pad(0.1));
  }
};

// ── Data ──────────────────────────────────────────────────────────────────────
const fetchRegions = async () => {
  loading.value = true;
  try {
    const response = await adminService.getRegions();
    regions.value = response.data?.regions || response.data || [];
    renderRegionsOnMap();
  } catch (err) {
    notifications.error(t('admin.regions.notifications.load_failed'));
  } finally {
    loading.value = false;
  }
};

const fetchPartners = async () => {
  try {
    const response = await adminService.getUsers({ type: 'partner' });
    partners.value = response.data?.users || response.data || [];
  } catch (err) {
    console.error('Failed to load partners', err);
  }
};

// ── CRUD ──────────────────────────────────────────────────────────────────────
const openCreateModal = () => {
  isEditing.value = false;
  form.value = { name: '', partner_id: null, polygon_json: '{"type": "Polygon", "coordinates": [[]]}', status: 'active' };
  showFormModal.value = true;
};

const editRegion = (region) => {
  isEditing.value = true;
  selectedRegion.value = region;
  form.value = {
    name: region.name,
    partner_id: region.partner_id,
    polygon_json: JSON.stringify(region.polygon || {}),
    status: region.status
  };
  showFormModal.value = true;
};

const saveRegion = async () => {
  const { valid } = await regionForm.value.validate();
  if (!valid) return;
  saving.value = true;
  try {
    const payload = { ...form.value, polygon: JSON.parse(form.value.polygon_json) };
    delete payload.polygon_json;
    if (isEditing.value) {
      await adminService.updateRegion(selectedRegion.value.id, payload);
    } else {
      await adminService.createRegion(payload);
    }
    notifications.success(t('admin.regions.notifications.save_success'));
    showFormModal.value = false;
    fetchRegions();
  } catch (err) {
    notifications.error(t('admin.regions.notifications.save_failed'));
  } finally {
    saving.value = false;
  }
};

const confirmDelete = (region) => {
  selectedRegion.value = region;
  showDeleteModal.value = true;
};

const doDelete = async () => {
  saving.value = true;
  try {
    await adminService.deleteRegion(selectedRegion.value.id);
    notifications.success(t('admin.regions.notifications.delete_success'));
    showDeleteModal.value = false;
    fetchRegions();
  } catch (err) {
    notifications.error(t('admin.regions.notifications.delete_failed'));
  } finally {
    saving.value = false;
  }
};

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(() => {
  initMap();
  fetchRegions();
  fetchPartners();
});

onUnmounted(() => {
  if (map) { map.remove(); map = null; }
});

watch(regions, renderRegionsOnMap, { deep: true });
</script>

<style scoped>
.regions-management {
  animation: fadeIn 0.5s ease-out;
}

.regions-map {
  border-radius: 0 0 8px 8px;
  z-index: 1;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
