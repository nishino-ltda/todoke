<template>
  <v-card class="metric-card" elevation="1">
    <v-card-text class="d-flex align-center">
      <div :class="['metric-icon-box', colorClass]">
        <v-icon :icon="icon" color="white"></v-icon>
      </div>
      <div class="ml-4">
        <div class="text-caption text-grey-darken-1 text-uppercase font-weight-bold">{{ title }}</div>
        <div class="text-h5 font-weight-bold" data-cy="metric-value">{{ value }}</div>
      </div>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    default: '0'
  },
  icon: {
    type: String,
    default: 'mdi-chart-line'
  },
  color: {
    type: String,
    default: 'blue'
  }
});

const colorClass = computed(() => `bg-${props.color}`);
</script>

<style scoped>
.metric-card {
  border-radius: 12px;
  transition: transform 0.2s ease;
}

.metric-card:hover {
  transform: translateY(-4px);
}

.metric-icon-box {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.bg-orange { background-color: #fb8c00; }
.bg-blue { background-color: #1e88e5; }
.bg-green { background-color: #43a047; }
.bg-purple { background-color: #8e24aa; }
.bg-red { background-color: #e53935; }
.bg-teal { background-color: #00897b; }
</style>
