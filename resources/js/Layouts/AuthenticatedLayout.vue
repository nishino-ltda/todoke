<template>
  <v-app data-test="authenticated-layout">
    <AppHeader />
    <v-navigation-drawer
      v-model="drawer"
      app
      temporary
      data-test="nav-drawer"
    >
      <v-list>
        <v-list-item
          v-for="(item, i) in navItems"
          :key="i"
          :to="item.to"
          :data-test="`nav-item-${item.text.toLowerCase()}`"
        >
          <v-list-item-title>{{ item.text }}</v-list-item-title>
        </v-list-item>
      </v-list>
    </v-navigation-drawer>

    <v-main>
      <v-container>
        <slot data-test="authenticated-content" />
      </v-container>
    </v-main>

    <AppFooter />
  </v-app>
</template>

<script setup>
import { ref } from 'vue'
import AppHeader from '@/components/AppHeader.vue'
import AppFooter from '@/components/AppFooter.vue'

const drawer = ref(false)
const navItems = [
  { text: 'Dashboard', to: '/dashboard' },
  { text: 'Profile', to: '/profile' },
  { text: 'Settings', to: '/settings' }
]
</script>
