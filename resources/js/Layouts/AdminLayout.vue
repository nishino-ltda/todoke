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
          data-cy="admin-nav-item"
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
const { drawer, currentRoute, navigateTo, user } = useLayout();

const hasCustomerRole = computed(() => user.value?.all_roles?.includes('customer'));

const navItems = computed(() => {
  const items = [
    { titleKey: 'admin.dashboard.title', icon: 'mdi-view-dashboard', route: '/admin/dashboard' },
    { titleKey: 'admin.nav.profile', icon: 'mdi-account', route: '/admin/profile' },
    { titleKey: 'admin.users.title', icon: 'mdi-account-group', route: '/admin/users' },
    { titleKey: 'admin.regions.title', icon: 'mdi-map-marker-radius', route: '/admin/regions' },
    { titleKey: 'admin.deliveries.title', icon: 'mdi-truck-delivery', route: '/admin/deliveries' },
    { titleKey: 'admin.settings.title', icon: 'mdi-cog', route: '/admin/settings' },
  ];

  if (hasCustomerRole.value) {
    items.push({ titleKey: 'admin.nav.access_as_customer', icon: 'mdi-account-switch', route: '/customer/dashboard' });
  }

  return items;
});

const isActive = (route) => {
  if (route === '/admin/dashboard') return currentRoute.value === '/admin' || currentRoute.value === '/admin/dashboard';
  return currentRoute.value.startsWith(route);
};

const matchedNavItem = computed(() => navItems.value.find(n => isActive(n.route)));

const currentPageTitle = computed(() => {
  const item = matchedNavItem.value;
  return item ? t(item.titleKey) : t('admin.title');
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
