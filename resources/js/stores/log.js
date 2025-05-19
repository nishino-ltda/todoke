import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useLogStore = defineStore('log', () => {
  const loggedMessage = ref('');

  function log(message) {
    console.log(message); // Keep console.log for visibility in terminal
    loggedMessage.value = message;

    if (window.Cypress) {
      window.loggedMessage = loggedMessage.value;
    }
  }

  return {
    loggedMessage,
    log,
  };
});
