<template>
  <v-app>
    <v-app-bar flat border color="white">
      <v-app-bar-title class="font-weight-bold primary--text">
        <v-icon icon="mdi-truck-delivery" class="mr-2"></v-icon>
        Courier Portal
      </v-app-bar-title>
      
      <v-spacer></v-spacer>
      
      <LanguageSelector />
      
      <v-btn icon id="courier-menu-btn">
        <v-avatar size="32">
          <v-img src="https://ui-avatars.com/api/?name=Courier&background=random"></v-img>
        </v-avatar>
      </v-btn>
      
      <v-menu activator="#courier-menu-btn">
        <v-list>
          <v-list-item prepend-icon="mdi-account" title="Profile" @click="goTo('/courier/profile')"></v-list-item>
          <v-list-item prepend-icon="mdi-logout" title="Logout" @click="logout"></v-list-item>
        </v-list>
      </v-menu>
    </v-app-bar>

    <v-main class="bg-grey-lighten-5">
      <v-container class="pa-4 pa-md-6" fluid>
        <slot />
      </v-container>
    </v-main>

    <v-bottom-navigation v-model="activeTab" color="primary" grow>
      <v-btn value="dashboard" @click="goTo('/courier')">
        <v-icon>mdi-view-dashboard</v-icon>
        Dashboard
      </v-btn>

      <v-btn value="history" @click="goTo('/courier/history')">
        <v-icon>mdi-history</v-icon>
        History
      </v-btn>

      <v-btn value="earnings" @click="goTo('/courier/earnings')">
        <v-icon>mdi-cash-multiple</v-icon>
        Earnings
      </v-btn>
    </v-bottom-navigation>
  </v-app>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import LanguageSelector from '../Components/LanguageSelector.vue';

const page = usePage();
const activeTab = computed(() => {
  if (page.url.includes('/history')) return 'history';
  if (page.url.includes('/earnings')) return 'earnings';
  return 'dashboard';
});

const goTo = (url) => {
  router.visit(url);
};

const logout = () => {
  router.post('/logout');
};
</script>

<style scoped>
.v-main {
  padding-bottom: 64px; /* Space for bottom nav */
}

@media (min-width: 960px) {
  .v-container {
    max-width: 800px;
    margin: 0 auto;
  }
}
</style>
