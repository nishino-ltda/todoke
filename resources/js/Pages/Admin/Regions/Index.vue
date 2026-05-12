<template>
  <AdminLayout>
    <div class="regions-management" data-cy="regions-management">
      <div class="d-flex align-center justify-space-between mb-6">
        <div>
          <h1 class="text-h4 font-weight-bold">{{ t('admin.regions.title') }}</h1>
          <p class="text-subtitle-1 text-grey">{{ t('admin.regions.description', 'Manage delivery regions and service areas') }}</p>
        </div>
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          size="large"
          class="rounded-lg shadow-sm"
          @click="router.visit(route('admin.regions.create'))"
          data-cy="create-region-btn"
        >
          {{ t('admin.regions.add') }}
        </v-btn>
      </div>

      <!-- Regions Map -->
      <v-card border elevation="0" class="rounded-xl mb-8 overflow-hidden">
        <v-card-title class="px-6 py-4 border-b bg-grey-lighten-5 d-flex align-center">
          <v-icon start color="primary">mdi-map</v-icon>
          <span class="text-h6 font-weight-bold">{{ t('admin.regions.overview', 'Coverage Map') }}</span>
          <v-spacer></v-spacer>
          <v-chip size="small" variant="flat" color="primary" class="font-weight-bold">
            {{ regions.length }} {{ t('admin.regions.count', 'Regions') }}
          </v-chip>
        </v-card-title>
        <v-card-text class="pa-0">
          <div
            id="regions-map"
            ref="mapEl"
            class="regions-map"
            data-cy="regions-map"
            style="height: 450px; width: 100%;"
          ></div>
        </v-card-text>
      </v-card>

      <!-- Regions Table -->
      <v-card border elevation="0" class="rounded-xl overflow-hidden">
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
              variant="flat"
              class="text-uppercase font-weight-bold"
            >
              {{ item.status === 'active' ? t('partner.regions.active') : t('partner.regions.inactive') }}
            </v-chip>
          </template>

          <template #item.partner="{ item }">
            <div class="d-flex align-center">
              <v-avatar size="32" color="primary-lighten-5" class="mr-3">
                <span class="text-primary text-caption font-weight-bold">{{ item.partner?.name?.charAt(0) }}</span>
              </v-avatar>
              <div class="d-flex flex-column">
                <span class="font-weight-medium text-body-2">{{ item.partner?.name || 'N/A' }}</span>
                <span class="text-caption text-grey">{{ item.partner?.email }}</span>
              </div>
            </div>
          </template>

          <template #item.actions="{ item }">
            <v-btn
              variant="tonal"
              color="primary"
              size="small"
              icon="mdi-pencil"
              class="mr-2"
              @click="router.visit(route('admin.regions.edit', { id: item.id }))"
              data-cy="edit-region-btn"
            ></v-btn>
            <v-btn
              variant="tonal"
              color="error"
              size="small"
              icon="mdi-delete"
              @click="confirmDelete(item)"
              data-cy="delete-region-btn"
            ></v-btn>
          </template>
        </DataTable>
      </v-card>

      <!-- Delete Confirmation -->
      <AppModal v-model="showDeleteModal" :title="t('partner.actions.confirm_delete')" maxWidth="400">
        <div class="text-center pa-4">
          <v-icon color="error" size="64" class="mb-4">mdi-alert-circle-outline</v-icon>
          <p class="text-body-1 mb-6">
            {{ t('partner.products.confirm_delete', { name: selectedRegion?.name }) }}
            <br>
            <span class="text-caption text-grey">{{ t('admin.regions.delete_warning', 'This action cannot be undone.') }}</span>
          </p>
        </div>
        <template #actions>
          <v-btn variant="text" block @click="showDeleteModal = false" class="mb-2">{{ t('partner.actions.cancel') }}</v-btn>
          <v-btn color="error" block @click="doDelete" :loading="saving" data-cy="confirm-delete-btn" class="rounded-lg">
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
import { router } from '@inertiajs/vue3';
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
const showDeleteModal = ref(false);
const selectedRegion = ref(null);
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

// ── Map ───────────────────────────────────────────────────────────────────────

const initMap = () => {
  if (!mapEl.value || map) return;
  map = L.map(mapEl.value, {
    zoomControl: false
  }).setView([-15.78, -47.93], 5);
  
  L.control.zoom({ position: 'topright' }).addTo(map);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);
  
  setTimeout(() => {
    if (map) map.invalidateSize();
  }, 250);
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
      let geoJson = region.polygon;
      if (typeof geoJson === 'string') {
        try {
          geoJson = JSON.parse(geoJson);
        } catch (e) {
          console.warn(`Invalid polygon JSON for region ${region.id}:`, geoJson);
          return;
        }
      }
      if (!geoJson || typeof geoJson !== 'object') {
        console.warn(`Invalid polygon data for region ${region.id}: expected GeoJSON object`);
        return;
      }

      const layer = L.geoJSON(geoJson, {
        style: {
          color: region.status === 'active' ? '#4CAF50' : '#9E9E9E',
          weight: 2,
          fillOpacity: 0.15,
        }
      }).addTo(map);

      layer.bindTooltip(region.name, { permanent: false });
      layer.on('click', () => router.visit(route('admin.regions.edit', { id: region.id })));
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
    regions.value = response.data?.data || response.data?.regions || [];
    renderRegionsOnMap();
  } catch (err) {
    notifications.error(t('admin.regions.notifications.load_failed'));
  } finally {
    loading.value = false;
  }
};

// ── CRUD ──────────────────────────────────────────────────────────────────────
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
  z-index: 1;
}

.shadow-sm {
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05) !important;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
