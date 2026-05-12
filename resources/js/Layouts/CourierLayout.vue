<template>
  <v-app>
    <v-app-bar app flat border color="white" class="glass-bar">
      <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>

      <v-app-bar-title class="text-subtitle-1 font-weight-bold">
        {{ currentPageTitle }}
      </v-app-bar-title>

      <v-spacer></v-spacer>

      <LanguageSelector />
    </v-app-bar>

    <v-navigation-drawer
      v-model="drawer"
      app
      :temporary="$vuetify.display.mobile"
      :permanent="!$vuetify.display.mobile"
      elevation="0"
      border="e"
    >
      <div class="pa-4 d-flex align-center">
        <v-img src="/images/logo.png" width="32" height="32" class="mr-2" />
        <span class="text-h6 font-weight-black color-primary">TODOKE</span>
      </div>

      <v-list density="comfortable" nav class="mt-2">
        <v-list-item
          v-for="item in navItems"
          :key="item.title"
          :active="isActive(item.route)"
          :prepend-icon="item.icon"
          :title="t(item.titleKey)"
          @click="goTo(item.route)"
          link
          rounded="lg"
          class="mb-1"
          data-cy="courier-nav-item"
        ></v-list-item>
      </v-list>

      <template v-slot:append>
        <v-divider></v-divider>
        <v-list-item
          class="pa-4"
          :prepend-avatar="`https://ui-avatars.com/api/?name=${user?.name || 'U'}&background=0D47A1&color=fff`"
          :title="user?.name || user?.email || t('courier.nav.user')"
          :subtitle="user?.email"
        >
          <template v-slot:append>
            <v-btn icon="mdi-logout" variant="text" @click="logout" size="small" color="error"></v-btn>
          </template>
        </v-list-item>
      </template>
    </v-navigation-drawer>

    <v-main class="bg-grey-lighten-4">
      <v-container fluid class="pa-4 pa-md-8 page-container">
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
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth';
import LanguageSelector from '../Components/LanguageSelector.vue';
import NotificationCenter from '@/Components/NotificationCenter.vue';
import AppFooter from '../Components/AppFooter.vue';

const { t } = useI18n();
const page = usePage();
const authStore = useAuthStore();
const drawer = ref(true);

const { user } = storeToRefs(authStore);

const currentRoute = computed(() => page.url);

const navItems = [
  { titleKey: 'courier.nav.dashboard', icon: 'mdi-view-dashboard', route: '/courier/dashboard' },
  { titleKey: 'courier.nav.deliveries', icon: 'mdi-truck-delivery', route: '/courier/deliveries' },
  { titleKey: 'courier.nav.hybrid_deliveries', icon: 'mdi-truck-fast', route: '/courier/hybrid-deliveries' },
  { titleKey: 'courier.nav.service_area', icon: 'mdi-map-marker-path', route: '/courier/service-area' },
  { titleKey: 'courier.nav.settings', icon: 'mdi-cog', route: '/courier/settings' },
  { titleKey: 'courier.nav.profile', icon: 'mdi-account', route: '/courier/profile' },
  { titleKey: 'courier.nav.access_as_customer', icon: 'mdi-account-switch', route: '/customer/dashboard' },
];

const currentPageTitle = computed(() => {
  const currentItem = navItems.find(item => isActive(item.route));
  return currentItem ? t(currentItem.titleKey) : t('courier.title');
});

const isActive = (route) => {
  if (route === '/courier/dashboard' || route === '/courier') {
    return currentRoute.value === '/courier' || currentRoute.value === '/courier/dashboard';
  }
  return currentRoute.value.startsWith(route);
};

const goTo = (url) => {
  router.visit(url);
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

.glass-bar {
  background: rgba(255, 255, 255, 0.8) !important;
  backdrop-filter: blur(10px);
}

.color-primary {
  color: var(--v-primary-base);
}

.page-container {
  max-width: 1400px;
  margin: 0 auto;
}
</style>
