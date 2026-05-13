<template>
  <Head :title="currentPageTitle" />
  <v-app>
    <v-app-bar app elevation="2" color="primary" theme="dark" data-cy="layout-header">
      <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
      <v-app-bar-title class="text-subtitle-1 font-weight-bold">
        <v-icon v-if="currentPageIcon" class="mr-1" size="small">{{ currentPageIcon }}</v-icon>
        {{ currentPageTitle }}
      </v-app-bar-title>
      <v-spacer></v-spacer>
      <CartIcon class="mr-4" />
      <LanguageSelector />
    </v-app-bar>

    <v-navigation-drawer
      v-model="drawer"
      app
      :temporary="$vuetify.display.mobile"
      :permanent="!$vuetify.display.mobile"
      elevation="2"
    >
      <v-list density="comfortable" nav>
        <v-list-item
          v-for="item in navItems"
          :key="item.titleKey"
          :active="isActive(item.route)"
          :prepend-icon="item.icon"
          :title="t(item.titleKey)"
          @click="navigateTo(item.route)"
          link
          data-cy="customer-nav-item"
        ></v-list-item>
      </v-list>

      <template v-slot:append>
        <UserMenuAppend />
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
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useLayout } from '@/Composables/useLayout';
import CartIcon from '@/Components/CartIcon.vue';
import LanguageSelector from '@/Components/LanguageSelector.vue';
import NotificationCenter from '@/Components/NotificationCenter.vue';
import AppFooter from '@/Components/AppFooter.vue';
import UserMenuAppend from '@/Components/UserMenuAppend.vue';

const props = defineProps({
  currentPageIcon: {
    type: String,
    default: null,
  },
});

const { t } = useI18n();
const { drawer, currentRoute, navigateTo } = useLayout();

const navItems = [
  { titleKey: 'customer.nav.dashboard', icon: 'mdi-view-dashboard', route: '/customer/dashboard' },
  { titleKey: 'customer.nav.menu', icon: 'mdi-menu', route: '/customer/menu' },
  { titleKey: 'customer.nav.orders', icon: 'mdi-clipboard-list', route: '/customer/orders' },
  { titleKey: 'components.address.manage_addresses', icon: 'mdi-map-marker-radius', route: '/customer/addresses' },
  { titleKey: 'customer.nav.profile', icon: 'mdi-account', route: '/customer/profile' },
  { titleKey: 'customer.nav.support', icon: 'mdi-face-agent', route: '/support' },
];

const isActive = (route) => {
  if (route === '/customer/dashboard') return currentRoute.value === '/customer' || currentRoute.value === '/customer/dashboard';
  return currentRoute.value.startsWith(route);
};

const matchedNavItem = computed(() => navItems.find(n => isActive(n.route)));

const currentPageTitle = computed(() => {
  const item = matchedNavItem.value;
  return item ? t(item.titleKey) : t('customer.title');
});

const currentPageIcon = computed(() => {
  if (props.currentPageIcon) return props.currentPageIcon;
  return matchedNavItem.value?.icon || null;
});
</script>

<style scoped>
.v-navigation-drawer {
  background: linear-gradient(180deg, #fafafa 0%, #f0f0f0 100%);
}
</style>
