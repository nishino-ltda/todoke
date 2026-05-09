<template>
  <v-app>
    <v-navigation-drawer v-model="drawer" permanent elevation="2">
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
        <div class="pa-4">
          <v-btn block color="error" variant="tonal" prepend-icon="mdi-logout" @click="logout">
            Logout
          </v-btn>
        </div>
      </template>
    </v-navigation-drawer>

    <v-app-bar flat border color="white">
      <v-app-bar-nav-icon v-if="$vuetify.display.mobile" @click="drawer = !drawer"></v-app-bar-nav-icon>
      <v-toolbar-title class="text-subtitle-1 font-weight-bold">
        {{ currentPageTitle }}
      </v-toolbar-title>
      <v-spacer></v-spacer>
      <LanguageSelector />
    </v-app-bar>

    <v-main class="bg-grey-lighten-4">
      <v-container fluid class="pa-6">
        <slot />
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import LanguageSelector from '../Components/LanguageSelector.vue';

const page = usePage();
const drawer = ref(true);

const currentRoute = computed(() => page.url);

const navItems = [
  { title: 'Dashboard', icon: 'mdi-view-dashboard', route: '/admin' },
  { title: 'User Management', icon: 'mdi-account-group', route: '/admin/users' },
  { title: 'Node Management', icon: 'mdi-lan', route: '/admin/nodes' },
  { title: 'Region Management', icon: 'mdi-map-marker-radius', route: '/admin/regions' },
  { title: 'Deliveries', icon: 'mdi-truck-delivery', route: '/admin/deliveries' },
  { title: 'Settings', icon: 'mdi-cog', route: '/admin/settings' },
];

const currentPageTitle = computed(() => {
  const item = navItems.find(n => n.route === currentRoute.value);
  return item ? item.title : 'Admin Panel';
});

const navigateTo = (route) => {
  router.visit(route);
};

const logout = () => {
  router.post('/logout');
};
</script>

<style scoped>
.v-navigation-drawer {
  background: linear-gradient(180deg, #fafafa 0%, #f0f0f0 100%);
}
</style>
