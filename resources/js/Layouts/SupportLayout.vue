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
          :title="t(item.titleKey)"
          :prepend-icon="item.icon"
          :active="isActive(item.route)"
          @click="navigateTo(item.route)"
          link
          data-cy="support-nav-item"
        />
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
const { drawer, currentRoute, navigateTo } = useLayout();

const navItems = [
  { titleKey: 'support.dashboard.title', icon: 'mdi-view-dashboard-outline', route: '/support' },
  { titleKey: 'support.tickets.title', icon: 'mdi-ticket-outline', route: '/support/tickets' },
  { titleKey: 'support.faq.title', icon: 'mdi-help-circle-outline', route: '/support/faq' },
];

const isActive = (route) => currentRoute.value.startsWith(route);

const matchedNavItem = computed(() => navItems.find(n => isActive(n.route)));

const currentPageTitle = computed(() => {
  const item = matchedNavItem.value;
  return item ? t(item.titleKey) : t('support.title');
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
