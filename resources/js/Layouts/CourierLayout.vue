<template>
  <v-app>
    <v-app-bar app flat border color="white">
      <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>

      <v-app-bar-title class="text-subtitle-1 font-weight-bold">
        Courier Portal
      </v-app-bar-title>

      <v-spacer></v-spacer>

      <LanguageSelector />
    </v-app-bar>

    <v-navigation-drawer
      v-model="drawer"
      app
      :temporary="$vuetify.display.mobile"
      :permanent="!$vuetify.display.mobile"
    >
      <v-list density="comfortable" nav>
        <v-list-item
          v-for="item in navItems"
          :key="item.title"
          :active="isActive(item.route)"
          :prepend-icon="item.icon"
          :title="item.title"
          @click="goTo(item.route)"
          link
          data-cy="courier-nav-item"
        ></v-list-item>
      </v-list>

      <template v-slot:append>
        <v-divider></v-divider>
        <v-list-item
          :prepend-avatar="`https://ui-avatars.com/api/?name=${user?.name || 'U'}&background=0D47A1&color=fff`"
          :title="user?.name || user?.email || 'User'"
          :subtitle="user?.email"
        >
          <template v-slot:append>
            <v-btn icon="mdi-logout" variant="text" @click="logout" size="small"></v-btn>
          </template>
        </v-list-item>
      </template>
    </v-navigation-drawer>

    <v-main class="bg-grey-lighten-4">
      <v-container fluid class="pa-6">
        <slot />
      </v-container>
      <NotificationCenter />
    </v-main>
  </v-app>
</template>

<script setup>
import { ref, computed } from 'vue';
import { storeToRefs } from 'pinia';
import { usePage, router } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/auth';
import LanguageSelector from '../Components/LanguageSelector.vue';
import NotificationCenter from '@/Components/NotificationCenter.vue';

const page = usePage();
const authStore = useAuthStore();
const drawer = ref(true);

const { user } = storeToRefs(authStore);

const currentRoute = computed(() => page.url);

const navItems = [
  { title: 'Dashboard', icon: 'mdi-view-dashboard', route: '/courier' },
  { title: 'Deliveries', icon: 'mdi-truck-delivery', route: '/courier/deliveries' },
  { title: 'Hybrid Deliveries', icon: 'mdi-truck-fast', route: '/courier/hybrid-deliveries' },
  { title: 'Service Area', icon: 'mdi-map-marker-path', route: '/courier/service-area' },
  { title: 'Settings', icon: 'mdi-cog', route: '/courier/settings' },
  { title: 'Profile', icon: 'mdi-account', route: '/courier/profile' },
  { title: 'Access as Customer', icon: 'mdi-account-switch', route: '/customer/dashboard' },
];

const isActive = (route) => {
  if (route === '/courier') {
    return currentRoute.value === route || currentRoute.value === '/courier/dashboard';
  }
  return currentRoute.value.startsWith(route);
};

const goTo = (url) => {
  router.visit(url);
};

const logout = () => {
  authStore.logout(router);
};
</script>

<style scoped>
.v-main {
  min-height: 100vh;
}
</style>
