<template>
  <div class="notification-center" data-cy="notifications-container">
    <div class="notification-stack">
      <AppAlert
        v-for="notification in notifications"
        :key="notification.id"
        :model-value="true"
        :type="notification.type"
        :title="notification.title"
        :text="notification.message"
        :auto-dismiss="notification.duration"
        @close="notificationStore.remove(notification.id)"
        data-cy="notification-item"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useNotificationStore } from '@/stores/notification';
import AppAlert from '@/Components/AppAlert.vue';

const notificationStore = useNotificationStore();
const notifications = computed(() => notificationStore.notifications);
</script>

<style scoped>
.notification-center {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  width: 350px;
  max-width: calc(100vw - 40px);
  pointer-events: none;
}

.notification-stack {
  display: flex;
  flex-direction: column-reverse;
  gap: 10px;
}

.notification-item {
  pointer-events: auto;
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}
</style>
