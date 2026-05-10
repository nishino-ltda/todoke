<template>
  <v-app data-cy="guest-layout">
    <AppHeader :minimal="props.minimal" :with-drawer-toggle="false" />
    <v-main>
      <v-container>
        <slot data-cy="guest-content" />
      </v-container>
    </v-main>
    <AppFooter :minimal="props.minimal" />
  </v-app>
</template>

<script setup>
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'

const props = defineProps({
  minimal: {
    type: Boolean,
    default: false
  }
})
</script>
