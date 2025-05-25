import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useLogStore = defineStore('log', () => {
  const logs = ref([]);
  const maxLogs = 100; // Prevent memory leaks

  // Expose store to window for Cypress
  if (typeof window !== 'undefined') {
    window.logStore = {
      getLogs: () => logs.value,
      getLatest: () => logs.value[0]?.message || '',
      clear: () => { logs.value = []; },
      log: (message, type = 'info', stack = null) => {
        const timestamp = new Date().toISOString();
        const logEntry = { timestamp, message, type, stack };
        
        const typePrefix = type === 'error' ? '❌' : 
                        type === 'warn' ? '⚠️' : 
                        type === 'info' ? 'ℹ️' : '📝';
        console.log(`${typePrefix}  [${timestamp}] ${message}`);  // Added extra space after emoji

        logs.value.push(logEntry);
        if (logs.value.length > maxLogs) {
          logs.value.length = maxLogs;
        }
      }
    };
  }

  function log(message, type = 'info', stack = null) {
    const timestamp = new Date().toISOString();
    const logEntry = { timestamp, message, type, stack };
    
    // Keep console.log for visibility in terminal
    const typePrefix = type === 'error' ? '❌' : 
                      type === 'warn' ? '⚠️' : 
                      type === 'info' ? 'ℹ️' : '📝';
    console.log(`${typePrefix}  [${timestamp}] ${message}`);  // Added extra space after emoji

    // Add to logs array
    logs.value.push(logEntry);
    
    // Keep only the most recent logs
    if (logs.value.length > maxLogs) {
      logs.value.length = maxLogs;
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
