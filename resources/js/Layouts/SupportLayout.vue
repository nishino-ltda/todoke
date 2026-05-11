<template>
  <v-app>
    <v-app-bar app flat border color="white">
      <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
      <v-app-bar-title class="text-subtitle-1 font-weight-bold">
        Support Center
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
          v-for="item in supportNav"
          :key="item.to"
          :title="t(item.title)"
          :prepend-icon="item.icon"
          link
          @click="router.visit(item.to)"
          :active="isLinkActive(item.to)"
          data-cy="support-nav-item"
        />
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
        <slot />
      </v-container>
      <NotificationCenter />
    </v-main>

    <AppFooter />
  </v-app>
</template>

<script setup>
import { ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n';
import { router, usePage } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/auth';
import LanguageSelector from '../Components/LanguageSelector.vue';
import AppFooter from '../Components/AppFooter.vue';
import NotificationCenter from '@/Components/NotificationCenter.vue';

const { t } = useI18n();
const page = usePage();
const authStore = useAuthStore();
const drawer = ref(true);

const { user } = storeToRefs(authStore);

const isLinkActive = (path) => {
  return page.url.startsWith(path);
};

const supportNav = [
  { title: 'support.dashboard.title', to: '/support', icon: 'mdi-view-dashboard-outline' },
  { title: 'support.tickets.title', to: '/support/tickets', icon: 'mdi-ticket-outline' },
  { title: 'support.faq.title', to: '/support/faq', icon: 'mdi-help-circle-outline' }
];

const logout = () => {
  authStore.logout(router);
};
</script>

<style scoped>
.v-main {
  min-height: 100vh;
}
</style>
