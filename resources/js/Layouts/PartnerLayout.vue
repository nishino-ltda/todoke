<template>
  <v-app>
    <AppHeader @toggle-drawer="drawer = !drawer" />

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
          :active="currentRoute === item.route"
          @click="navigateTo(item.route)"
          :prepend-icon="item.icon"
          :title="item.title"
          link
          data-cy="partner-nav-item"
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
      <v-container fluid class="pa-8">
        <div class="d-flex align-center mb-6">
          <v-icon size="32" color="primary" class="mr-3">mdi-store</v-icon>
          <h1 class="text-h4 font-weight-bold">Partner Dashboard</h1>
        </div>

        <slot />
      </v-container>
      <NotificationCenter />
    </v-main>

    <AppFooter />
  </v-app>
</template>

<script setup>
import { ref, computed } from 'vue';
import { storeToRefs } from 'pinia';
import { usePage, router } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/auth';
import AppHeader from '../Components/AppHeader.vue';
import AppFooter from '../Components/AppFooter.vue';
import NotificationCenter from '@/Components/NotificationCenter.vue';

const page = usePage();
const authStore = useAuthStore();
const drawer = ref(true);

const { user } = storeToRefs(authStore);

const currentRoute = computed(() => page.url);

const navItems = [
  { title: 'Dashboard', icon: 'mdi-view-dashboard', route: '/partner' },
  { title: 'Orders', icon: 'mdi-clipboard-list', route: '/partner/orders' },
  { title: 'Products', icon: 'mdi-package-variant-closed', route: '/partner/products' },
  { title: 'Settings', icon: 'mdi-cog', route: '/partner/settings' },
  { title: 'Profile', icon: 'mdi-account', route: '/partner/profile' },
  { title: 'Access as Customer', icon: 'mdi-account-switch', route: '/customer/dashboard' },
];

const navigateTo = (route) => {
  router.visit(route);
};

const logout = () => {
  authStore.logout(router);
};

// Sync auth store with server-side props
import { watch } from 'vue';
watch(() => page.props.auth?.user, (newUser) => {
  if (newUser) {
    authStore.user = newUser;
  }
}, { immediate: true });
</script>

<style scoped>
.v-main {
  min-height: 100vh;
}
</style>
