<template>
  <v-app data-test="guest-layout">
    <AppHeader minimal />
    <v-main>
      <v-container>
        <slot data-test="guest-content" />
      </v-container>
    </v-main>
    <AppFooter minimal />
  </v-app>
</template>

<script setup>
import AppHeader from '@/components/AppHeader.vue'
import AppFooter from '@/components/AppFooter.vue'
</script>
