<template>
  <div>
    <h1>Test Log Page</h1>
    <TestComponent />
    <p id="logged-message">{{ latestLog }}</p>
    <div v-if="logs.length > 0">
      <h3>Recent Logs:</h3>
      <ul>
        <li v-for="(entry, index) in logs" :key="index">
          [{{ entry.timestamp }}] {{ entry.message }}
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useLogStore } from '@/stores/log';
import { storeToRefs } from 'pinia';
import TestComponent from '@/components/TestComponent.vue';

const logStore = useLogStore();
const { logs } = storeToRefs(logStore);

const latestLog = computed(() => logs.value[0]?.message || '');
</script>
