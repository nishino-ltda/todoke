<template>
  <v-app>
    <v-app-bar app flat border color="white">
      <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
      <v-app-bar-title class="text-subtitle-1 font-weight-bold">
        {{ currentPageTitle }}
      </v-app-bar-title>
      <v-spacer></v-spacer>
      <LanguageSelector />
    </v-app-bar>

    <v-navigation-drawer v-model="drawer" app :permanent="!$vuetify.display.mobile" :temporary="$vuetify.display.mobile" elevation="2">
      <v-list-item
        prepend-avatar="https://ui-avatars.com/api/?name=Admin&background=0D47A1&color=fff"
        title="Admin Panel"
        subtitle="System Management"
        class="pa-4"
      ></v-list-item>

      <v-divider></v-divider>

      <v-list density="comfortable" nav>
        <v-list-item
          v-for="item in navItems"
          :key="item.title"
          :active="currentRoute === item.route"
          :prepend-icon="item.icon"
          :title="item.title"
          @click="navigateTo(item.route)"
          link
          data-cy="admin-nav-item"
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

const hasCustomerRole = computed(() => user.value?.all_roles?.includes('customer'));

const navItems = computed(() => {
  const items = [
    { title: t('admin.dashboard.title'), icon: 'mdi-view-dashboard', route: '/admin/dashboard' },
    { title: t('admin.users.title'), icon: 'mdi-account-group', route: '/admin/users' },
    { title: t('admin.regions.title'), icon: 'mdi-map-marker-radius', route: '/admin/regions' },
    { title: t('admin.deliveries.title'), icon: 'mdi-truck-delivery', route: '/admin/deliveries' },
    { title: t('admin.settings.title'), icon: 'mdi-cog', route: '/admin/settings' },
    { title: 'Profile', icon: 'mdi-account', route: '/admin/profile' },
  ];

  if (hasCustomerRole.value) {
    items.push({ title: 'Access as Customer', icon: 'mdi-account-switch', route: '/customer/dashboard' });
  }

  return items;
});

const currentPageTitle = computed(() => {
  const item = navItems.value.find(n => n.route === currentRoute.value);
  return item ? item.title : 'Admin Panel';
});

const navigateTo = (route) => {
  router.visit(route);
};

const logout = () => {
  authStore.logout(router);
};
</script>

<style scoped>
.v-navigation-drawer {
  background: linear-gradient(180deg, #fafafa 0%, #f0f0f0 100%);
}
</style>
