<template>
  <v-app>
    <v-app-bar app flat border color="white">
      <v-app-bar-nav-icon @click="drawer = !drawer" data-cy="app-bar-nav-icon"></v-app-bar-nav-icon>
      <v-app-bar-title class="text-subtitle-1 font-weight-bold d-flex align-center">
        <v-icon size="24" color="primary" class="mr-2">mdi-store</v-icon>
        {{ currentPageTitle }}
      </v-app-bar-title>
      <v-spacer></v-spacer>
      
      <LanguageSelector class="mr-2" />
    </v-app-bar>

    <v-navigation-drawer
      v-model="drawer"
      app
      :temporary="$vuetify.display.mobile"
      :permanent="!$vuetify.display.mobile"
      elevation="2"
    >
      <v-list-item
        prepend-avatar="https://ui-avatars.com/api/?name=Partner&background=0D47A1&color=fff"
        :title="t('partner.title')"
        subtitle="Partner Portal"
        class="pa-4"
      ></v-list-item>

      <v-divider></v-divider>

      <v-list density="comfortable" nav>
        <v-list-item
          v-for="item in navItems"
          :key="item.title"
          :active="isActive(item.route)"
          @click="navigateTo(item.route)"
          :prepend-icon="item.icon"
          :title="t(item.titleKey)"
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
            <v-btn icon="mdi-logout" variant="text" @click="logout" size="small" data-cy="logout-btn"></v-btn>
          </template>
        </v-list-item>
      </template>
    </v-navigation-drawer>

    <v-main class="bg-grey-lighten-4">
      <v-container fluid class="pa-8">
        <slot />
      </v-container>
      <NotificationCenter />
    </v-main>

    <AppFooter />
  </v-app>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { usePage, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth';
import AppFooter from '../Components/AppFooter.vue';
import NotificationCenter from '@/Components/NotificationCenter.vue';
import LanguageSelector from '../Components/LanguageSelector.vue';

const { t } = useI18n();
const page = usePage();
const authStore = useAuthStore();
const drawer = ref(true);

const { user } = storeToRefs(authStore);

const currentRoute = computed(() => page.url);

const navItems = [
  { titleKey: 'partner.nav.dashboard', icon: 'mdi-view-dashboard', route: '/partner/dashboard' },
  { titleKey: 'partner.nav.orders', icon: 'mdi-clipboard-list', route: '/partner/orders' },
  { titleKey: 'partner.nav.products', icon: 'mdi-package-variant-closed', route: '/partner/products' },
  { titleKey: 'partner.nav.settings', icon: 'mdi-cog', route: '/partner/settings' },
  { titleKey: 'partner.nav.profile', icon: 'mdi-account', route: '/partner/profile' },
  { titleKey: 'partner.nav.access_as_customer', icon: 'mdi-account-switch', route: '/customer/dashboard' },
];

const isActive = (route) => {
  if (route === '/partner/dashboard') return currentRoute.value === '/partner' || currentRoute.value === '/partner/dashboard';
  return currentRoute.value.startsWith(route);
};

const currentPageTitle = computed(() => {
  const item = navItems.find(n => isActive(n.route));
  return item ? t(item.titleKey) : t('partner.title');
});

const navigateTo = (route) => {
  router.visit(route);
};

const logout = () => {
  authStore.logout(router);
};

// Sync auth store with server-side props
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

.v-navigation-drawer {
  background: linear-gradient(180deg, #fafafa 0%, #f0f0f0 100%);
}
</style>
