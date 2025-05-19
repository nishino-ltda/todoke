<template>
  <div>
    <h1>Test Log Page</h1>
    <p id="logged-message">{{ loggedMessage }}</p>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useLogStore } from '@/stores/log';
import { storeToRefs } from 'pinia';

const logStore = useLogStore();
const { loggedMessage } = storeToRefs(logStore);
const { log } = logStore;

onMounted(() => {
  console.log('Direct console.log from TestLog.vue');
  log('Hello from TestLog.vue');
});
</script>
