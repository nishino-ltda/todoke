import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useNotificationStore = defineStore('notification', () => {
  const notifications = ref([]);

  /**
   * Add a notification
   * @param {Object} notification
   * @param {string} notification.message - The message to display
   * @param {string} [notification.type='info'] - success, info, warning, error
   * @param {string} [notification.title] - Optional title
   * @param {number} [notification.duration=5000] - Duration in ms
   */
  function add(notification) {
    const id = Date.now() + Math.random().toString(36).substr(2, 9);
    const newNotification = {
      id,
      type: 'info',
      duration: 5000,
      ...notification
    };

    notifications.value.push(newNotification);

    if (newNotification.duration > 0) {
      setTimeout(() => {
        remove(id);
      }, newNotification.duration);
    }

    return id;
  }

  function remove(id) {
    const index = notifications.value.findIndex(n => n.id === id);
    if (index !== -1) {
      notifications.value.splice(index, 1);
    }
  }

  function clear() {
    notifications.value = [];
  }

  // Helper methods
  const success = (message, title = '') => add({ message, title, type: 'success' });
  const error = (message, title = '') => add({ message, title, type: 'error' });
  const info = (message, title = '') => add({ message, title, type: 'info' });
  const warning = (message, title = '') => add({ message, title, type: 'warning' });

  return {
    notifications,
    add,
    remove,
    clear,
    success,
    error,
    info,
    warning
  };
});
