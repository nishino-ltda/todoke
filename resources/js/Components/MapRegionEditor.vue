<template>
  <div class="map-region-editor" :class="{ 'read-only': readOnly }">
    <div ref="mapContainer" class="map-container" :style="{ height: height }"></div>
    <div class="map-toolbar v-card" v-if="!readOnly">
      <div class="d-flex align-center px-3 py-2">
        <v-icon size="small" color="primary" class="mr-2">mdi-information-outline</v-icon>
        <span class="text-caption font-weight-medium text-grey-darken-1">
          {{ t('admin.regions.editor_hint', 'Click on the map to start drawing a region') }}
        </span>
        <v-spacer></v-spacer>
        <v-btn
          v-if="hasPolygon"
          variant="text"
          color="error"
          size="small"
          prepend-icon="mdi-delete"
          @click="clearMap"
        >
          {{ t('partner.actions.clear', 'Clear') }}
        </v-btn>
      </div>
    </div>
    <div v-if="error" class="map-error pa-2">
      <v-alert type="error" density="compact" variant="tonal">
        {{ error }}
      </v-alert>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet-draw';
import 'leaflet-draw/dist/leaflet.draw.css';

const { t } = useI18n();

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ type: 'Polygon', coordinates: [[]] })
  },
  height: {
    type: String,
    default: '500px'
  },
  readOnly: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue']);

const mapContainer = ref(null);
const error = ref(null);
const hasPolygon = ref(false);
let map = null;
let drawnItems = null;
let drawControl = null;

// Fix Leaflet Default Icon issues with build tools
const fixLeafletIcons = () => {
  delete L.Icon.Default.prototype._getIconUrl;
  L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon-2x.png',
    iconUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
  });
};

const initMap = () => {
  if (!mapContainer.value) return;

  fixLeafletIcons();

  map = L.map(mapContainer.value, {
    zoomControl: false
  }).setView([-15.78, -47.93], 4);

  L.control.zoom({ position: 'topright' }).addTo(map);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);

  drawnItems = new L.FeatureGroup();
  map.addLayer(drawnItems);

  if (!props.readOnly) {
    drawControl = new L.Control.Draw({
      edit: {
        featureGroup: drawnItems,
        poly: { allowIntersection: true },
        remove: true
      },
      draw: {
        polygon: {
          allowIntersection: true,
          showArea: true,
          shapeOptions: {
            color: '#4F46E5',
            fillOpacity: 0.2
          }
        },
        polyline: false,
        rectangle: false,
        circle: false,
        marker: false,
        circlemarker: false
      }
    });

    // We only add the control if there is no polygon yet, 
    // or we can keep it but only allow one polygon.
    map.addControl(drawControl);

    map.on(L.Draw.Event.CREATED, (e) => {
      drawnItems.clearLayers();
      const layer = e.layer;
      drawnItems.addLayer(layer);
      hasPolygon.value = true;
      updateModelValue();
    });

    map.on(L.Draw.Event.EDITED, () => {
      updateModelValue();
    });

    map.on(L.Draw.Event.DELETED, () => {
      hasPolygon.value = false;
      updateModelValue();
    });
  }

  loadInitialData();
  
  // Ensure the map container is correctly sized
  setTimeout(() => {
    if (map) map.invalidateSize();
  }, 200);
};

const loadInitialData = () => {
  if (!props.modelValue || !props.modelValue.coordinates || props.modelValue.coordinates.length === 0 || props.modelValue.coordinates[0].length === 0) {
    hasPolygon.value = false;
    return;
  }

  try {
    const geoJsonLayer = L.geoJSON(props.modelValue, {
      style: {
        color: '#4F46E5',
        weight: 3,
        fillOpacity: 0.2
      }
    });

    geoJsonLayer.eachLayer((l) => {
      // Convert GeoJSON layer to a Draw-compatible layer
      let newLayer;
      if (l instanceof L.Polygon) {
        newLayer = L.polygon(l.getLatLngs(), {
          color: '#4F46E5',
          weight: 3,
          fillOpacity: 0.2
        });
      }
      
      if (newLayer) {
        drawnItems.addLayer(newLayer);
        hasPolygon.value = true;
      }
    });
    
    const bounds = drawnItems.getBounds();
    if (bounds.isValid()) {
      map.fitBounds(bounds, { padding: [40, 40] });
    }
  } catch (e) {
    console.error('Error loading initial polygon', e);
    error.value = 'Invalid polygon data';
  }
};

const updateModelValue = () => {
  const layers = drawnItems.getLayers();
  if (layers.length === 0) {
    emit('update:modelValue', { type: 'Polygon', coordinates: [[]] });
    return;
  }

  const layer = layers[0];
  const geojson = layer.toGeoJSON();
  emit('update:modelValue', geojson.geometry);
};

const clearMap = () => {
  drawnItems.clearLayers();
  hasPolygon.value = false;
  updateModelValue();
};

onMounted(() => {
  setTimeout(() => {
    initMap();
  }, 100);
});

onUnmounted(() => {
  if (map) {
    map.remove();
    map = null;
  }
});
</script>

<style scoped>
.map-region-editor {
  width: 100%;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  position: relative;
  background: #f8fafc;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
}

.map-container {
  width: 100%;
  z-index: 1;
}

.map-toolbar {
  position: absolute;
  bottom: 16px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 1000;
  width: auto;
  min-width: 300px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(8px);
  border-radius: 12px;
  box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
  border: 1px solid rgba(var(--v-border-color), 0.1);
}

.read-only .map-toolbar {
  display: none;
}

/* Leaflet Draw Overrides for Premium Look */
:deep(.leaflet-draw-toolbar a) {
  background-color: #fff !important;
  border-bottom: 1px solid #e2e8f0 !important;
}

:deep(.leaflet-draw-toolbar a:first-child) {
  border-top-left-radius: 8px !important;
  border-top-right-radius: 8px !important;
}

:deep(.leaflet-draw-toolbar a:last-child) {
  border-bottom-left-radius: 8px !important;
  border-bottom-right-radius: 8px !important;
  border-bottom: none !important;
}

:deep(.leaflet-draw-actions a) {
  background-color: #4F46E5 !important;
  color: #fff !important;
}

:deep(.leaflet-draw-actions li:first-child a) {
  border-top-left-radius: 4px !important;
  border-bottom-left-radius: 4px !important;
}

:deep(.leaflet-draw-actions li:last-child a) {
  border-top-right-radius: 4px !important;
  border-bottom-right-radius: 4px !important;
}
</style>
