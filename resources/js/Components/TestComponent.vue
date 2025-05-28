<template>
  <div>
    <button @click="logMessage" id="log-button">
      Log Test Message
    </button>
  </div>
</template>

<script setup>
import { useLogStore } from '@/stores/log';

const logStore = useLogStore();
const { log } = logStore;

function logMessage() {
  log('Test message from TestComponent');
}
</script>
