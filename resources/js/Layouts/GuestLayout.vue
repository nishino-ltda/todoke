<template>
  <v-app data-cy="guest-layout">
    <AppHeader minimal />
    <v-main>
      <v-container>
        <slot data-cy="guest-content" />
      </v-container>
    </v-main>
    <AppFooter minimal />
  </v-app>
</template>

<script setup>
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
</script>
