<template>
  <v-app>
    <AppHeader />
    
    <v-main class="bg-grey-lighten-4">
      <v-container fluid class="pa-6">
        <div class="d-flex align-center mb-6">
          <v-icon size="32" color="primary" class="mr-3">mdi-store</v-icon>
          <h1 class="text-h4 font-weight-bold">Partner Dashboard</h1>
        </div>
        
        <v-row>
          <!-- Sidebar / Navigation -->
          <v-col cols="12" md="3" lg="2">
            <v-card class="sidebar-card" elevation="1">
              <v-list density="comfortable">
                <v-list-item
                  v-for="item in navItems"
                  :key="item.title"
                  :active="currentRoute === item.route"
                  @click="navigateTo(item.route)"
                  :prepend-icon="item.icon"
                  :title="item.title"
                  link
                  data-cy="partner-nav-item"
                ></v-list-item>
              </v-list>
            </v-card>
          </v-col>
          
          <!-- Main Content -->
          <v-col cols="12" md="9" lg="10">
            <slot />
          </v-col>
        </v-row>
      </v-container>
    </v-main>
    
    <AppFooter />
  </v-app>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import AppHeader from '../Components/AppHeader.vue';
import AppFooter from '../Components/AppFooter.vue';

const page = usePage();
const currentRoute = computed(() => page.url);

const navItems = [
  { title: 'Dashboard', icon: 'mdi-view-dashboard', route: '/partner' },
  { title: 'Orders', icon: 'mdi-clipboard-list', route: '/partner/orders' },
  { title: 'Products', icon: 'mdi-package-variant-closed', route: '/partner/products' },
  { title: 'Settings', icon: 'mdi-cog', route: '/partner/settings' },
];

const navigateTo = (route) => {
  router.visit(route);
};
</script>

<style scoped>
.sidebar-card {
  border-radius: 12px;
  position: sticky;
  top: 80px;
}

.v-main {
  min-height: 100vh;
}
</style>
