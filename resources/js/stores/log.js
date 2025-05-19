import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useLogStore = defineStore('log', () => {
  const logs = ref([]);
  const maxLogs = 100; // Prevent memory leaks

  function log(message) {
    const timestamp = new Date().toISOString();
    const logEntry = { timestamp, message };
    
    // Keep console.log for visibility in terminal
    console.log(`[${timestamp}] ${message}`);

    // Add to logs array
    logs.value.unshift(logEntry);
    
    // Keep only the most recent logs
    if (logs.value.length > maxLogs) {
      logs.value.length = maxLogs;
    }

    // Make available to Cypress
    if (window.Cypress) {
      window.logStore = {
        getLogs: () => logs.value,
        getLatest: () => logs.value[0]?.message || '',
        clear: () => { logs.value = []; }
      };
    }
  }

  function clear() {
    logs.value = [];
  }

  return {
    logs,
    log,
    clear
  };
});
