<template>
  <div class="user-menu-append">
    <v-divider></v-divider>
    <v-list-item
      v-if="isAuthenticated"
      :prepend-avatar="avatarUrl"
      :title="userName"
      :subtitle="userEmail"
      data-cy="user-menu-append"
      class="pa-4"
    >
      <template v-slot:append>
        <v-btn
          icon="mdi-logout"
          variant="text"
          @click="logout"
          size="small"
          color="error"
        ></v-btn>
      </template>
    </v-list-item>
    <v-list-item v-else class="pa-4">
      <v-btn
        block
        color="primary"
        variant="flat"
        prepend-icon="mdi-login"
        @click="navigateTo('/login')"
        class="text-none font-weight-bold"
        rounded="pill"
        data-cy="login-button"
      >
        {{ $t('auth.login') }}
      </v-btn>
    </v-list-item>
  </div>
</template>

<script setup>
import { useLayout } from '@/Composables/useLayout';

const { isAuthenticated, avatarUrl, userName, userEmail, logout, navigateTo } = useLayout();
</script>

<style scoped>
.user-menu-append {
  width: 100%;
}
</style>
