<template>
  <PartnerLayout>
    <div class="region-show" data-cy="partner-regions-show">
      <div v-if="loading" class="d-flex justify-center pa-12">
        <v-progress-circular indeterminate color="primary"></v-progress-circular>
      </div>
      
      <v-row v-else-if="region">
        <v-col cols="12" md="4">
          <v-card class="glass-card pa-6 h-100" rounded="xl">
            <div class="d-flex align-center mb-4">
              <v-icon color="primary" class="mr-2">mdi-map-marker-radius</v-icon>
              <h1 class="text-h5 font-weight-bold">{{ region.name }}</h1>
            </div>
            
            <div class="mb-6">
              <div class="text-caption text-uppercase text-medium-emphasis mb-1">{{ t('partner.regions.status') }}</div>
              <v-chip :color="region.active ? 'success' : 'error'" size="small">
                {{ region.active ? t('partner.regions.active') : t('partner.regions.inactive') }}
              </v-chip>
            </div>

            <v-divider class="mb-6"></v-divider>

            <div class="d-flex gap-2">
              <v-btn
                color="primary"
                prepend-icon="mdi-pencil"
                rounded="lg"
                block
                @click="router.visit(`/partner/regions/${region.id}/edit`)"
                data-cy="edit-region-btn"
              >
                {{ t('partner.actions.edit') }}
              </v-btn>
            </div>
            <v-btn
              variant="text"
              color="primary"
              rounded="lg"
              block
              class="mt-2"
              @click="router.visit('/partner/regions')"
            >
              {{ t('common.back') || 'Back' }}
            </v-btn>
          </v-card>
        </v-col>

        <v-col cols="12" md="8">
          <v-card class="glass-card overflow-hidden h-100" rounded="xl" min-height="500">
            <div ref="mapContainer" class="map-container"></div>
          </v-card>
        </v-col>
      </v-row>
    </div>
  </PartnerLayout>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useI18n } from 'vue-i18n';
import { router } from '@inertiajs/vue3';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';
import partnerService from '@/services/partner';
import { useNotificationStore } from '@/stores/notification';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
  regionId: {
    type: [String, Number],
    required: true
  }
});

const { t } = useI18n();
const notifications = useNotificationStore();
const loading = ref(true);
const region = ref(null);
const mapContainer = ref(null);
let map = null;

const initMap = () => {
  if (!region.value?.coordinates) return;

  map = L.map(mapContainer.value).setView([0, 0], 13);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  try {
    const coords = typeof region.value.coordinates === 'string' 
      ? JSON.parse(region.value.coordinates) 
      : region.value.coordinates;
      
    const polygon = L.polygon(coords, { color: '#0D47A1', fillColor: '#0D47A1', fillOpacity: 0.2 }).addTo(map);
    map.fitBounds(polygon.getBounds());
  } catch (e) {
    console.error('Failed to parse coordinates', e);
  }
};

const fetchRegion = async () => {
  loading.value = true;
  try {
    const response = await partnerService.getRegions();
    region.value = response.data.find(r => r.id == props.regionId);
    if (region.value) {
      await nextTick();
      initMap();
    } else {
      notifications.error(t('partner.regions.error.load'));
    }
  } catch (err) {
    notifications.error(t('partner.regions.error.load'));
  } finally {
    loading.value = false;
  }
};

onMounted(fetchRegion);
</script>

<style scoped>
.glass-card {
  background: rgba(255, 255, 255, 0.7) !important;
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07) !important;
}

.map-container {
  width: 100%;
  height: 100%;
  min-height: 500px;
}

.gap-2 {
  gap: 0.5rem;
}
</style>
